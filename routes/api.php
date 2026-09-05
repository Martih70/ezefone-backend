<?php

use App\Http\Controllers\BlogPostController;
use Illuminate\Support\Facades\Route;

// Public, unauthenticated blog content for the separate Next.js marketing
// site (ezefone-web) — this is the permanent replacement for depending on
// SEObot directly. See App\Console\Commands\SeobotImportCommand.
Route::get('/blog-posts', [BlogPostController::class, 'index']);
Route::get('/blog-posts/{blog_post:slug}', [BlogPostController::class, 'show']);
