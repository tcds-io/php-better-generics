<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Fixtures\Inheritance;

/**
 * @template T
 */
class Collection
{
    /** @var list<T> */
    protected array $items = [];

    /**
     * @return list<T>
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * @param T $item
     */
    public function add($item): void
    {
        $this->items[] = $item;
    }
}
