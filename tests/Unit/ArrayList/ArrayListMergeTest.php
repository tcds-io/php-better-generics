<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Unit\ArrayList;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Test\Tcds\Io\Generic\Fixtures\Bar;

class ArrayListMergeTest extends TestCase
{
    #[Test] public function given_an_array_list_when_foreach_is_called_then_apply_callback_to_each_array_item(): void
    {
        $first = listOf(
            new Bar("1"),
            new Bar("2"),
        );

        $merged = $first->merge(
            listOf(new Bar("3")),
            listOf(new Bar("4")),
        );

        $this->assertEquals(
            listOf(
                new Bar("1"),
                new Bar("2"),
                new Bar("3"),
                new Bar("4"),
            ),
            $merged,
        );
    }
}
