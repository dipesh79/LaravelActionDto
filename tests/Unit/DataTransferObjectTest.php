<?php

use Illuminate\Http\Request;
use Tests\Fixtures\SampleDataTransferObject;

it('creates a data transfer object from an array', function () {
    $dto = SampleDataTransferObject::fromArray([
        'name' => 'Taylor',
        'age' => 34,
    ]);

    expect($dto)->toBeInstanceOf(SampleDataTransferObject::class)
        ->and($dto->toArray())->toBe([
            'name' => 'Taylor',
            'age' => 34,
        ]);
});

it('creates a data transfer object from a request', function () {
    $request = Request::create('/users', 'POST', [
        'name' => 'Taylor',
        'age' => 34,
    ]);

    $dto = SampleDataTransferObject::fromRequest($request);

    expect($dto->toArray())->toBe([
        'name' => 'Taylor',
        'age' => 34,
    ]);
});
