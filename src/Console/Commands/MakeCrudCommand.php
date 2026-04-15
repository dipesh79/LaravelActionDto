<?php

namespace Dipesh79\LaravelActionDto\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;

use function Laravel\Prompts\text;

class MakeCrudCommand extends Command implements PromptsForMissingInput
{
    protected $signature = 'make:crud {name : Model name}';

    protected $description = 'Create CRUD query, actions, and DTOs for a model';

    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'name' => fn (): string => text(
                label: 'What model should the CRUD set be generated for?',
                placeholder: 'User or Admin/User',
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

        $plan = $this->generationPlan($name);

        $this->components->info('Generating CRUD classes...');

        foreach ($plan as $step) {
            $status = self::FAILURE;

            $this->components->task('Generating '.$step['name'], function () use (&$status, $step): bool {
                $status = $this->runGenerator($step['command'], $step['name']);

                return $status === self::SUCCESS;
            });

            if ($status !== self::SUCCESS) {
                $this->components->warn('CRUD generation stopped due to an error.');

                return self::FAILURE;
            }
        }

        $this->components->info('CRUD classes generated successfully.');

        return self::SUCCESS;
    }

    /**
     * @return array<int, array{command: string, name: string}>
     */
    protected function generationPlan(string $name): array
    {
        $normalized = str_replace('/', '\\', $name);
        $normalized = str($normalized)->studly()->toString();

        $segments = array_values(array_filter(explode('\\', $normalized), static fn (string $segment): bool => $segment !== ''));
        $model = array_pop($segments);
        $prefix = implode('\\', $segments);

        $qualify = static fn (string $class): string => $prefix !== '' ? $prefix.'\\'.$class : $class;

        return [
            ['command' => 'make:query', 'name' => $qualify($model.'Query')],
            ['command' => 'make:action', 'name' => $qualify('Create'.$model.'Action')],
            ['command' => 'make:action', 'name' => $qualify('Update'.$model.'Action')],
            ['command' => 'make:action', 'name' => $qualify('Delete'.$model.'Action')],
            ['command' => 'make:dto', 'name' => $qualify($model.'IndexDTO')],
            ['command' => 'make:dto', 'name' => $qualify($model.'ShowDTO')],
            ['command' => 'make:dto', 'name' => $qualify($model.'UpdateDTO')],
        ];
    }

    protected function runGenerator(string $command, string $name): int
    {
        return $this->callSilently($command, [
            'name' => $name,
        ]);
    }

    protected function validateName(string $name): ?string
    {
        if ($name === '') {
            return 'The model name is required.';
        }

        if (! preg_match('#^[A-Za-z][A-Za-z0-9_\\\\/]*$#', $name)) {
            return 'Use letters, numbers, underscores, and namespace separators (/ or \\) only.';
        }

        return null;
    }
}
