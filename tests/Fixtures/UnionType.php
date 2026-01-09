<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Fixtures;

readonly class UnionType
{
    public function __construct(public string|int $value)
    {
    }
}
