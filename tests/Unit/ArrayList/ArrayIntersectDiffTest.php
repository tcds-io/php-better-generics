<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Unit\ArrayList;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\ArrayList;

class ArrayIntersectDiffTest extends TestCase
{
    #[Test] public function given_two_array_list_then_return_the_intersection_between_the_first_and_others(): void
    {
        $first = listOf("1", "2", "3");
        $second = listOf("3", "4", "5");

        $diff = $first->intersect($second);

        $this->assertEquals(new ArrayList(["3"]), $diff);
    }
}
