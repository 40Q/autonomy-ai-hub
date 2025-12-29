<?php

return [
    'menu_title' => '40Q Autonomy AI',
    'menu_position' => 56,
    'capability' => 'manage_options',

    'ai' => [
        'default_model' => env('AUTONOMY_AI_MODEL', 'openai'),
        'openai' => [
            'api_key' => env('OPENAI_API_KEY', ''),
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        ],
        'anthropic' => [
            'api_key' => env('ANTHROPIC_API_KEY', ''),
            'model' => env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-latest'),
        ],
    ],

    'packages' => [
        [
            'slug' => 'seo-assistant',
            'title' => 'SEO Assistant',
            'description' => 'AI-assisted titles, meta descriptions, and on-page SEO suggestions.',
            'docs_url' => 'https://github.com/40Q/40q-seo-assistant',
            'manage_url' => 'admin.php?page=40q-autonomy-ai-seo-assistant',
            'status_class' => 'FortyQ\\SeoAssistant\\SeoAssistantServiceProvider',
        ],
        [
            'slug' => 'media-alt-suggester',
            'title' => 'Media Alt Suggester',
            'description' => 'Accessible alt text suggestions for media library attachments.',
            'docs_url' => null,
            'manage_url' => 'upload.php',
            'status_class' => 'FortyQ\\MediaAltSuggester\\MediaAltSuggesterServiceProvider',
        ],
    ],

    'settings' => [
        'env_keys' => [
            'OPENAI_API_KEY',
            'OPENAI_MODEL',
            'ANTHROPIC_API_KEY',
            'ANTHROPIC_MODEL',
            'MEDIA_ALT_PROVIDER',
            'MEDIA_ALT_VISION',
            'AUTONOMY_AI_MODEL',
        ],
        'notes' => [
            'Secrets live in .env; keep API keys out of wp_options.',
            'Publish per-site config with wp acorn vendor:publish --tag=media-alt-config.',
            'Use Composer path repos for local package development; tag releases in individual repos for production.',
        ],
    ],
];
