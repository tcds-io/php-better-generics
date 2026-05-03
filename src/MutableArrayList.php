<?php

declare(strict_types=1);

namespace Tcds\Io\Generic;

use OutOfRangeException;

/**
 * @template GenericItem
 * @extends ArrayList<GenericItem>
 */
class MutableArrayList extends ArrayList
{
    /**
     * @param GenericItem $item
     * @return $this
     */
    public function push($item): self
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @return GenericItem
     */
    public function pop()
    {
        if ($this->items === []) {
            throw new OutOfRangeException("Cannot pop from an empty list");
        }

        return array_pop($this->items);
    }

    /**
     * @param GenericItem $item
     * @return $this
     */
    public function set(int $index, $item): self
    {
        if (!array_key_exists($index, $this->items)) {
            throw new OutOfRangeException("Index $index does not exist");
        }

        $items = $this->items;
        $items[$index] = $item;
        $this->items = array_values($items);

        return $this;
    }

    /**
     * @return $this
     */
    public function removeAt(int $index): self
    {
        if (!array_key_exists($index, $this->items)) {
            throw new OutOfRangeException("Index $index does not exist");
        }

        array_splice($this->items, $index, 1);

        return $this;
    }

    /**
     * @return $this
     */
    public function clear(): self
    {
        $this->items = [];

        return $this;
    }
}
