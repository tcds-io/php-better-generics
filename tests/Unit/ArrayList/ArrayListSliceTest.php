<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Unit\ArrayList;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\Fixtures\Bar;

class ArrayListSliceTest extends TestCase
{
    #[Test] public function given_an_array_list_when_the_offset_is_positive_then_get_a_sliced_array_list(): void
    {
        $list = listOf(
            new Bar("1"),
            new Bar("2"),
            new Bar("3"),
            new Bar("4"),
            new Bar("5"),
            new Bar("6"),
        );

        $slice = $list->slice(2, 2);

        $this->assertEquals(
            listOf(
                new Bar("3"),
                new Bar("4"),
            ),
            $slice,
        );
    }

    #[Test] public function given_an_array_list_when_the_offset_is_negative_then_get_a_sliced_array_list(): void
    {
        $list = listOf(
            new Bar("1"),
            new Bar("2"),
            new Bar("3"),
            new Bar("4"),
            new Bar("5"),
            new Bar("6"),
        );

        $slice = $list->slice(-2, 2);

        $this->assertEquals(
            listOf(
                new Bar("5"),
                new Bar("6"),
            ),
            $slice,
        );
    }
}
