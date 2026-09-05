<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'seobot_id',
        'title',
        'slug',
        'excerpt',
        'content_html',
        'meta_title',
        'meta_description',
        'keywords',
        'category',
        'tags',
        'cover_image_url',
        'published_at',
        'status',
    ];

    protected $casts = [
        'keywords' => 'array',
        'tags' => 'array',
        'published_at' => 'datetime',
    ];

    /**
     * Use the slug for route-model binding (e.g. {blog_post:slug}).
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
