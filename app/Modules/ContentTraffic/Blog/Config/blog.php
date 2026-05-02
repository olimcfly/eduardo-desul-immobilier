<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Blog Module Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Blog module with multi-tenant support
    |
    */

    'enabled' => env('BLOG_MODULE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenant Settings
    |--------------------------------------------------------------------------
    */
    'table_prefix' => env('BLOG_TABLE_PREFIX', 'blog_'),

    'tenant_column' => env('BLOG_TENANT_COLUMN', 'tenant_id'),

    /*
    |--------------------------------------------------------------------------
    | Routing Configuration
    |--------------------------------------------------------------------------
    */
    'route_prefix' => env('BLOG_ROUTE_PREFIX', 'api/blog'),

    'web_prefix' => env('BLOG_WEB_PREFIX', 'blog'),

    'route_middleware' => [
        'api',
        'auth:api',
        'tenant.check',
    ],

    'web_middleware' => [
        'web',
        'auth',
        'tenant.check',
    ],

    /*
    |--------------------------------------------------------------------------
    | Article Configuration
    |--------------------------------------------------------------------------
    */
    'article' => [
        'per_page' => env('BLOG_ARTICLES_PER_PAGE', 15),
        'excerpt_length' => env('BLOG_EXCERPT_LENGTH', 150),
        'image_width' => 1200,
        'image_height' => 630,
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO Configuration
    |--------------------------------------------------------------------------
    */
    'seo' => [
        'enabled' => env('BLOG_SEO_ENABLED', true),
        'min_title_length' => 30,
        'max_title_length' => 60,
        'min_description_length' => 120,
        'max_description_length' => 160,
        'keywords_max' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Media Upload Configuration
    |--------------------------------------------------------------------------
    */
    'media' => [
        'enabled' => env('BLOG_MEDIA_ENABLED', true),
        'disk' => env('BLOG_MEDIA_DISK', 'public'),
        'path' => env('BLOG_MEDIA_PATH', 'blog/articles'),
        'max_file_size' => 5120, // 5MB in KB
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => env('BLOG_CACHE_ENABLED', true),
        'ttl' => env('BLOG_CACHE_TTL', 3600),
        'key_prefix' => 'blog:',
    ],

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    */
    'features' => [
        'comments' => env('BLOG_COMMENTS_ENABLED', false),
        'ratings' => env('BLOG_RATINGS_ENABLED', false),
        'sharing' => env('BLOG_SHARING_ENABLED', true),
        'tags' => env('BLOG_TAGS_ENABLED', true),
        'categories' => env('BLOG_CATEGORIES_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */
    'pagination' => [
        'per_page' => env('BLOG_PAGINATION_PER_PAGE', 15),
        'path' => 'blog',
        'query_string' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    */
    'connection' => env('BLOG_DATABASE_CONNECTION', null),

];
