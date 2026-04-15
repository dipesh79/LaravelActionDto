# Laravel Action DTO

[![Tests](https://github.com/Dipesh79/LaravelActionDto/actions/workflows/tests.yml/badge.svg?branch=1.x)](https://github.com/Dipesh79/LaravelActionDto/actions/workflows/tests.yml?query=branch%3A1.x)
[![Coverage](https://codecov.io/gh/Dipesh79/LaravelActionDto/branch/1.x/graph/badge.svg)](https://codecov.io/gh/Dipesh79/LaravelActionDto/tree/1.x)

A minimal Action + DTO pattern for Laravel with generator commands for a fast, consistent workflow.

## Features

- Generate Action classes via `php artisan make:action`
- Generate DTO classes via `php artisan make:dto`
- Generate Query classes via `php artisan make:query`
- Generate CRUD set via `php artisan make:crud`
- Base `BaseAction` and `DataTransferObject` classes included
- Configurable namespaces, directories, and suffixes
- Custom stub override support

## Requirements

- PHP `^8.1`
- Laravel application (package is auto-discovered through Composer)

## Installation

```bash
composer require dipesh79/laravel-action-dto
```

## Publishing the Configuration

If you want to customize the default namespaces, directories, or suffix behavior, publish the package configuration file:

```bash
php artisan vendor:publish --tag=laravel-action-dto-config
```

This will publish the following file into your application:

- `config/laravel-action-dto.php`

After publishing, you may adjust the defaults to match your app's structure.

## Configuration

File: `config/laravel-action-dto.php`

### Actions

Use the `actions` section to control where generated Action classes are placed.

| Key | Default | Description |
| --- | --- | --- |
| `actions.namespace` | `App\\Actions` | Namespace used for generated actions |
| `actions.directory` | `app_path('Actions')` | Filesystem directory for generated actions |
| `actions.suffix` | `true` | Appends `Action` to generated class names |

### DTOs

Use the `dto` section to control where generated DTO classes are placed.

| Key | Default | Description |
| --- | --- | --- |
| `dto.namespace` | `App\\DTOs` | Namespace used for generated DTOs |
| `dto.directory` | `app_path('DTOs')` | Filesystem directory for generated DTOs |
| `dto.suffix` | `true` | Appends `DTO` to generated class names |

### Queries

Use the `queries` section to control where generated Query classes are placed.

| Key | Default | Description |
| --- | --- | --- |
| `queries.namespace` | `App\\Queries` | Namespace used for generated queries |
| `queries.directory` | `app_path('Queries')` | Filesystem directory for generated queries |
| `queries.suffix` | `true` | Appends `Query` to generated class names |

## Customizing the Generated Stubs

You can override the default generator stubs by creating these files in your Laravel application root:

- `stubs/action-dto/action.stub`
- `stubs/action-dto/dto.stub`
- `stubs/action-dto/query.stub`

When these files exist, the package will use them instead of the bundled stubs.

## Available Commands

```bash
php artisan make:action CreateUser
php artisan make:dto CreateUser
php artisan make:query GetUsers
php artisan make:crud User
```

Interactive mode is supported with Laravel Prompts. If you run a command without the `name` argument, it will ask for one with validation.

```bash
php artisan make:action
php artisan make:dto
php artisan make:query
php artisan make:crud
```

## Quick Usage

### 1) Generate files

```bash
php artisan make:crud User
```

By default this generates:

- `app/Queries/UserQuery.php`
- `app/Actions/CreateUserAction.php`
- `app/Actions/UpdateUserAction.php`
- `app/Actions/DeleteUserAction.php`
- `app/DTOs/UserIndexDTO.php`
- `app/DTOs/UserShowDTO.php`
- `app/DTOs/UserUpdateDTO.php`

### 2) Define your DTO

```php
<?php

namespace App\DTOs;

use Dipesh79\LaravelActionDto\DTOs\DataTransferObject;

class CreateUserDTO extends DataTransferObject
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {}
}
```

### 3) Implement your Action

```php
<?php

namespace App\Actions;

use App\DTOs\CreateUserDTO;
use App\Models\User;
use Dipesh79\LaravelActionDto\Actions\BaseAction;
use Illuminate\Support\Facades\Hash;

class CreateUserAction extends BaseAction
{
    public function handle(object $dto): mixed
    {
        /** @var CreateUserDTO $dto */
        return User::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
        ]);
    }
}
```

### 4) Use from a controller

```php
<?php

use App\Actions\CreateUserAction;
use App\DTOs\CreateUserDTO;
use Illuminate\Http\Request;

public function store(Request $request, CreateUserAction $action)
{
    $dto = CreateUserDTO::fromRequest($request);

    $user = $action->execute($dto);

    return response()->json($user, 201);
}
```


## Base Classes

### `DataTransferObject`

- `fromRequest(Request $request): static`
- `fromArray(array $data): static`
- `toArray(): array`

### `BaseAction`

- `execute(object $dto): mixed`
- `handle(object $dto): mixed` (implement in your action)

## Running Tests

From your Laravel app root:

```bash
php artisan test
```

## Contributing

Issues and PRs are welcome.

If you contribute:

1. Keep generated outputs and config behavior consistent.
2. Add/adjust tests for command or config changes.
3. Update this README when behavior changes.

## License

MIT

