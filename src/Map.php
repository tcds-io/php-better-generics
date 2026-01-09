<?php

declare(strict_types=1);

namespace Tcds\Io\Generic;

use ArrayAccess;
use LogicException;
use Override;

/**
 * @template GenericKey of string
 * @template GenericValue
 * @phpstan-type Entries array<GenericKey, GenericValue>
 * @implements ArrayAccess<GenericKey, GenericValue>
 */
class Map implements ArrayAccess
{
    /**
     * @param array<GenericKey, GenericValue> $entries
     */
    final public function __construct(protected array $entries)
    {
    }

    /**
     * @return array<GenericKey, GenericValue>
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
     * @param GenericKey $key
     * @noinspection PhpMissingParamTypeInspection
     */
    public function containsKey($key): bool
    {
        return array_key_exists($key, $this->entries);
    }

    /**
     * @param GenericKey $key
     * @return GenericValue|null
     * @noinspection PhpMissingParamTypeInspection
     */
    public function get($key)
    {
        return $this->entries[$key] ?? null;
    }

    /**
     * @template GenericMappedKey of string
     * @template GenericMappedValue
     * @param callable(GenericKey, GenericValue): array{GenericMappedKey, GenericMappedValue} $callback
     * @return self<GenericMappedKey, GenericMappedValue>
     */
    public function map(callable $callback): self
    {
        /** @var array<GenericMappedKey, GenericMappedValue> $mapped */
        $mapped = [];

        foreach ($this->entries as $key => $value) {
            [$k, $v] = $callback($key, $value);
            $mapped[$k] = $v;
        }

        return new static($mapped);
    }

    /**
     * Applies the callback to the values of current Map
     *
     * @template GenericMappedKey of string
     * @param callable(GenericKey): GenericMappedKey $callback <p>
     * Callback function to run for each element in each item.
     * </p>
     * @return self<GenericMappedKey, GenericValue> after applying the callback function to each one.
     */
    public function mapKeys(callable $callback): self
    {
        return $this->map(fn($key, $value) => [$callback($key), $value]);
    }

    /**
     * Applies the callback to the values of current Map
     *
     * @template GenericMappedValue
     * @param callable(GenericValue): GenericMappedValue $callback <p>
     * Callback function to run for each element.
     * </p>
     * @return self<GenericKey, GenericMappedValue> after applying the callback function to each one.
     */
    public function mapValues(callable $callback): self
    {
        return $this->map(fn($key, $value) => [$key, $callback($value)]);
    }

    /**
     * @return MutableMap<GenericKey, GenericValue>
     */
    public function toMutableMap(): MutableMap
    {
        /** @var MutableMap<GenericKey, GenericValue> */
        return new MutableMap($this->entries);
    }

    /**
     * @param GenericKey $offset
     */
    #[Override] public function offsetExists(mixed $offset): bool
    {
        return $this->containsKey($offset);
    }

    /**
     * @param GenericKey $offset
     * @return GenericValue|null
     */
    #[Override] public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    #[Override] public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new LogicException("Cannot set values on a non-mutable map");
    }

    #[Override] public function offsetUnset(mixed $offset): void
    {
        throw new LogicException("Cannot unset values on a non-mutable map");
    }
}
