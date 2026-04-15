<?php

namespace Tests\Support;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;

class TestApplication
{
    public static function make(?string $basePath = null, array $overrides = []): Application
    {
        $basePath ??= self::basePath();

        if (! is_dir($basePath)) {
            mkdir($basePath, 0755, true);
        }

        $app = new Application($basePath);
        $app->instance('config', new Repository(array_replace_recursive([
            'laravel-action-dto' => [
                'actions' => [
                    'namespace' => 'App\\Actions',
                    'directory' => $app->path('Actions'),
                    'suffix' => true,
                ],
                'dto' => [
                    'namespace' => 'App\\DTOs',
                    'directory' => $app->path('DTOs'),
                    'suffix' => true,
                ],
                'queries' => [
                    'namespace' => 'App\\Queries',
                    'directory' => $app->path('Queries'),
                    'suffix' => true,
                ],
            ],
        ], $overrides)));

        Container::setInstance($app);
        Facade::setFacadeApplication($app);
        Facade::clearResolvedInstances();

        return $app;
    }

    public static function basePath(): string
    {
        return sys_get_temp_dir().'/laravel-action-dto-tests';
    }
}
