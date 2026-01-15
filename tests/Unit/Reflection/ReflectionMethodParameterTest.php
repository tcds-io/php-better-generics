<?php

namespace Test\Tcds\Io\Generic\Unit\Reflection;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\Reflection\ReflectionFunction;
use Test\Tcds\Io\Generic\Fixtures\Address;

class ReflectionMethodParameterTest extends TestCase
{
    #[Test]
    public function get_fqn_annotated_type(): void
    {
        $reflection = new ReflectionFunction(Address::copy(...));
        $params = $reflection->getParameters();

        $addressParameter = $params[0];

        $this->assertEquals(Address::class, $addressParameter->getType()->type);
    }
}
