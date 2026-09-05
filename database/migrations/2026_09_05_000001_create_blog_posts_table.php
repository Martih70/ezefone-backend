<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();

            // SEObot's own article id — re-imports upsert on this, never duplicate.
            $table->string('seobot_id')->unique();

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content_html');
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->json('keywords')->nullable();
            $table->string('category')->nullable();
            $table->json('tags')->nullable();
            $table->string('cover_image_url')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('status')->default('published');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
