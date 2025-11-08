<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Package Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the core namespaces and paths used by Laravel Artifacts.
    |
    */

    'model_namespace' => env('ARTIFACTS_MODEL_NS', 'LaravelArtifacts\\Models'),
    'command_namespace' => env('ARTIFACTS_COMMAND_NS', 'LaravelArtifacts\\Commands'),
    'service_namespace' => env('ARTIFACTS_SERVICE_NS', 'LaravelArtifacts\\Services'),

    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how and where artifacts are stored. Supports local, S3, Azure,
    | and database storage.
    |
    */

    'storage' => [
        'default' => env('ARTIFACTS_STORAGE', 'local'),

        'drivers' => [
            'local' => [
                'path' => env('ARTIFACTS_LOCAL_PATH', 'artifacts'),
            ],
            's3' => [
                'bucket' => env('ARTIFACTS_S3_BUCKET'),
                'region' => env('ARTIFACTS_S3_REGION', 'us-east-1'),
                'path' => env('ARTIFACTS_S3_PATH', 'artifacts'),
            ],
            'azure' => [
                'container' => env('ARTIFACTS_AZURE_CONTAINER'),
                'account' => env('ARTIFACTS_AZURE_ACCOUNT'),
            ],
            'database' => [
                'enabled' => env('ARTIFACTS_DB_STORAGE', false),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Configuration
    |--------------------------------------------------------------------------
    |
    | Configure AI providers for content generation. Currently supports
    | OpenAI, Claude (Anthropic), and Gemini (Google).
    |
    */

    'ai' => [
        'default_provider' => env('ARTIFACTS_AI_PROVIDER', 'openai'),

        'providers' => [
            'openai' => [
                'api_key' => env('OPENAI_API_KEY'),
                'model' => env('ARTIFACTS_OPENAI_MODEL', 'gpt-4-turbo'),
                'temperature' => env('ARTIFACTS_OPENAI_TEMP', 0.3),
                'max_tokens' => env('ARTIFACTS_OPENAI_TOKENS', 2000),
            ],
            'claude' => [
                'api_key' => env('ANTHROPIC_API_KEY'),
                'model' => env('ARTIFACTS_CLAUDE_MODEL', 'claude-3-5-sonnet-20241022'),
                'temperature' => env('ARTIFACTS_CLAUDE_TEMP', 0.3),
                'max_tokens' => env('ARTIFACTS_CLAUDE_TOKENS', 2000),
            ],
            'gemini' => [
                'api_key' => env('GEMINI_API_KEY'),
                'model' => env('ARTIFACTS_GEMINI_MODEL', 'gemini-pro'),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Quality Gates Configuration
    |--------------------------------------------------------------------------
    |
    | Configure quality validation rules and gates. These ensure content
    | meets minimum standards before publication.
    |
    */

    'quality_gates' => [
        'enabled' => env('ARTIFACTS_QUALITY_GATES_ENABLED', true),
        'strict_mode' => env('ARTIFACTS_QUALITY_STRICT', false),

        'rules' => [
            \LaravelArtifacts\Services\ValidationService\Rules\CompletenessRule::class,
            \LaravelArtifacts\Services\ValidationService\Rules\ConsistencyRule::class,
            \LaravelArtifacts\Services\ValidationService\Rules\LinkValidityRule::class,
            \LaravelArtifacts\Services\ValidationService\Rules\CodeExamplesRule::class,
        ],

        'required_sections' => [
            'overview',
            'installation',
            'usage',
            'examples',
            'faq',
        ],

        'min_completeness_score' => env('ARTIFACTS_MIN_COMPLETENESS', 70),
        'min_quality_score' => env('ARTIFACTS_MIN_QUALITY', 80),
    ],

    /*
    |--------------------------------------------------------------------------
    | Merge Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how AI-generated content is merged with existing content.
    |
    */

    'merge' => [
        'semantic_threshold' => env('ARTIFACTS_MERGE_THRESHOLD', 0.85),
        'preserve_manual_edits' => env('ARTIFACTS_PRESERVE_MANUAL', true),
        'track_all_changes' => env('ARTIFACTS_TRACK_CHANGES', true),
        'use_embeddings' => env('ARTIFACTS_USE_EMBEDDINGS', false),

        'conflict_resolution' => env('ARTIFACTS_CONFLICT_RESOLUTION', 'manual'), // manual, auto, ai
    ],

    /*
    |--------------------------------------------------------------------------
    | Version Control
    |--------------------------------------------------------------------------
    |
    | Configure version tracking and retention policies.
    |
    */

    'versioning' => [
        'enabled' => env('ARTIFACTS_VERSIONING', true),
        'auto_increment' => env('ARTIFACTS_AUTO_INCREMENT', true),
        'max_versions' => env('ARTIFACTS_MAX_VERSIONS', 50),
        'retention_days' => env('ARTIFACTS_RETENTION_DAYS', 365),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance & Caching
    |--------------------------------------------------------------------------
    |
    | Configure caching and performance settings.
    |
    */

    'cache' => [
        'enabled' => env('ARTIFACTS_CACHE_ENABLED', true),
        'ttl' => env('ARTIFACTS_CACHE_TTL', 3600), // seconds
        'prefix' => env('ARTIFACTS_CACHE_PREFIX', 'artifacts'),
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Configuration
    |--------------------------------------------------------------------------
    |
    | Configure dashboard and UI settings.
    |
    */

    'ui' => [
        'items_per_page' => env('ARTIFACTS_ITEMS_PER_PAGE', 15),
        'editor' => env('ARTIFACTS_EDITOR', 'markdown'), // markdown, wysiwyg
        'theme' => env('ARTIFACTS_THEME', 'light'),
    ],
];
