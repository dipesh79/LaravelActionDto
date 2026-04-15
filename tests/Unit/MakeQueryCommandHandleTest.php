<?php

use Dipesh79\LaravelActionDto\Console\Commands\MakeQueryCommand;
use Tests\Support\FakeCommandComponents;
use Tests\Support\TestApplication;

it('exposes prompt configuration for missing query name', function () {
    $command = new class extends MakeQueryCommand
    {
        public function exposePromptConfiguration(): array
        {
            return $this->promptForMissingArgumentsUsing();
        }
    };

    expect($command->exposePromptConfiguration())->toHaveKey('name');
});

it('creates a query file when handle succeeds', function () {
    $basePath = TestApplication::basePath().'/query-handle-success-'.uniqid('', true);
    TestApplication::make($basePath);

    $components = new FakeCommandComponents;

    $command = new class('User', $components) extends MakeQueryCommand
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
    $path = $basePath.'/app/Queries/UserQuery.php';

    expect($status)->toBe(MakeQueryCommand::SUCCESS)
        ->and(file_exists($path))->toBeTrue()
        ->and(file_get_contents($path))->toContain('class UserQuery')
        ->and(file_get_contents($path))->not->toContain('extends BaseAction');
});

it('fails query generation for invalid input', function () {
    TestApplication::make(TestApplication::basePath().'/query-handle-invalid-'.uniqid('', true));

    $components = new FakeCommandComponents;

    $command = new class('!bad', $components) extends MakeQueryCommand
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

    expect($command->handle())->toBe(MakeQueryCommand::FAILURE)
        ->and($components->errors)->toContain('Use letters, numbers, underscores, and namespace separators (/ or \\) only.');
});

it('fails query generation when the file already exists', function () {
    $basePath = TestApplication::basePath().'/query-handle-existing-'.uniqid('', true);
    TestApplication::make($basePath);

    $path = $basePath.'/app/Queries/UserQuery.php';
    mkdir(dirname($path), 0755, true);
    file_put_contents($path, '<?php // existing');

    $components = new FakeCommandComponents;

    $command = new class('User', $components) extends MakeQueryCommand
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

    expect($command->handle())->toBe(MakeQueryCommand::FAILURE)
        ->and($components->errors)->toContain('Query already exists!');
});
