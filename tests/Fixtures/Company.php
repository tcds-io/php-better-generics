<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Fixtures;

readonly class Company
{
    /**
     * @param list<Address> $addresses
     */
    public function __construct(
        private string $businessName,
        private string $registrationName,
        private bool $active,
        private array $addresses,
    ) {
    }

    /**
     * @param list<Address> $addresses
     */
    public static function create(string $name, bool $active, array $addresses): self
    {
        preg_match('/^(.*?)\s*\((.*?)\)$/', $name, $matches);

        return new self($matches[1] ?? '', $matches[2] ?? '', $active, $addresses);
    }

    public function name(): string
    {
        return "$this->businessName ($this->registrationName)";
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @return list<Address>
     */
    public function getAddresses(): array
    {
        return $this->addresses;
    }
}
