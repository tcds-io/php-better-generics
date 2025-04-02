<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Unit\ArrayList;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ArrayListContainsTest extends TestCase
{
    #[Test] public function should_check_if_array_list_contains_given_element(): void
    {
        $list = listOf("1", "2", "3");

        $this->assertTrue($list->contains("2"));
        $this->assertFalse($list->contains("7"));
    }
}
