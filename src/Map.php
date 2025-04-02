<?php

declare(strict_types=1);

namespace Tcds\Io\Generic;

/**
 * @phpstan-type Entries array<GenericKey, GenericValue>
 * @template GenericKey of int|string
 * @template GenericValue
 */
class Map
{
    /**
     * @param Entries $entries
     */
    public function __construct(protected array $entries)
    {
    }

    /**
     * @return Entries
     */
    public function entries(): array
    {
        return $this->entries;
    }

    /**
     * @return list<GenericKey>
     */
    public function keys(): array
    {
        return array_keys($this->entries);
    }

    /**
     * @return list<GenericValue>
     */
    public function values(): array
    {
        return array_values($this->entries);
    }

    /**
     * @return MutableMap<GenericKey, GenericValue>
     */
    public function toMutableMap(): MutableMap
    {
        /** @var MutableMap<GenericKey, GenericValue> */
        return new MutableMap($this->entries);
    }
}
