<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Unit\ArrayList;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\Fixtures\Bar;

class ArrayListMapTest extends TestCase
{
    #[Test] public function given_an_array_list_when_map_is_called_then_apply_callback_to_each_item_and_return_new_array_list(): void
    {
        $list = listOf(
            new Bar("1"),
            new Bar("2"),
            new Bar("3"),
        );

        $mapped = $list->map(fn(Bar $item) => $item->value);

        $this->assertEquals(listOf("1", "2", "3"), $mapped);
    }

    #[Test] public function given_multiple_array_list_when_flat_map_is_called_then_apply_callback_to_each_item_and_return_new_array_list(): void
    {
        $list = listOf(
            [new Bar("1"), new Bar("2")],
            [new Bar("3")],
        );

        $mapped = $list->flatMap(fn(Bar $item) => $item->value);

        $this->assertEquals(listOf("1", "2", "3"), $mapped);
    }

    #[Test] public function given_multiple_array_list_then_flatten_into_a_single_list(): void
    {
        $list = listOf(
            [new Bar("1"), new Bar("2")],
            [new Bar("3")],
        );

        $flatten = $list->flatten();

        $this->assertEquals(listOf(
            new Bar("1"),
            new Bar("2"),
            new Bar("3"),
        ), $flatten);
    }
}
