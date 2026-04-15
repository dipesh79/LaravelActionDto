<?php

namespace Tests\Fixtures;

use Dipesh79\LaravelActionDto\DTOs\DataTransferObject;

class SampleDataTransferObject extends DataTransferObject
{
    public string $name;

    public int $age;

    public function __construct(mixed ...$data)
    {
        $this->name = $data['name'];
        $this->age = $data['age'];
    }
}
