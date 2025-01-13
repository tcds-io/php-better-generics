<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Unit\ArrayList;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\ArrayList;
use Tcds\Io\Generic\Fixtures\Bar;

class ArrayListReverseTest extends TestCase
{
    #[Test] public function given_an_array_list_then_get_its_reversed(): void
    {
        $list = new ArrayList([
            new Bar("1"),
            new Bar("2"),
            new Bar("3"),
        ]);

        $reversed = $list->reverse();

        $this->assertEquals(
            new ArrayList([
                new Bar("3"),
                new Bar("2"),
                new Bar("1"),
            ]),
            $reversed,
        );
    }
}
