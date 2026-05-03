<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Fixtures\UseStatements;

use Test\Tcds\Io\Generic\Fixtures\Bar as Renamed;
use Test\Tcds\Io\Generic\Fixtures\Foo;

class AliasedImports
{
    public function __construct(public Foo $foo, public Renamed $renamed)
    {
    }
}
