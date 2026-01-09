<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Fixtures;

readonly class Bar
{
    public function __construct(public string $value)
    {
    }

    public function getDuplicateName(): string
    {
        return "$this->value-$this->value";
    }
}
