<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SeobotImportCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_upserts_only_published_non_deleted_articles_and_triggers_rebuild(): void
    {
        config([
            'services.seobot.key' => 'testkey',
            'services.seobot.ezefone_web_deploy_hook_url' => 'https://example.com/deploy-hook',
        ]);

        Http::fake([
            'https://cdn.seobotai.com/testkey/system/base.json' => Http::response([
                ['id' => 'abc123'],
                ['id' => 'def456'],
            ]),
            'https://cdn.seobotai.com/testkey/blog/abc123.json' => Http::response([
                'id' => 'abc123',
                'slug' => 'published-post',
                'headline' => 'Published Post',
                'metaDescription' => 'A summary.',
                'metaKeywords' => 'foo, bar',
                'html' => '<p>Hello</p>',
                'category' => ['title' => 'Guides'],
                'tags' => [['title' => 'Tips']],
                'image' => 'https://example.com/cover.jpg',
                'publishedAt' => '2026-01-01T00:00:00.000Z',
                'published' => true,
                'deleted' => false,
            ]),
            'https://cdn.seobotai.com/testkey/blog/def456.json' => Http::response([
                'id' => 'def456',
                'slug' => 'draft-post',
                'headline' => 'Draft Post',
                'published' => false,
                'deleted' => false,
            ]),
            'https://example.com/deploy-hook' => Http::response('ok'),
        ]);

        $this->artisan('seobot:import')
            ->expectsOutputToContain('1 created, 0 updated, 1 skipped')
            ->assertSuccessful();

        $this->assertDatabaseCount('blog_posts', 1);
        $this->assertDatabaseHas('blog_posts', [
            'seobot_id' => 'abc123',
            'slug' => 'published-post',
            'category' => 'Guides',
        ]);

        Http::assertSent(fn ($request) => $request->url() === 'https://example.com/deploy-hook');
    }

    public function test_import_skips_rebuild_trigger_when_nothing_changed(): void
    {
        config([
            'services.seobot.key' => 'testkey',
            'services.seobot.ezefone_web_deploy_hook_url' => 'https://example.com/deploy-hook',
        ]);

        Http::fake([
            'https://cdn.seobotai.com/testkey/system/base.json' => Http::response([]),
        ]);

        $this->artisan('seobot:import')
            ->expectsOutputToContain('0 created, 0 updated')
            ->assertSuccessful();

        Http::assertNotSent(fn ($request) => $request->url() === 'https://example.com/deploy-hook');
    }

    public function test_import_fails_gracefully_without_an_api_key(): void
    {
        config(['services.seobot.key' => null]);

        $this->artisan('seobot:import')->assertFailed();
    }
}
