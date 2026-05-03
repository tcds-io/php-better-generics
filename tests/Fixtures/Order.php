<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Fixtures;

readonly class Order
{
    /**
     * Unnecessary annotations will be used on tests
     */
    public function __construct(public string $userId, public Address $delivery)
    {
    }
}
