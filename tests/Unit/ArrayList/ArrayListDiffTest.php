<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Unit\ArrayList;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\ArrayList;

class ArrayListDiffTest extends TestCase
{
    #[Test] public function given_two_array_list_then_return_a_new_list_with_diff_from_first_list_to_others(): void
    {
        $first = new ArrayList(["1", "2", "3"]);
        $second = new ArrayList(["2", "4", "5"]);

        $diff = $first->diff($second);

        $this->assertEquals(new ArrayList(["1", "3"]), $diff);
    }
}
