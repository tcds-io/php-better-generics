<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Fixtures\UseStatements;

use Test\Tcds\Io\Generic\Fixtures\Bar;
use Test\Tcds\Io\Generic\Fixtures\Foo;

class PlainImports
{
    public function __construct(public Foo $foo, public Bar $bar)
    {
    }
}
