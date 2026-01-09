<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Unit\ArrayList;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\Fixtures\Bar;

class ArrayListFilterTest extends TestCase
{
    #[Test] public function given_an_array_list_without_closure_filter_then_remove_empty_values(): void
    {
        $list = listOf(
            "",
            new Bar("2"),
            null,
            "foo",
            0,
            10,
        );

        $filtered = $list->filter();

        $this->assertEquals(
            listOf(
                new Bar("2"),
                "foo",
                10,
            ),
            $filtered,
        );
    }

    #[Test] public function given_an_array_list_when_filter_closure_is_specified_then_filter_list(): void
    {
        $list = listOf(
            new Bar("100"),
            new Bar("201"),
            new Bar("202"),
            new Bar("300"),
        );

        $filtered = $list->filter(fn(Bar $item) => str_starts_with($item->value, "2"));

        $this->assertEquals(
            listOf(
                new Bar("201"),
                new Bar("202"),
            ),
            $filtered,
        );
    }
}
