<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Unit\ArrayList;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\Fixtures\Bar;

class ArrayListFirstLastTest extends TestCase
{
    #[Test] public function given_an_then_get_its_first_element(): void
    {
        $list = listOf(
            new Bar("1"),
            new Bar("2"),
            new Bar("3"),
        );

        $first = $list->first();

        $this->assertEquals(new Bar("1"), $first);
    }

    #[Test] public function given_an_then_get_its_first_element_meeting_the_condition(): void
    {
        $list = listOf(
            new Bar("1"),
            new Bar("23"),
            new Bar("24"),
            new Bar("3"),
        );

        $first = $list->first(fn(Bar $item) => str_starts_with($item->value, "2"));

        $this->assertEquals(new Bar("23"), $first);
    }

    #[Test] public function given_an_then_get_its_last_element(): void
    {
        $list = listOf(
            new Bar("1"),
            new Bar("2"),
            new Bar("3"),
        );

        $last = $list->last();

        $this->assertEquals(new Bar("3"), $last);
    }

    #[Test] public function given_an_then_get_its_last_element_meeting_the_condition(): void
    {
        $list = listOf(
            new Bar("1"),
            new Bar("32"),
            new Bar("36"),
            new Bar("4"),
        );

        $last = $list->last(fn(Bar $item) => str_starts_with($item->value, "3"));

        $this->assertEquals(new Bar("36"), $last);
    }
}
