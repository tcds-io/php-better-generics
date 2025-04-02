<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Unit\ArrayList;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\ArrayList;
use Tcds\Io\Generic\Fixtures\Bar;

class ArrayListForEachTest extends TestCase
{
    #[Test] public function given_an_array_list_then_run_standard_foreach(): void
    {
        $list = new ArrayList([
            new Bar("1"),
            new Bar("2"),
            new Bar("3"),
        ]);

        $sum = 0;

        foreach ($list as $item) {
            $sum += intval($item->value);
        }

        $this->assertEquals(6, $sum);
    }

    #[Test] public function given_an_array_list_then_run_functional_foreach(): void
    {
        $list = new ArrayList([
            new Bar("1"),
            new Bar("2"),
            new Bar("3"),
        ]);

        $sum = 0;

        $list->forEach(function (Bar $item) use (&$sum): void {
            $sum += intval($item->value);
        });

        $this->assertEquals(6, $sum);
    }
}
