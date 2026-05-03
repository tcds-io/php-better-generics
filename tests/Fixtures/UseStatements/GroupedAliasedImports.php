<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Fixtures\UseStatements;

use Test\Tcds\Io\Generic\Fixtures\{Bar as RenamedBar, Foo};

class GroupedAliasedImports
{
    public function __construct(public Foo $foo, public RenamedBar $bar)
    {
    }
}
