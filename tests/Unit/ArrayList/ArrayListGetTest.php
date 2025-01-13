<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Unit\ArrayList;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\ArrayList;
use Tcds\Io\Generic\ExpectThrows;

class ArrayListGetTest extends TestCase
{
    use ExpectThrows;

    #[Test] public function given_an_index_when_the_related_item_exists_then_return_existing_item(): void
    {
        $list = new ArrayList(["1", "2", "3"]);

        $item = $list->get(1);

        $this->assertEquals("2", $item);
    }

    #[Test] public function given_an_index_when_the_related_item_does_not_exist_then_throw_exception(): void
    {
        $list = new ArrayList(["1", "2", "3"]);

        $exception = $this->expectThrows(fn() => $list->get(10));

        $this->assertEquals("Index 10 does not exist", $exception->getMessage());
    }
}
