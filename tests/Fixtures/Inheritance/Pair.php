<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Fixtures\Inheritance;

/**
 * @template K
 * @template V
 */
class Pair
{
    /**
     * @param K $key
     * @param V $value
     */
    public function __construct(public mixed $key, public mixed $value)
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
    public function value(): mixed
    {
        return $this->value;
    }
}
