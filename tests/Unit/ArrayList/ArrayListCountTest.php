<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Unit\ArrayList;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\Fixtures\Bar;

class ArrayListCountTest extends TestCase
{
    #[Test] public function given_an_array_list_then_get_the_count_of_items(): void
    {
        $list = listOf(
            new Bar("1"),
            new Bar("2"),
            new Bar("3"),
        );

        $count = $list->count();

        $this->assertEquals(3, $count);
    }

    #[Test] public function given_an_array_list_when_filter_is_not_null_then_get_the_count_of_items_matching_the_filter(): void
    {
        $list = listOf(
            new Bar("101"),
            new Bar("201"),
            new Bar("202"),
            new Bar("301"),
        );

        $count = $list->count(fn(Bar $item) => str_starts_with($item->value, "2"));

        $this->assertEquals(2, $count);
    }
}
