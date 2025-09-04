<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\MutableArrayList;

class MutableArrayListTest extends TestCase
{
    #[Test] public function push(): void
    {
        /** @var MutableArrayList<string> $mutableArrayList */
        $mutableArrayList = mutableListOf(['foo']);

        $mutableArrayList->push('bar');

        $this->assertEquals(['foo', 'bar'], $mutableArrayList->items());
    }
}
