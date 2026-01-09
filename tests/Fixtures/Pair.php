<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Fixtures;

/**
 * @template K
 * @template V of object
 */
readonly class Pair
{
    /**
     * @param K $key
     * @param V $value
     */
    public function __construct(public mixed $key, public object $value)
    {
    }

    /**
     * @return K
     */
    public function key(): mixed
    {
        return $this->key;
    }

    /**
     * @return V
     */
    public function value(): object
    {
        return $this->value;
    }
}
