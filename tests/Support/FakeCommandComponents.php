<?php

namespace Tests\Support;

class FakeCommandComponents
{
    /**
     * @var array<int, string>
     */
    public array $errors = [];

    /**
     * @var array<int, string>
     */
    public array $infos = [];

    /**
     * @var array<int, string>
     */
    public array $warnings = [];

    /**
     * @var array<int, array{0: string, 1: string}>
     */
    public array $details = [];

    /**
     * @var array<int, string>
     */
    public array $tasks = [];

    public function error(string $message): void
    {
        $this->errors[] = $message;
    }

    public function info(string $message): void
    {
        $this->infos[] = $message;
    }

    public function warn(string $message): void
    {
        $this->warnings[] = $message;
    }

    public function twoColumnDetail(string $first, string $second): void
    {
        $this->details[] = [$first, $second];
    }

    public function task(string $title, callable $callback): bool
    {
        $this->tasks[] = $title;
        $result = $callback();

        return is_bool($result) ? $result : true;
    }
}
