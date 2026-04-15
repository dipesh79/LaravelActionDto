<?php

use Dipesh79\LaravelActionDto\Console\Commands\MakeActionCommand;
use Tests\Support\TestApplication;

it('qualifies action classes using the configured namespace and suffix', function () {
    $basePath = TestApplication::basePath().'/action-'.uniqid('', true);
    TestApplication::make($basePath);

    $command = new class extends MakeActionCommand
    {
        public function exposeQualifyClass(string $name): string
        {
            return $this->qualifyClass($name);
        }

        public function exposeGetPath(string $class): string
        {
            return $this->getPath($class);
        }

        public function exposeGetStub(): string
        {
            return $this->getStub();
        }
    };

    expect($command->exposeQualifyClass('Admin/CreateUser'))->toBe('App\\Actions\\Admin\\CreateUserAction')
        ->and($command->exposeGetPath('App\\Actions\\Admin\\CreateUserAction'))->toBe($basePath.'/app/Actions/Admin/CreateUserAction.php');
});

it('removes the action suffix when it is disabled', function () {
    TestApplication::make(TestApplication::basePath().'/action-suffix-off-'.uniqid('', true), [
        'laravel-action-dto' => [
            'actions' => [
                'suffix' => false,
            ],
        ],
    ]);

    $command = new class extends MakeActionCommand
    {
        public function exposeQualifyClass(string $name): string
        {
            return $this->qualifyClass($name);
        }
    };

    expect($command->exposeQualifyClass('CreateUserAction'))->toBe('App\\Actions\\CreateUser');
});

it('uses a published action stub when present', function () {
    $basePath = TestApplication::basePath().'/action-stub-'.uniqid('', true);
    TestApplication::make($basePath);

    $stubPath = $basePath.'/stubs/action-dto/action.stub';
    mkdir(dirname($stubPath), 0755, true);
    file_put_contents($stubPath, 'custom action stub');

    $command = new class extends MakeActionCommand
    {
        public function exposeGetStub(): string
        {
            return $this->getStub();
        }
    };

    expect($command->exposeGetStub())->toBe('custom action stub');
});

it('validates empty action names', function () {
    $command = new class extends MakeActionCommand
    {
        public function exposeValidateName(string $name): ?string
        {
            return $this->validateName($name);
        }
    };

    expect($command->exposeValidateName(''))->toBe('The action name is required.');
});

it('throws when the default action stub cannot be loaded', function () {
    TestApplication::make(TestApplication::basePath().'/action-stub-missing-'.uniqid('', true));

    $stubPath = __DIR__.'/../../src/stubs/action.stub';
    $backupPath = $stubPath.'.bak';

    if (file_exists($backupPath)) {
        unlink($backupPath);
    }

    rename($stubPath, $backupPath);

    try {
        set_error_handler(static fn (): bool => true, E_WARNING);

        $command = new class extends MakeActionCommand
        {
            public function exposeGetStub(): string
            {
                return $this->getStub();
            }
        };

        $command->exposeGetStub();

        $this->fail('Expected RuntimeException was not thrown.');
    } catch (RuntimeException $exception) {
        expect($exception->getMessage())->toBe('Unable to load action stub.');
    } finally {
        restore_error_handler();
        rename($backupPath, $stubPath);
    }
});
