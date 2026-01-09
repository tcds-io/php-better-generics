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
    private array $buffered = [];

    /** @var array<Key, Value> */
    private array $loaded = [];

    public function __construct(
        /** @var class-string<Value> */
        private readonly string $class,
        /** @var Closure(list<Key> $keys): array<Key, Value> $bufferLoader */
        private readonly Closure $bufferLoader,
        private readonly int $maxBufferSize = 10,
    ) {
    }

    /**
     * @param Key $key
     * @return Value
     */
    public function lazyOf($key): object
    {
        if (!isset($this->buffered[$key]) && !isset($this->loaded[$key])) {
            $this->buffered[$key] = $key;
        }

        if (count($this->buffered) >= $this->maxBufferSize) {
            $this->loadBufferedKeys();
        }

        return lazyOf($this->class, fn() => $this->getBuffered($key));
    }

    /**
     * @param Key $key
     * @return Value
     */
    private function getBuffered($key): object
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
