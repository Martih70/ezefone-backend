<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Services\SeobotClient;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Throwable;

class SeobotImportCommand extends Command
{
    protected $signature = 'seobot:import';

    protected $description = 'Import (or re-import) published, non-deleted SEObot articles into blog_posts';

    public function handle(): int
    {
        $key = config('services.seobot.key');

        if (! $key) {
            $this->error('SEOBOT_API_KEY is not set — add it to .env.');

            return self::FAILURE;
        }

        $client = new SeobotClient($key);

        $index = $client->fetchIndex();

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($index as $item) {
            if (empty($item['id'])) {
                continue;
            }

            $article = $client->fetchArticle($item['id']);

            // The compressed index has no published/deleted flags — only the
            // full article does. Only ever import what SEObot itself would
            // consider live.
            if (empty($article) || ($article['deleted'] ?? false) || ! ($article['published'] ?? false)) {
                $skipped++;

                continue;
            }

            $title = $article['headline'] ?? $article['title'] ?? '';

            $keywords = collect(explode(',', $article['metaKeywords'] ?? ''))
                ->map(fn ($keyword) => trim($keyword))
                ->filter()
                ->values()
                ->all();

            $tags = collect($article['tags'] ?? [])
                ->pluck('title')
                ->filter()
                ->values()
                ->all();

            $post = BlogPost::updateOrCreate(
                ['seobot_id' => $article['id']],
                [
                    'title' => $title,
                    'slug' => $article['slug'],
                    // SEObot has no distinct excerpt field — its metaDescription
                    // is the only summary text available, so it doubles as both.
                    'excerpt' => $article['metaDescription'] ?? null,
                    'content_html' => $article['html'] ?? '',
                    // No distinct SEO title either — reuse the headline.
                    'meta_title' => $title,
                    'meta_description' => $article['metaDescription'] ?? null,
                    'keywords' => $keywords ?: null,
                    'category' => $article['category']['title'] ?? null,
                    'tags' => $tags ?: null,
                    'cover_image_url' => $article['image'] ?? null,
                    'published_at' => isset($article['publishedAt'])
                        ? Carbon::parse($article['publishedAt'])
                        : null,
                    'status' => 'published',
                ]
            );

            $post->wasRecentlyCreated ? $created++ : $updated++;
        }

        $summary = "{$created} created, {$updated} updated";

        if ($skipped) {
            $summary .= ", {$skipped} skipped (unpublished/deleted)";
        }

        $this->info($summary);

        if ($created + $updated > 0) {
            $this->triggerEzefoneWebRebuild();
        }

        return self::SUCCESS;
    }

    /**
     * ezefone-web is a static export — it only picks up new/changed posts
     * when it's rebuilt. Best-effort: a failure here shouldn't fail the
     * import itself, since blog_posts is already correctly updated either
     * way and the next scheduled run will try again.
     */
    protected function triggerEzefoneWebRebuild(): void
    {
        $hookUrl = config('services.seobot.ezefone_web_deploy_hook_url');

        if (! $hookUrl) {
            return;
        }

        try {
            Http::timeout(10)->get($hookUrl)->throw();
            $this->info('Triggered ezefone-web rebuild.');
        } catch (Throwable $e) {
            $this->warn("Could not trigger ezefone-web rebuild: {$e->getMessage()}");
        }
    }
}
