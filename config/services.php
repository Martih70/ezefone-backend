<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'stripe' => [
        'key'                  => env('STRIPE_KEY'),
        'secret'               => env('STRIPE_SECRET'),
        'webhook'              => env('STRIPE_WEBHOOK_SECRET'),
        'price_early_adopter'  => env('STRIPE_PRICE_EARLY_ADOPTER'),
        'price_standard'       => env('STRIPE_PRICE_STANDARD'),
        'early_adopter_limit'  => env('STRIPE_EARLY_ADOPTER_LIMIT', 100),
    ],

    // Temporary: only needed until SEObot is cancelled — see seobot:import.
    'seobot' => [
        'key' => env('SEOBOT_API_KEY'),

        // ezefone-web is a static export rebuilt only when its Forge deploy
        // hook fires — this is that hook's URL, so seobot:import can trigger
        // a rebuild itself whenever it actually imports something new.
        // Site → Deployments tab in Forge. Left unset, the import command
        // just skips this step.
        'ezefone_web_deploy_hook_url' => env('EZEFONE_WEB_DEPLOY_HOOK_URL'),
    ],

];
