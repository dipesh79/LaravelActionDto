<?php

use Dipesh79\LaravelActionDto\Console\Commands\MakeActionCommand;
use Tests\Support\FakeCommandComponents;
use Tests\Support\TestApplication;

it('exposes prompt configuration for missing action name', function () {
    $command = new class extends MakeActionCommand
    {
        public function exposePromptConfiguration(): array
        {
            return $this->promptForMissingArgumentsUsing();
        }
    };

    expect($command->exposePromptConfiguration())->toHaveKey('name');
});

it('creates an action file when handle succeeds', function () {
    $basePath = TestApplication::basePath().'/action-handle-success-'.uniqid('', true);
    TestApplication::make($basePath);

    $components = new FakeCommandComponents;

    $command = new class('CreateUser', $components) extends MakeActionCommand
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
    $path = $basePath.'/app/Actions/CreateUserAction.php';

    expect($status)->toBe(MakeActionCommand::SUCCESS)
        ->and(file_exists($path))->toBeTrue()
        ->and(file_get_contents($path))->toContain('class CreateUserAction');
});

it('fails action generation for invalid input', function () {
    TestApplication::make(TestApplication::basePath().'/action-handle-invalid-'.uniqid('', true));

    $components = new FakeCommandComponents;

    $command = new class('1Invalid', $components) extends MakeActionCommand
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

    expect($command->handle())->toBe(MakeActionCommand::FAILURE)
        ->and($components->errors)->toContain('Use letters, numbers, underscores, and namespace separators (/ or \\) only.');
});

it('fails action generation when the file already exists', function () {
    $basePath = TestApplication::basePath().'/action-handle-existing-'.uniqid('', true);
    TestApplication::make($basePath);

    $path = $basePath.'/app/Actions/CreateUserAction.php';
    mkdir(dirname($path), 0755, true);
    file_put_contents($path, '<?php // existing');

    $components = new FakeCommandComponents;

    $command = new class('CreateUser', $components) extends MakeActionCommand
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

    expect($command->handle())->toBe(MakeActionCommand::FAILURE)
        ->and($components->errors)->toContain('Action already exists!');
});
