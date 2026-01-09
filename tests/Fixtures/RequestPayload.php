<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Fixtures;

class RequestPayload
{
    /**
     * @param array{
     *     company: Company,
     *     address: Address,
     *     description: string,
     *     previous: array{ company: Company, address: Address },
     * } $data
     * @param object{
     *     company: Company,
     *     address: Address,
     *     description: string,
     *     previous: object{ company: Company, address: Address },
     * } $payload
     */
    public function __construct(public array $data, public object $payload)
    {
    }

    /**
     * @return array{
     *      company: Company,
     *      address: Address,
     *      description: string,
     *  }
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return object{
     *      company: Company,
     *      address: Address,
     *      description: string,
     *  }
     */
    public function getPayload(): object
    {
        return $this->payload;
    }
}
