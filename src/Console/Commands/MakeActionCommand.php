<?php

namespace Dipesh79\LaravelActionDto\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use RuntimeException;

use function Laravel\Prompts\text;

class MakeActionCommand extends Command implements PromptsForMissingInput
{
    protected $signature = 'make:action {name : Action class name}';

    protected $description = 'Create a new action class';

    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'name' => fn (): string => text(
                label: 'What should the action be named?',
                placeholder: 'CreateUser or Admin/CreateUser',
                required: true,
                validate: fn (string $value): ?string => $this->validateName($value),
            ),
        ];
    }

    public function handle(): int
    {
        $argument = $this->argument('name');
        $name = is_string($argument) ? trim($argument) : '';

        if ($message = $this->validateName($name)) {
            $this->components->error($message);

            return self::FAILURE;
        }

        $class = $this->qualifyClass($name);
        $path = $this->getPath($class);

        if (file_exists($path)) {
            $this->components->error('Action already exists!');

            return self::FAILURE;
        }

        $this->components->task('Creating action class', function () use ($path, $class): void {
            $this->makeDirectory($path);
            file_put_contents($path, $this->buildClass($class));
        });

        $this->components->info('Action created successfully.');
        $this->components->twoColumnDetail('Class', class_basename($class));
        $this->components->twoColumnDetail('Path', $path);

        return self::SUCCESS;
    }

    /**
     * Convert input to the full class name
     */
    protected function qualifyClass(string $name): string
    {
        $name = str_replace('/', '\\', $name);

        $name = str($name)->studly()->toString();

        $suffixEnabled = (bool) config('laravel-action-dto.actions.suffix', true);
        $namespace = config('laravel-action-dto.actions.namespace', 'App\\Actions');
        $namespace = is_string($namespace) ? $namespace : 'App\\Actions';

        if ($suffixEnabled && ! str_ends_with($name, 'Action')) {
            $name .= 'Action';
        }

        if (! $suffixEnabled && str_ends_with($name, 'Action')) {
            $name = substr($name, 0, -6);
        }

        return $namespace.'\\'.$name;
    }

    /**
     * Get a file path from a class
     */
    protected function getPath(string $class): string
    {
        $namespace = config('laravel-action-dto.actions.namespace', 'App\\Actions');
        $namespace = is_string($namespace) ? $namespace : 'App\\Actions';

        $basePath = config('laravel-action-dto.actions.directory', app_path('Actions'));
        $basePath = is_string($basePath) ? $basePath : app_path('Actions');

        $class = str_replace($namespace.'\\', '', $class);

        return $basePath.'/'.str_replace('\\', '/', $class).'.php';
    }

    /**
     * Ensure a directory exists
     */
    protected function makeDirectory(string $path): void
    {
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    /**
     * Build class using stub
     */
    protected function buildClass(string $class): string
    {
        $stub = $this->getStub();

        return str_replace(
            ['{{ namespace }}', '{{ class }}'],
            [$this->getNamespace($class), class_basename($class)],
            $stub
        );
    }

    /**
     * Load stub (with override support)
     */
    protected function getStub(): string
    {
        $customStub = base_path('stubs/action-dto/action.stub');

        if (file_exists($customStub)) {
            $contents = file_get_contents($customStub);

            if ($contents !== false) {
                return $contents;
            }
        }

        $defaultStub = file_get_contents(__DIR__.'/../../stubs/action.stub');

        if ($defaultStub === false) {
            throw new RuntimeException('Unable to load action stub.');
        }

        return $defaultStub;
    }

    /**
     * Extract namespace
     */
    protected function getNamespace(string $class): string
    {
        return trim(implode('\\', array_slice(explode('\\', $class), 0, -1)), '\\');
    }

    protected function validateName(string $name): ?string
    {
        if ($name === '') {
            return 'The action name is required.';
        }

        if (! preg_match('#^[A-Za-z][A-Za-z0-9_\\\\/]*$#', $name)) {
            return 'Use letters, numbers, underscores, and namespace separators (/ or \\) only.';
        }

        return null;
    }
}
