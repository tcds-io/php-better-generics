<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Unit;

use OutOfRangeException;
use PHPUnit\Framework\Attributes\Test;
use Tcds\Io\Generic\MutableArrayList;
use Test\Tcds\Io\Generic\BetterGenericTestCase;

class MutableArrayListTest extends BetterGenericTestCase
{
    #[Test] public function push(): void
    {
        /** @var MutableArrayList<string> $mutableArrayList */
        $mutableArrayList = mutableListOf(['foo']);

        $mutableArrayList->push('bar');

        $this->assertEquals(['foo', 'bar'], $mutableArrayList->items());
    }

    #[Test] public function pop_returns_and_removes_last(): void
    {
        /** @var MutableArrayList<string> $list */
        $list = mutableListOf(['foo', 'bar']);

        $this->assertEquals('bar', $list->pop());
        $this->assertEquals(['foo'], $list->items());
    }

    #[Test] public function pop_throws_on_empty(): void
    {
        /** @var MutableArrayList<string> $list */
        $list = new MutableArrayList([]);

        $this->expectThrows(OutOfRangeException::class, fn () => $list->pop());
    }

    #[Test] public function set_replaces_item_at_index(): void
    {
        /** @var MutableArrayList<string> $list */
        $list = mutableListOf(['foo', 'bar']);

        $list->set(1, 'baz');

        $this->assertEquals(['foo', 'baz'], $list->items());
    }

    #[Test] public function set_throws_on_invalid_index(): void
    {
        /** @var MutableArrayList<string> $list */
        $list = mutableListOf(['foo']);

        $this->expectThrows(OutOfRangeException::class, fn () => $list->set(5, 'baz'));
    }

    #[Test] public function remove_at_drops_item_and_reindexes(): void
    {
        /** @var MutableArrayList<string> $list */
        $list = mutableListOf(['foo', 'bar', 'baz']);

        $list->removeAt(1);

        $this->assertEquals(['foo', 'baz'], $list->items());
    }

    #[Test] public function clear_empties_the_list(): void
    {
        /** @var MutableArrayList<string> $list */
        $list = mutableListOf(['foo', 'bar']);

        $list->clear();

        $this->assertEquals([], $list->items());
    }
}
