<?php

use Dipesh79\LaravelActionDto\Console\Commands\MakeDtoCommand;
use Tests\Support\TestApplication;

it('qualifies dto classes using the configured namespace and suffix', function () {
    $basePath = TestApplication::basePath().'/dto-'.uniqid('', true);
    TestApplication::make($basePath);

    $command = new class extends MakeDtoCommand
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

    expect($command->exposeQualifyClass('Admin/CreateUser'))->toBe('App\\DTOs\\Admin\\CreateUserDTO')
        ->and($command->exposeGetPath('App\\DTOs\\Admin\\CreateUserDTO'))->toBe($basePath.'/app/DTOs/Admin/CreateUserDTO.php');
});

it('removes the dto suffix when it is disabled', function () {
    TestApplication::make(TestApplication::basePath().'/dto-suffix-off-'.uniqid('', true), [
        'laravel-action-dto' => [
            'dto' => [
                'suffix' => false,
            ],
        ],
    ]);

    $command = new class extends MakeDtoCommand
    {
        public function exposeQualifyClass(string $name): string
        {
            return $this->qualifyClass($name);
        }
    };

    expect($command->exposeQualifyClass('CreateUserDTO'))->toBe('App\\DTOs\\CreateUser');
});

it('uses a published dto stub when present', function () {
    $basePath = TestApplication::basePath().'/dto-stub-'.uniqid('', true);
    TestApplication::make($basePath);

    $stubPath = $basePath.'/stubs/action-dto/dto.stub';
    mkdir(dirname($stubPath), 0755, true);
    file_put_contents($stubPath, 'custom dto stub');

    $command = new class extends MakeDtoCommand
    {
        public function exposeGetStub(): string
        {
            return $this->getStub();
        }
    };

    expect($command->exposeGetStub())->toBe('custom dto stub');
});

it('validates empty dto names', function () {
    $command = new class extends MakeDtoCommand
    {
        public function exposeValidateName(string $name): ?string
        {
            return $this->validateName($name);
        }
    };

    expect($command->exposeValidateName(''))->toBe('The DTO name is required.');
});

it('throws when the default dto stub cannot be loaded', function () {
    TestApplication::make(TestApplication::basePath().'/dto-stub-missing-'.uniqid('', true));

    $stubPath = __DIR__.'/../../src/stubs/dto.stub';
    $backupPath = $stubPath.'.bak';

    if (file_exists($backupPath)) {
        unlink($backupPath);
    }

    rename($stubPath, $backupPath);

    try {
        set_error_handler(static fn (): bool => true, E_WARNING);

        $command = new class extends MakeDtoCommand
        {
            public function exposeGetStub(): string
            {
                return $this->getStub();
            }
        };

        $command->exposeGetStub();

        $this->fail('Expected RuntimeException was not thrown.');
    } catch (RuntimeException $exception) {
        expect($exception->getMessage())->toBe('Unable to load DTO stub.');
    } finally {
        restore_error_handler();
        rename($backupPath, $stubPath);
    }
});
