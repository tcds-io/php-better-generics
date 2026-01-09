<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Fixtures;

readonly class Foo
{
    public function __construct(public string $name, public Bar $bar)
    {
    }
}
