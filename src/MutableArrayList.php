<?php

declare(strict_types=1);

namespace Tcds\Io\Generic;

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
}
