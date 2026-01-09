<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Unit\ArrayList;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\ArrayList;
use Test\Tcds\Io\Generic\Fixtures\Bar;

class ArrayListOf extends TestCase
{
    #[Test] public function given_multiple_items_then_create_array_list_from_function(): void
    {
        $list = listOf(
            new Bar("1"),
            new Bar("2"),
            new Bar("3"),
            new Bar("4"),
        );

        $this->assertEquals(
            new ArrayList([
                new Bar("1"),
                new Bar("2"),
                new Bar("3"),
                new Bar("4"),
            ]),
            $list,
        );
    }
}
