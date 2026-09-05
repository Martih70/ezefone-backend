<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogPostApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_only_published_posts_newest_first(): void
    {
        $older = BlogPost::factory()->create([
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
        $newer = BlogPost::factory()->create([
            'status' => 'published',
            'published_at' => now(),
        ]);
        BlogPost::factory()->create(['status' => 'draft']);

        $response = $this->getJson('/api/blog-posts');

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        $this->assertSame($newer->slug, $response->json('data.0.slug'));
        $this->assertSame($older->slug, $response->json('data.1.slug'));
        $response->assertJsonStructure([
            'data' => [['title', 'slug', 'excerpt', 'cover_image_url', 'published_at']],
        ]);
    }

    public function test_show_returns_full_published_post(): void
    {
        $post = BlogPost::factory()->create(['status' => 'published']);

        $response = $this->getJson("/api/blog-posts/{$post->slug}");

        $response->assertOk();
        $response->assertJson([
            'slug' => $post->slug,
            'content_html' => $post->content_html,
            'meta_description' => $post->meta_description,
        ]);
    }

    public function test_show_404s_for_unpublished_post(): void
    {
        $post = BlogPost::factory()->create(['status' => 'draft']);

        $this->getJson("/api/blog-posts/{$post->slug}")->assertNotFound();
    }

    public function test_show_404s_for_missing_slug(): void
    {
        $this->getJson('/api/blog-posts/does-not-exist')->assertNotFound();
    }
}
