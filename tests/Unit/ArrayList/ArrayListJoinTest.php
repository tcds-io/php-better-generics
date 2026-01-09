<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Unit\ArrayList;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Test\Tcds\Io\Generic\Fixtures\Bar;

class ArrayListJoinTest extends TestCase
{
    #[Test] public function given_a_primitive_array_list_then_join_its_values(): void
    {
        $list = listOf("1", "2", "3");

        $noSeparator = $list->join();
        $commaSpaceSeparator = $list->join(", ");
        $dashSeparator = $list->join("-");

        $this->assertEquals("123", $noSeparator);
        $this->assertEquals("1, 2, 3", $commaSpaceSeparator);
        $this->assertEquals("1-2-3", $dashSeparator);
    }

    #[Test] public function given_an_object_array_list_then_join_its_values(): void
    {
        $list = listOf(
            new Bar("1"),
            new Bar("2"),
            new Bar("3"),
        );

        $noSeparator = $list->join(callable: fn(Bar $item) => $item->value);
        $commaSpaceSeparator = $list->join(", ", fn(Bar $item) => $item->value);
        $dashSeparator = $list->join("-", fn(Bar $item) => $item->value);

        $this->assertEquals("123", $noSeparator);
        $this->assertEquals("1, 2, 3", $commaSpaceSeparator);
        $this->assertEquals("1-2-3", $dashSeparator);
    }
}
