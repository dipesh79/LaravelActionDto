<?php

use Tests\Fixtures\SampleAction;
use Tests\Fixtures\SampleDataTransferObject;

it('delegates execute to handle', function () {
    $dto = SampleDataTransferObject::fromArray([
        'name' => 'Taylor',
        'age' => 34,
    ]);

    $action = new SampleAction;

    expect($action->execute($dto))->toBe('handled')
        ->and($action->receivedDto)->toBe($dto);
});
