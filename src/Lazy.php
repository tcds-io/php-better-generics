<?php

declare(strict_types=1);

namespace Tcds\Io\Generic;

use ReflectionClass;

/**
 * @template GenericItem of object
 * @param class-string<GenericItem> $class
 */
final readonly class Lazy
{
    /**
     * @param class-string<GenericItem> $class
     */
    private function __construct(private string $class)
    {
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return self<T>
     */
    public static function of(string $class): self
    {
        return new self($class);
    }

    /**
     * @param callable(): GenericItem $factory
     * @return GenericItem
     */
    public function create(callable $factory)
    {
        $reflector = new ReflectionClass($this->class);

        /** @var GenericItem */
        return $reflector->newLazyProxy($factory);
    }
}
