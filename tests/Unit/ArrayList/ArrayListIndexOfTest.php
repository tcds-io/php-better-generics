<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Unit\ArrayList;

use OutOfRangeException;
use PHPUnit\Framework\Attributes\Test;
use Test\Tcds\Io\Generic\BetterGenericTestCase;

class ArrayListIndexOfTest extends BetterGenericTestCase
{
    #[Test] public function given_an_item_when_it_is_present_in_the_list_then_return_its_index(): void
    {
        $list = listOf("1", "2", "3");

        $index = $list->indexOf("2");

        $this->assertEquals(1, $index);
    }

    #[Test] public function given_an_item_when_it_is_not_present_in_the_list_then_throw_exception(): void
    {
        $list = listOf("1", "2", "3");

        $exception = $this->expectThrows(OutOfRangeException::class, fn() => $list->indexOf("10"));

        $this->assertEquals("No matching item found in the list", $exception->getMessage());
    }
}
