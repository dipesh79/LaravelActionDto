![Package Image](https://banners.beyondco.de/LaravelActionDto.png?theme=light&packageManager=composer+require&packageName=dipesh79%2Flaravel-action-dto&pattern=architect&style=style_1&description=Generate+Actions%2C+DTOs%2C+and+Queries+for+Laravel&md=1&showWatermark=1&fontSize=100px&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg)

# Laravel Action DTO

[![Latest Stable Version](https://img.shields.io/packagist/v/dipesh79/laravel-action-dto.svg?style=flat-square)](https://packagist.org/packages/dipesh79/laravel-action-dto)
[![Total Downloads](https://img.shields.io/packagist/dt/dipesh79/laravel-action-dto)](https://packagist.org/packages/dipesh79/laravel-action-dto)
[![License](https://img.shields.io/packagist/l/dipesh79/laravel-action-dto)](https://packagist.org/packages/dipesh79/laravel-action-dto)
[![Tests](https://github.com/Dipesh79/LaravelActionDto/actions/workflows/tests.yml/badge.svg?branch=1.x)](https://github.com/Dipesh79/LaravelActionDto/actions/workflows/tests.yml?query=branch%3A1.x)
[![Coverage](https://codecov.io/gh/Dipesh79/LaravelActionDto/branch/1.x/graph/badge.svg)](https://codecov.io/gh/Dipesh79/LaravelActionDto/tree/1.x)

This Laravel package helps you generate Action, DTO, Query, and CRUD boilerplate with a consistent structure.

## Quick Start

### Install Using Composer

```bash
composer require dipesh79/laravel-action-dto
```

### Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag=laravel-action-dto-config
```

## Available Commands

```bash
php artisan make:action CreateUser
php artisan make:dto CreateUser
php artisan make:query User
php artisan make:crud User
```

Running `make:crud User` generates:

- `app/Queries/UserQuery.php`
- `app/Actions/CreateUserAction.php`
- `app/Actions/UpdateUserAction.php`
- `app/Actions/DeleteUserAction.php`
- `app/DTOs/UserIndexDTO.php`
- `app/DTOs/UserShowDTO.php`
- `app/DTOs/UserUpdateDTO.php`

## Official Documentation

Documentation can be found on my [website](https://khanaldipesh.com.np/package/laravel-action-dto).

## License

[MIT](https://choosealicense.com/licenses/mit/)

## Author

- [@Dipesh79](https://www.github.com/Dipesh79)

## Support

For support, email dipeshkhanal79[at]gmail[dot]com.

