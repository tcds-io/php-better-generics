<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Unit\ArrayList;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\ArrayList;
use Tcds\Io\Generic\Fixtures\Bar;

class ArrayListEmptyTest extends TestCase
{
    #[Test] public function given_an_array_list_when_it_contains_no_items_then_it_is_empty(): void
    {
        $list = new ArrayList([]);

        $isEmpty = $list->isEmpty();
        $isNotEmpty = $list->isNotEmpty();

        $this->assertTrue($isEmpty);
        $this->assertFalse($isNotEmpty);
    }

    #[Test] public function given_an_array_list_when_it_contains_items_then_it_is_not_empty(): void
    {
        $list = listOf(
            new Bar("1"),
            new Bar("2"),
            new Bar("3"),
            new Bar("4"),
        );

        $isEmpty = $list->isEmpty();
        $isNotEmpty = $list->isNotEmpty();

        $this->assertFalse($isEmpty);
        $this->assertTrue($isNotEmpty);
    }
}
