<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Unit\ArrayList;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\Fixtures\Bar;

class ArrayListSortTest extends TestCase
{
    #[Test] public function given_an_array_list_then_sort_without_comparing_callback(): void
    {
        $list = listOf(
            new Bar("4"),
            new Bar("2"),
            new Bar("1"),
            new Bar("3"),
        );

        $sorted = $list->sort();

        $this->assertEquals(listOf(new Bar("4"), new Bar("2"), new Bar("1"), new Bar("3")), $list);
        $this->assertEquals(listOf(new Bar("1"), new Bar("2"), new Bar("3"), new Bar("4")), $sorted);
    }

    #[Test] public function given_an_array_list_then_sort_with_comparing_callback(): void
    {
        $list = listOf(
            new Bar("44"),
            new Bar("26"),
            new Bar("17"),
            new Bar("35"),
        );

        $sorted = $list->sort(fn(Bar $a, Bar $b) => intval(strrev($a->value)) - intval(strrev($b->value)));

        $this->assertEquals(listOf(new Bar("44"), new Bar("26"), new Bar("17"), new Bar("35")), $list);
        $this->assertEquals(listOf(new Bar("44"), new Bar("35"), new Bar("26"), new Bar("17")), $sorted);
    }
}
