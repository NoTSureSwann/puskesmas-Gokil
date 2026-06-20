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
        'token' => env('POSTMARK_TOKEN'),
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

    /*
    |--------------------------------------------------------------------------
    | AI Engine (Flask + Groq LLM)
    |--------------------------------------------------------------------------
    |
    | Konfigurasi terpusat untuk AI Engine lokal (Flask) dan Groq Cloud API.
    | Gunakan config('services.ai_engine.*') di kode, BUKAN env() langsung.
    |
    */
    'ai_engine' => [
        'flask_url' => env('AI_ENGINE_URL', 'http://127.0.0.1:5000'),
        'flask_secret' => env('AI_ENGINE_SECRET', ''),
        'groq_api_key' => env('GROQ_API_KEY'),
        'groq_model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
    ],

];
