<?php

namespace Dipesh79\LaravelActionDto\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use RuntimeException;

use function Laravel\Prompts\text;

class MakeQueryCommand extends Command implements PromptsForMissingInput
{
    protected $signature = 'make:query {name : Query class name}';

    protected $description = 'Create a new query class';

    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'name' => fn (): string => text(
                label: 'What should the query be named?',
                placeholder: 'GetUsers or Admin/GetUsers',
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
            $this->components->error('Query already exists!');

            return self::FAILURE;
        }

        $this->components->task('Creating query class', function () use ($path, $class): void {
            $this->makeDirectory($path);
            file_put_contents($path, $this->buildClass($class));
        });

        $this->components->info('Query created successfully.');
        $this->components->twoColumnDetail('Class', class_basename($class));
        $this->components->twoColumnDetail('Path', $path);

        return self::SUCCESS;
    }

    protected function qualifyClass(string $name): string
    {
        $name = str_replace('/', '\\', $name);

        $name = str($name)->studly()->toString();

        $suffixEnabled = (bool) config('laravel-action-dto.queries.suffix', true);
        $namespace = config('laravel-action-dto.queries.namespace', 'App\\Queries');
        $namespace = is_string($namespace) ? $namespace : 'App\\Queries';

        if ($suffixEnabled && ! str_ends_with($name, 'Query')) {
            $name .= 'Query';
        }

        if (! $suffixEnabled && str_ends_with($name, 'Query')) {
            $name = substr($name, 0, -5);
        }

        return $namespace.'\\'.$name;
    }

    protected function getPath(string $class): string
    {
        $namespace = config('laravel-action-dto.queries.namespace', 'App\\Queries');
        $namespace = is_string($namespace) ? $namespace : 'App\\Queries';

        $basePath = config('laravel-action-dto.queries.directory', app_path('Queries'));
        $basePath = is_string($basePath) ? $basePath : app_path('Queries');

        $class = str_replace($namespace.'\\', '', $class);

        return $basePath.'/'.str_replace('\\', '/', $class).'.php';
    }

    protected function makeDirectory(string $path): void
    {
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    protected function buildClass(string $class): string
    {
        $stub = $this->getStub();

        return str_replace(
            ['{{ namespace }}', '{{ class }}'],
            [$this->getNamespace($class), class_basename($class)],
            $stub
        );
    }

    protected function getStub(): string
    {
        $customStub = base_path('stubs/action-dto/query.stub');

        if (file_exists($customStub)) {
            $contents = file_get_contents($customStub);

            if ($contents !== false) {
                return $contents;
            }
        }

        $defaultStub = file_get_contents(__DIR__.'/../../stubs/query.stub');

        if ($defaultStub === false) {
            throw new RuntimeException('Unable to load query stub.');
        }

        return $defaultStub;
    }

    protected function getNamespace(string $class): string
    {
        return trim(implode('\\', array_slice(explode('\\', $class), 0, -1)), '\\');
    }

    protected function validateName(string $name): ?string
    {
        if ($name === '') {
            return 'The query name is required.';
        }

        if (! preg_match('#^[A-Za-z][A-Za-z0-9_\\\\/]*$#', $name)) {
            return 'Use letters, numbers, underscores, and namespace separators (/ or \\) only.';
        }

        return null;
    }
}
