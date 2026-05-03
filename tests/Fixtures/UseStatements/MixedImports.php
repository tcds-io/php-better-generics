<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Fixtures\UseStatements;

use Test\Tcds\Io\Generic\Fixtures\Foo;

use function array_map;

use const PHP_INT_MAX;

class MixedImports
{
    public int $cap = PHP_INT_MAX;

    public function __construct(public Foo $foo)
    {
    }

    /**
     * @param list<int> $values
     * @return list<int>
     */
    public function double(array $values): array
    {
        return array_map(static fn (int $v): int => $v * 2, $values);
    }
}
