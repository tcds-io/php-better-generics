<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Unit\ArrayList;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\ArrayList;

class ListOfInitializer extends TestCase
{
    #[Test] public function initialize_spreading(): void
    {
        $list = listOf('foo', 'bar');

        $this->assertEquals(new ArrayList(['foo', 'bar']), $list);
    }

    #[Test] public function initialize_single_array(): void
    {
        $list = listOf(['foo', 'bar']);

        $this->assertEquals(new ArrayList(['foo', 'bar']), $list);
    }

    #[Test] public function initialize_single_iterable(): void
    {
        $list = listOf(listOf(['foo', 'bar']));

        $this->assertEquals(new ArrayList(['foo', 'bar']), $list);
    }
}
