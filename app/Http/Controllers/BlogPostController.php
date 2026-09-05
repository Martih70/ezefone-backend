<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;

class BlogPostController extends Controller
{
    /**
     * Public, unauthenticated list of published posts for the marketing
     * site's blog index. Newest first, paginated.
     */
    public function index()
    {
        return BlogPost::published()
            ->orderByDesc('published_at')
            ->paginate(10)
            ->through(fn (BlogPost $post) => [
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt,
                'cover_image_url' => $post->cover_image_url,
                'published_at' => $post->published_at,
            ]);
    }

    /**
     * Full detail for one post. 404s if missing or not published, so
     * anything SEObot has since unpublished never leaks publicly.
     */
    public function show(BlogPost $blog_post)
    {
        abort_unless($blog_post->status === 'published', 404);

        return $blog_post;
    }
}
