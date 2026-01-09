<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Unit\ArrayList;

use OutOfRangeException;
use PHPUnit\Framework\Attributes\Test;
use Test\Tcds\Io\Generic\BetterGenericTestCase;

class ArrayListGetTest extends BetterGenericTestCase
{
    #[Test] public function given_an_index_when_the_related_item_exists_then_return_existing_item(): void
    {
        $list = listOf("1", "2", "3");

        $item = $list->get(1);

        $this->assertEquals("2", $item);
    }

    #[Test] public function given_an_index_when_the_related_item_does_not_exist_then_throw_exception(): void
    {
        $list = listOf("1", "2", "3");

        $exception = $this->expectThrows(OutOfRangeException::class, fn () => $list->get(10));

        $this->assertEquals("Index 10 does not exist", $exception->getMessage());
    }
}
