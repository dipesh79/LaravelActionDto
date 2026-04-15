<?php

declare(strict_types=1);

namespace Dipesh79\LaravelActionDto\Actions\Contracts;

interface Action
{
    public function execute(object $dto): mixed;
}
