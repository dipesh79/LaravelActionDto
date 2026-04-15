<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Action Generator Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the namespace and output directory used when generating
    | action classes. When enabled, the package will append the "Action"
    | suffix to generated class names.
    |
    */

    'actions' => [
        'namespace' => 'App\\Actions',
        'directory' => app_path('Actions'),
        'suffix' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | DTO Generator Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the namespace and output directory used when generating
    | data transfer objects. When enabled, the package will append the "DTO"
    | suffix to generated class names.
    |
    */

    'dto' => [
        'namespace' => 'App\\DTOs',
        'directory' => app_path('DTOs'),
        'suffix' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Query Generator Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the namespace and output directory used when generating
    | query classes. When enabled, the package will append the "Query"
    | suffix to generated class names.
    |
    */

    'queries' => [
        'namespace' => 'App\\Queries',
        'directory' => app_path('Queries'),
        'suffix' => true,
    ],

];
