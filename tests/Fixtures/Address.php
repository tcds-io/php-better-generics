<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Fixtures;

readonly class Address
{
    public function __construct(public string $street, public int $number, public bool $main)
    {
    }

    public static function copy(self $address): self
    {
        return $address;
    }
}
