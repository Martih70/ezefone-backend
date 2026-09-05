<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Thin client for SEObot's "Connect my Blog" REST/CDN feed.
 *
 * This isn't a conventional authenticated REST API: the API key is embedded
 * directly in the URL path, and the whole article index comes back in a
 * single request — there's no server-side ?page= pagination. This mirrors
 * SEObot's own official `seobot` npm package (BlogClient), verified live
 * against both the npm package's public demo key and our own account key:
 *
 *   GET /{key}/system/base.json    -> compressed index of every article
 *   GET /{key}/blog/{id}.json      -> one full article (html, published,
 *                                     deleted, etc. only live here, not on
 *                                     the compressed index)
 *
 * @see https://github.com/MarsX-dev/seobot/blob/main/src/blog/client.ts
 */
class SeobotClient
{
    public function __construct(
        protected string $key,
        protected string $baseUrl = 'https://cdn.seobotai.com',
    ) {
        if ($this->key === '') {
            throw new RuntimeException('SEOBOT_API_KEY is not configured.');
        }
    }

    /**
     * Fetch the compressed article index. Each item is just enough to know
     * which full articles exist (id, slug, title, etc.) — it does NOT carry
     * `published`/`deleted`, so callers must fetch each full article to
     * filter on those.
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetchIndex(): array
    {
        $response = Http::get("{$this->baseUrl}/{$this->key}/system/base.json");

        $response->throw();

        return $response->json() ?? [];
    }

    /**
     * Fetch one full article by SEObot id.
     *
     * @return array<string, mixed>
     */
    public function fetchArticle(string $id): array
    {
        $response = Http::get("{$this->baseUrl}/{$this->key}/blog/{$id}.json");

        $response->throw();

        return $response->json() ?? [];
    }
}
