<?php

declare(strict_types=1);

namespace Dipesh79\LaravelActionDto\Actions;

use Dipesh79\LaravelActionDto\Actions\Contracts\Action;

abstract class BaseAction implements Action
{
    /**
     * Handle the action logic.
     */
    abstract public function handle(object $dto): mixed;

    /**
     * Execute the action.
     */
    public function execute(object $dto): mixed
    {
        return $this->handle($dto);
    }
}
