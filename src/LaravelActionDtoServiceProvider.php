<?php

declare(strict_types=1);

namespace Dipesh79\LaravelActionDto;

use Dipesh79\LaravelActionDto\Console\Commands\MakeActionCommand;
use Dipesh79\LaravelActionDto\Console\Commands\MakeCrudCommand;
use Dipesh79\LaravelActionDto\Console\Commands\MakeDtoCommand;
use Dipesh79\LaravelActionDto\Console\Commands\MakeQueryCommand;
use Illuminate\Support\ServiceProvider;

class LaravelActionDtoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/laravel-action-dto.php', 'laravel-action-dto');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeActionCommand::class,
                MakeDtoCommand::class,
                MakeCrudCommand::class,
                MakeQueryCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/config/laravel-action-dto.php' => config_path('laravel-action-dto.php'),
            ], 'laravel-action-dto-config');
        }
    }
}
