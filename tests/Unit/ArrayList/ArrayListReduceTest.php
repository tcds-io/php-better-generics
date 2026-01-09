<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Unit\ArrayList;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Test\Tcds\Io\Generic\Fixtures\Bar;

class ArrayListReduceTest extends TestCase
{
    #[Test] public function given_an_array_list_then_reduce_its_value_to_the_callable_function(): void
    {
        $list = listOf(
            new Bar("1"),
            new Bar("2"),
            new Bar("3"),
        );

        $reduced = $list->reduce(fn(int $carry, Bar $item) => $carry + intval($item->value), 0);

        $this->assertEquals(6, $reduced);
    }
}
