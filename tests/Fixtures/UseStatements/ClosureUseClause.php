<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Fixtures\UseStatements;

use Test\Tcds\Io\Generic\Fixtures\Foo;

class ClosureUseClause
{
    public function bind(Foo $foo): callable
    {
        // Closure `use ($foo)` must NOT be picked up as an import.
        return function () use ($foo) {
            return $foo;
        };
    }
}
