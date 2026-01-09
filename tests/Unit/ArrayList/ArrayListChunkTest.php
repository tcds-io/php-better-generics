<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Unit\ArrayList;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\Fixtures\Bar;

class ArrayListChunkTest extends TestCase
{
    #[Test] public function given_an_array_list_then_get_in_in_chunks(): void
    {
        $list = listOf(new Bar("1"), new Bar("2"), new Bar("3"), new Bar("4"));

        $chunks = $list->chunk(2);

        $this->assertEquals(
            listOf(
                listOf(new Bar("1"), new Bar("2")),
                listOf(new Bar("3"), new Bar("4")),
            ),
            $chunks,
        );
    }
}
