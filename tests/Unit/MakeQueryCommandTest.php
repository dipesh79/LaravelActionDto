<?php

use Dipesh79\LaravelActionDto\Console\Commands\MakeQueryCommand;
use Tests\Support\TestApplication;

it('qualifies query classes using the configured namespace and suffix', function () {
    $basePath = TestApplication::basePath().'/query-'.uniqid('', true);
    TestApplication::make($basePath);

    $command = new class extends MakeQueryCommand
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

    expect($command->exposeQualifyClass('Admin/GetUsers'))->toBe('App\\Queries\\Admin\\GetUsersQuery')
        ->and($command->exposeGetPath('App\\Queries\\Admin\\GetUsersQuery'))->toBe($basePath.'/app/Queries/Admin/GetUsersQuery.php');
});

it('removes the query suffix when it is disabled', function () {
    TestApplication::make(TestApplication::basePath().'/query-suffix-off-'.uniqid('', true), [
        'laravel-action-dto' => [
            'queries' => [
                'suffix' => false,
            ],
        ],
    ]);

    $command = new class extends MakeQueryCommand
    {
        public function exposeQualifyClass(string $name): string
        {
            return $this->qualifyClass($name);
        }
    };

    expect($command->exposeQualifyClass('GetUsersQuery'))->toBe('App\\Queries\\GetUsers');
});

it('uses a published query stub when present', function () {
    $basePath = TestApplication::basePath().'/query-stub-'.uniqid('', true);
    TestApplication::make($basePath);

    $stubPath = $basePath.'/stubs/action-dto/query.stub';
    mkdir(dirname($stubPath), 0755, true);
    file_put_contents($stubPath, 'custom query stub');

    $command = new class extends MakeQueryCommand
    {
        public function exposeGetStub(): string
        {
            return $this->getStub();
        }
    };

    expect($command->exposeGetStub())->toBe('custom query stub');
});

it('validates empty query names', function () {
    $command = new class extends MakeQueryCommand
    {
        public function exposeValidateName(string $name): ?string
        {
            return $this->validateName($name);
        }
    };

    expect($command->exposeValidateName(''))->toBe('The query name is required.');
});

it('throws when the default query stub cannot be loaded', function () {
    TestApplication::make(TestApplication::basePath().'/query-stub-missing-'.uniqid('', true));

    $stubPath = __DIR__.'/../../src/stubs/query.stub';
    $backupPath = $stubPath.'.bak';

    if (file_exists($backupPath)) {
        unlink($backupPath);
    }

    rename($stubPath, $backupPath);

    try {
        set_error_handler(static fn (): bool => true, E_WARNING);

        $command = new class extends MakeQueryCommand
        {
            public function exposeGetStub(): string
            {
                return $this->getStub();
            }
        };

        $command->exposeGetStub();

        $this->fail('Expected RuntimeException was not thrown.');
    } catch (RuntimeException $exception) {
        expect($exception->getMessage())->toBe('Unable to load query stub.');
    } finally {
        restore_error_handler();
        rename($backupPath, $stubPath);
    }
});
