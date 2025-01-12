<?php

declare(strict_types=1);

namespace Tcds\Io\Generic;

use ReflectionClass;

/**
 * @template T of object
 * @param class-string<T> $class
 */
final readonly class Lazy
{
    /**
     * @param class-string<T> $class
     */
    private function __construct(private string $class)
    {
    }

    /**
     * @param class-string<T> $class
     * @return self<T>
     */
    public static function of(string $class): self
    {
        return new self($class);
    }

    /**
     * @param callable(): T $factory
     * @return T
     */
    public function create(callable $factory)
    {
        $reflector = new ReflectionClass($this->class);

        /** @var T $lazy */
        $lazy = $reflector->newLazyProxy($factory);

        return $lazy;
    }
}
