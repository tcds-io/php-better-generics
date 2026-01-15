<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Fixtures;

readonly class Address
{
    public function __construct(public string $street, public int $number, public bool $main)
    {
    }

    /**
     * @param \Test\Tcds\Io\Generic\Fixtures\Address $address
     */
    public static function copy(Address $address): Address
    {
        return $address;
    }
}
