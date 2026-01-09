<?php

declare(strict_types=1);

namespace Tcds\Io\Generic;

use Override;

/**
 * @template GenericKey of string
 * @template GenericValue
 * @extends Map<string, GenericValue>
 */
class MutableMap extends Map
{
    /**
     * @param GenericKey $key
     * @param GenericValue $value
     * @noinspection PhpMissingParamTypeInspection
     */
    public function put($key, $value): void
    {
        $this->entries[$key] = $value;
    }

    /**
     * @param Map<GenericKey, GenericValue> $map
     */
    public function putAll(Map $map): void
    {
        $this->entries = array_merge($this->entries, $map->entries);
    }

    /**
     * @param GenericKey $key
     * @noinspection PhpMissingParamTypeInspection
     */
    public function remove($key): void
    {
        unset($this->entries[$key]);
    }

    /**
     * @param GenericKey $offset
     * @param GenericValue $value
     */
    #[Override] public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->put($offset, $value);
    }

    /**
     * @param GenericKey $offset
     */
    #[Override] public function offsetUnset(mixed $offset): void
    {
        $this->remove($offset);
    }
}
