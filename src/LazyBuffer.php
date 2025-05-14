<?php

declare(strict_types=1);

namespace Tcds\Io\Generic;

use Closure;
use RuntimeException;

/**
 * @template Key of string|int
 * @template Value of object
 */
class LazyBuffer
{
    /** @var array<Key, Key> */
    public private(set) array $buffered = [];

    /** @var array<Key, Value> */
    public private(set) array $loaded = [];

    public function __construct(
        /** @var class-string<Value> */
        private readonly string $class,
        /** @var Closure(list<Key> $keys): array<Key, Value> $bufferLoader */
        private readonly Closure $bufferLoader,
    ) {
    }

    /**
     * @param Key $key
     * @return Value
     */
    public function get($key)
    {
        if (!isset($this->buffered[$key]) && !isset($this->loaded[$key])) {
            $this->buffered[$key] = $key;
        }

        return lazyOf($this->class, fn() => $this->getBuffered($key));
    }

    /**
     * @param Key $key
     * @return Value
     */
    private function getBuffered($key)
    {
        if (!isset($this->loaded[$key])) {
            $this->loadBufferedKeys();
        }

        return $this->loaded[$key] ?? throw new RuntimeException("Unable to load value from key: <$key>");
    }

    private function loadBufferedKeys(): void
    {
        $keys = array_values($this->buffered);
        $values = ($this->bufferLoader)($keys);

        foreach ($values as $key => $value) {
            $this->loaded[$key] = $value;
            unset($this->buffered[$key]);
        }
    }
}
