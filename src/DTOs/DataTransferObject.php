<?php

namespace Dipesh79\LaravelActionDto\DTOs;

use Illuminate\Http\Request;

abstract class DataTransferObject
{
    /**
     * @param  mixed  ...$data
     */
    abstract public function __construct(...$data);

    /**
     * Create DTO from a request.
     */
    public static function fromRequest(Request $request): static
    {
        $normalized = [];

        foreach ($request->all() as $key => $value) {
            $normalized[(string) $key] = $value;
        }

        return static::fromArray($normalized);
    }

    /**
     * Create DTO from an array.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        return new static(...$data);
    }

    /**
     * Convert DTO to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        /** @var array<string, mixed> $data */
        $data = get_object_vars($this);

        return $data;
    }
}
