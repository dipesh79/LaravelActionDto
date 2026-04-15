<?php

use Dipesh79\LaravelActionDto\Console\Commands\MakeDtoCommand;
use Tests\Support\FakeCommandComponents;
use Tests\Support\TestApplication;

it('exposes prompt configuration for missing dto name', function () {
    $command = new class extends MakeDtoCommand
    {
        public function exposePromptConfiguration(): array
        {
            return $this->promptForMissingArgumentsUsing();
        }
    };

    expect($command->exposePromptConfiguration())->toHaveKey('name');
});

it('creates a dto file when handle succeeds', function () {
    $basePath = TestApplication::basePath().'/dto-handle-success-'.uniqid('', true);
    TestApplication::make($basePath);

    $components = new FakeCommandComponents;

    $command = new class('UserShow', $components) extends MakeDtoCommand
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

    $status = $command->handle();
    $path = $basePath.'/app/DTOs/UserShowDTO.php';

    expect($status)->toBe(MakeDtoCommand::SUCCESS)
        ->and(file_exists($path))->toBeTrue()
        ->and(file_get_contents($path))->toContain('class UserShowDTO');
});

it('fails dto generation for invalid input', function () {
    TestApplication::make(TestApplication::basePath().'/dto-handle-invalid-'.uniqid('', true));

    $components = new FakeCommandComponents;

    $command = new class('!bad', $components) extends MakeDtoCommand
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

    expect($command->handle())->toBe(MakeDtoCommand::FAILURE)
        ->and($components->errors)->toContain('Use letters, numbers, underscores, and namespace separators (/ or \\) only.');
});

it('fails dto generation when the file already exists', function () {
    $basePath = TestApplication::basePath().'/dto-handle-existing-'.uniqid('', true);
    TestApplication::make($basePath);

    $path = $basePath.'/app/DTOs/UserShowDTO.php';
    mkdir(dirname($path), 0755, true);
    file_put_contents($path, '<?php // existing');

    $components = new FakeCommandComponents;

    $command = new class('UserShow', $components) extends MakeDtoCommand
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

    expect($command->handle())->toBe(MakeDtoCommand::FAILURE)
        ->and($components->errors)->toContain('DTO already exists!');
});
