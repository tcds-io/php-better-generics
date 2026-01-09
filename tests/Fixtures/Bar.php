<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Fixtures;

readonly class Bar
{
    public function __construct(public string $name)
    {
    }

    public function getDuplicateName(): string
    {
        return "$this->name-$this->name";
    }
}
