<?php

use Dipesh79\LaravelActionDto\Console\Commands\MakeActionCommand;
use Dipesh79\LaravelActionDto\Console\Commands\MakeCrudCommand;
use Dipesh79\LaravelActionDto\Console\Commands\MakeDtoCommand;
use Dipesh79\LaravelActionDto\Console\Commands\MakeQueryCommand;
use Dipesh79\LaravelActionDto\LaravelActionDtoServiceProvider;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

it('merges package config on register', function () {
    $basePath = sys_get_temp_dir().'/provider-register-'.uniqid('', true);
    mkdir($basePath, 0755, true);

    $app = new Application($basePath);
    $app->instance('config', new Repository([]));

    $provider = new LaravelActionDtoServiceProvider($app);
    $provider->register();

    expect($app['config']->get('laravel-action-dto.actions.namespace'))->toBe('App\\Actions')
        ->and($app['config']->get('laravel-action-dto.dto.namespace'))->toBe('App\\DTOs')
        ->and($app['config']->get('laravel-action-dto.queries.namespace'))->toBe('App\\Queries');
});

it('registers commands and publish paths when running in console', function () {
    $basePath = sys_get_temp_dir().'/provider-boot-console-'.uniqid('', true);
    mkdir($basePath, 0755, true);

    $app = new class($basePath) extends Application
    {
        public function runningInConsole(): bool
        {
            return true;
        }
    };

    $provider = new class($app) extends LaravelActionDtoServiceProvider
    {
        /**
         * @var array<int, class-string>
         */
        public array $registeredCommands = [];

        /**
         * @var array<string, string>
         */
        public array $publishedPaths = [];

        public ?string $publishGroup = null;

        public function commands($commands): void
        {
            $this->registeredCommands = is_array($commands) ? $commands : func_get_args();
        }

        protected function publishes(array $paths, $groups = null): void
        {
            $this->publishedPaths = $paths;
            $this->publishGroup = is_string($groups) ? $groups : null;
        }
    };

    $provider->boot();

    expect($provider->registeredCommands)->toBe([
        MakeActionCommand::class,
        MakeDtoCommand::class,
        MakeCrudCommand::class,
        MakeQueryCommand::class,
    ])
        ->and($provider->publishGroup)->toBe('laravel-action-dto-config')
        ->and(array_key_first($provider->publishedPaths))->toEndWith('/src/config/laravel-action-dto.php')
        ->and(array_values($provider->publishedPaths)[0])->toBe($basePath.'/config/laravel-action-dto.php');
});

it('does not register console-only resources outside console runtime', function () {
    $basePath = sys_get_temp_dir().'/provider-boot-http-'.uniqid('', true);
    mkdir($basePath, 0755, true);

    $app = new class($basePath) extends Application
    {
        public function runningInConsole(): bool
        {
            return false;
        }
    };

    $provider = new class($app) extends LaravelActionDtoServiceProvider
    {
        /**
         * @var array<int, class-string>
         */
        public array $registeredCommands = [];

        /**
         * @var array<string, string>
         */
        public array $publishedPaths = [];

        public function commands($commands): void
        {
            $this->registeredCommands = is_array($commands) ? $commands : func_get_args();
        }

        protected function publishes(array $paths, $groups = null): void
        {
            $this->publishedPaths = $paths;
        }
    };

    $provider->boot();

    expect($provider->registeredCommands)->toBe([])
        ->and($provider->publishedPaths)->toBe([])
        ->and(ServiceProvider::pathsToPublish(LaravelActionDtoServiceProvider::class, 'laravel-action-dto-config'))->toBeArray();
});
