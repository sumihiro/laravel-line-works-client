<?php

return [
    /*
    |--------------------------------------------------------------------------
    | LINE WORKS API Configuration
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials and configuration for the
    | LINE WORKS API. You can configure multiple bots by adding them to the
    | 'bots' array with a unique key for each bot.
    |
    */

    // Default bot to use when no bot is specified
    'default' => env('LINEWORKS_DEFAULT_BOT', 'default'),

    // API Base URL
    'api_base_url' => env('LINEWORKS_API_BASE_URL', 'https://www.worksapis.com/v1.0'),

    // Cache configuration
    'cache' => [
        // Whether to cache JWT tokens
        'enabled' => env('LINEWORKS_CACHE_ENABLED', true),
        
        // Cache store to use (null for default cache store)
        'store' => env('LINEWORKS_CACHE_STORE', null),
        
        // Token cache TTL in minutes (default: 30 minutes)
        'ttl' => env('LINEWORKS_CACHE_TTL', 30),
    ],

    // Logging configuration
    'logging' => [
        // Whether to log API requests and responses
        'enabled' => env('LINEWORKS_LOGGING_ENABLED', true),
        
        // Log level (debug, info, notice, warning, error, critical, alert, emergency)
        'level' => env('LINEWORKS_LOG_LEVEL', 'debug'),
        
        // Log channel to use (null for default log channel)
        'channel' => env('LINEWORKS_LOG_CHANNEL', null),
    ],

    // Bot configurations
    'bots' => [
        'default' => [
            // Service Account ID (JWT issuer)
            'service_account' => env('LINEWORKS_SERVICE_ACCOUNT'),
            
            // Private Key for JWT signing (content or path to file)
            'private_key' => env('LINEWORKS_PRIVATE_KEY'),
            
            // Private Key ID (optional)
            'private_key_id' => env('LINEWORKS_PRIVATE_KEY_ID'),
            
            // Client ID
            'client_id' => env('LINEWORKS_CLIENT_ID'),
            
            // Client Secret
            'client_secret' => env('LINEWORKS_CLIENT_SECRET'),
            
            // Bot ID
            'bot_id' => env('LINEWORKS_BOT_ID'),
            
            // Bot Secret
            'bot_secret' => env('LINEWORKS_BOT_SECRET'),
            
            // Domain ID
            'domain_id' => env('LINEWORKS_DOMAIN_ID'),
        ],
        
        // You can add more bots here
        // 'another_bot' => [
        //     'service_account' => env('LINEWORKS_ANOTHER_SERVICE_ACCOUNT'),
        //     'private_key' => env('LINEWORKS_ANOTHER_PRIVATE_KEY'),
        //     'private_key_id' => env('LINEWORKS_ANOTHER_PRIVATE_KEY_ID'),
        //     'client_id' => env('LINEWORKS_ANOTHER_CLIENT_ID'),
        //     'client_secret' => env('LINEWORKS_ANOTHER_CLIENT_SECRET'),
        //     'bot_id' => env('LINEWORKS_ANOTHER_BOT_ID'),
        //     'bot_secret' => env('LINEWORKS_ANOTHER_BOT_SECRET'),
        //     'domain_id' => env('LINEWORKS_ANOTHER_DOMAIN_ID'),
        // ],
    ],
]; 