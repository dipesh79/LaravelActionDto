<?php

use Dipesh79\LaravelActionDto\Console\Commands\MakeCrudCommand;
use Tests\Support\FakeCommandComponents;

it('builds the expected generation plan for a model', function () {
    $command = new class extends MakeCrudCommand
    {
        public function exposeGenerationPlan(string $name): array
        {
            return $this->generationPlan($name);
        }
    };

    expect($command->exposeGenerationPlan('User'))->toBe([
        ['command' => 'make:query', 'name' => 'UserQuery'],
        ['command' => 'make:action', 'name' => 'CreateUserAction'],
        ['command' => 'make:action', 'name' => 'UpdateUserAction'],
        ['command' => 'make:action', 'name' => 'DeleteUserAction'],
        ['command' => 'make:dto', 'name' => 'UserIndexDTO'],
        ['command' => 'make:dto', 'name' => 'UserShowDTO'],
        ['command' => 'make:dto', 'name' => 'UserUpdateDTO'],
    ]);
});

it('builds a namespaced generation plan for nested models', function () {
    $command = new class extends MakeCrudCommand
    {
        public function exposeGenerationPlan(string $name): array
        {
            return $this->generationPlan($name);
        }
    };

    expect($command->exposeGenerationPlan('Admin/User'))->toBe([
        ['command' => 'make:query', 'name' => 'Admin\\UserQuery'],
        ['command' => 'make:action', 'name' => 'Admin\\CreateUserAction'],
        ['command' => 'make:action', 'name' => 'Admin\\UpdateUserAction'],
        ['command' => 'make:action', 'name' => 'Admin\\DeleteUserAction'],
        ['command' => 'make:dto', 'name' => 'Admin\\UserIndexDTO'],
        ['command' => 'make:dto', 'name' => 'Admin\\UserShowDTO'],
        ['command' => 'make:dto', 'name' => 'Admin\\UserUpdateDTO'],
    ]);
});

it('validates the model name input', function () {
    $command = new class extends MakeCrudCommand
    {
        public function exposeValidateName(string $name): ?string
        {
            return $this->validateName($name);
        }
    };

    expect($command->exposeValidateName(''))->toBe('The model name is required.')
        ->and($command->exposeValidateName('1User'))->toBe('Use letters, numbers, underscores, and namespace separators (/ or \\) only.')
        ->and($command->exposeValidateName('Admin/User'))->toBeNull();
});

it('exposes prompt configuration for missing model name', function () {
    $command = new class extends MakeCrudCommand
    {
        public function exposePromptConfiguration(): array
        {
            return $this->promptForMissingArgumentsUsing();
        }
    };

    expect($command->exposePromptConfiguration())->toHaveKey('name');
});

it('runs all generators for a valid model name', function () {
    $components = new FakeCommandComponents;

    $command = new class('User', $components) extends MakeCrudCommand
    {
        /**
         * @var array<int, array{command: string, name: string}>
         */
        public array $calls = [];

        public function __construct(private string $inputName, private FakeCommandComponents $fakeComponents)
        {
            parent::__construct();
            $this->components = $this->fakeComponents;
        }

        public function argument($key = null)
        {
            return $this->inputName;
        }

        protected function runGenerator(string $command, string $name): int
        {
            $this->calls[] = [
                'command' => $command,
                'name' => $name,
            ];

            return self::SUCCESS;
        }
    };

    expect($command->handle())->toBe(MakeCrudCommand::SUCCESS)
        ->and($command->calls)->toHaveCount(7)
        ->and($command->calls[0])->toBe(['command' => 'make:query', 'name' => 'UserQuery'])
        ->and($command->calls[6])->toBe(['command' => 'make:dto', 'name' => 'UserUpdateDTO']);
});

it('fails early when a nested generator fails', function () {
    $components = new FakeCommandComponents;

    $command = new class('User', $components) extends MakeCrudCommand
    {
        /**
         * @var array<int, array{command: string, name: string}>
         */
        public array $calls = [];

        /**
         * @var array<int, int>
         */
        private array $results = [
            self::SUCCESS,
            self::SUCCESS,
            self::FAILURE,
        ];

        public function __construct(private string $inputName, private FakeCommandComponents $fakeComponents)
        {
            parent::__construct();
            $this->components = $this->fakeComponents;
        }

        public function argument($key = null)
        {
            return $this->inputName;
        }

        protected function runGenerator(string $command, string $name): int
        {
            $this->calls[] = [
                'command' => $command,
                'name' => $name,
            ];

            return array_shift($this->results) ?? self::SUCCESS;
        }
    };

    expect($command->handle())->toBe(MakeCrudCommand::FAILURE)
        ->and($command->calls)->toHaveCount(3)
        ->and($components->warnings)->toContain('CRUD generation stopped due to an error.');
});

it('fails validation for invalid model names before running generators', function () {
    $components = new FakeCommandComponents;

    $command = new class('1User', $components) extends MakeCrudCommand
    {
        public function __construct(private string $inputName, private FakeCommandComponents $fakeComponents)
        {
            parent::__construct();
            $this->components = $this->fakeComponents;
        }

        public function argument($key = null)
        {
            return $this->inputName;
        }
    };

    expect($command->handle())->toBe(MakeCrudCommand::FAILURE)
        ->and($components->errors)->toContain('Use letters, numbers, underscores, and namespace separators (/ or \\) only.');
});

it('delegates generator execution through callSilently', function () {
    $command = new class extends MakeCrudCommand
    {
        /**
         * @var array{0: string, 1: array<string, string>}|null
         */
        public ?array $captured = null;

        public function exposeRunGenerator(string $command, string $name): int
        {
            return $this->runGenerator($command, $name);
        }

        public function callSilently($command, array $arguments = []): int
        {
            $this->captured = [$command, $arguments];

            return self::SUCCESS;
        }
    };

    expect($command->exposeRunGenerator('make:query', 'UserQuery'))->toBe(MakeCrudCommand::SUCCESS)
        ->and($command->captured)->toBe(['make:query', ['name' => 'UserQuery']]);
});
