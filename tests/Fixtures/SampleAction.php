<?php

namespace Tests\Fixtures;

use Dipesh79\LaravelActionDto\Actions\BaseAction;

class SampleAction extends BaseAction
{
    public ?object $receivedDto = null;

    public function handle(object $dto): mixed
    {
        $this->receivedDto = $dto;

        return 'handled';
    }
}
