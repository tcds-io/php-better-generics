<?php

declare(strict_types=1);

namespace Tcds\Io\Generic;

/**
 * @phpstan-type Entries array<GenericKey, GenericValue>
 * @template GenericKey of int|string
 * @template GenericValue
 * @extends Map<GenericKey, GenericValue>
 */
class MutableMap extends Map
{
    /**
     * @param array<GenericKey, GenericValue> $entries
     */
    public function __construct(array $entries)
    {
        parent::__construct($entries);
    }

    /**
     * @return Entries
     */
    public function entries(): array
    {
        return $this->entries;
    }

    /**
     * @param GenericKey $key
     * @param GenericValue $value
     * @noinspection PhpMissingParamTypeInspection
     */
    public function put($key, $value): void
    {
        $this->entries[$key] = $value;
    }
}
