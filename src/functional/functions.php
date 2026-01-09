<?php

declare(strict_types=1);

use Tcds\Io\Generic\ArrayList;
use Tcds\Io\Generic\LazyBuffer;
use Tcds\Io\Generic\Map;
use Tcds\Io\Generic\MutableMap;

/**
 * @template T of object
 * @param class-string<T> $class
 * @param callable(): T $initializer
 * @return T
 * @noinspection PhpDocSignatureInspection
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */
function lazyOf(string $class, callable $initializer): object
{
    $reflector = new ReflectionClass($class);

    /** @var T */
    return $reflector->newLazyProxy($initializer);
}

/**
 * @template Key of string|int
 * @template Value of object
 * @param class-string<Value> $class
 * @param Closure(list<Key> $keys): array<Key, Value> $bufferLoader
 * @return LazyBuffer<Key, Value>
 */
function lazyBufferOf(string $class, Closure $bufferLoader): LazyBuffer
{
    return new LazyBuffer($class, $bufferLoader);
}

function initializeLazyObject(object $lazy): void
{
    $reflector = new ReflectionClass($lazy);
    $reflector->initializeLazyObject($lazy);
}

/**
 * @template T
 * @param T ...$items
 * @return ArrayList<T>
 */
function listOf(...$items): ArrayList
{
    if (count($items) === 1 && is_iterable($items[0])) {
        $items = [...$items[0]];
    }

    /** @var list<T> $typed */
    $typed = $items;

    return new ArrayList($typed);
}

/**
 * @template Key of int|string
 * @template Value
 * @param array<Key, Value> $entries
 * @return Map<Key, Value>
 */
function mapOf(array $entries): Map
{
    /** @var Map<Key, Value> */
    return new Map($entries);
}

/**
 * @template Key of int|string
 * @template Value
 * @param array<Key, Value> $entries
 * @return MutableMap<Key, Value>
 */
function mutableMapOf(array $entries): MutableMap
{
    /** @var MutableMap<Key, Value> */
    return new MutableMap($entries);
}
