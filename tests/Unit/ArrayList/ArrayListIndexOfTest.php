<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Unit\ArrayList;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\ArrayList;
use Tcds\Io\Generic\ExpectThrows;

class ArrayListIndexOfTest extends TestCase
{
    use ExpectThrows;

    #[Test] public function given_an_item_when_it_is_present_in_the_list_then_return_its_index(): void
    {
        $list = new ArrayList(["1", "2", "3"]);

        $index = $list->indexOf("2");

        $this->assertEquals(1, $index);
    }

    #[Test] public function given_an_item_when_it_is_not_present_in_the_list_then_throw_exception(): void
    {
        $list = new ArrayList(["1", "2", "3"]);

        $exception = $this->expectThrows(fn() => $list->indexOf("10"));

        $this->assertEquals("No matching item found in the list", $exception->getMessage());
    }
}
