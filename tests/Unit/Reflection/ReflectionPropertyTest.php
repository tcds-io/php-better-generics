<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Unit\Reflection;

use PHPUnit\Framework\Attributes\Test;
use Tcds\Io\Generic\Reflection\ReflectionClass;
use Tcds\Io\Generic\Reflection\ReflectionProperty;
use Test\Tcds\Io\Generic\BetterGenericTestCase;
use Test\Tcds\Io\Generic\Fixtures\Address;
use Test\Tcds\Io\Generic\Fixtures\User;

class ReflectionPropertyTest extends BetterGenericTestCase
{
    #[Test]
    public function when_constructor_has_promoted_properties_then_get_props_from_params(): void
    {
        $reflection = new ReflectionClass(Address::class);

        $params = $reflection->getConstructor()->getParameters();

        $street = $params[0]->getProperty();
        $number = $params[1]->getProperty();
        $main = $params[2]->getProperty();

        $this->assertInstanceOf(ReflectionProperty::class, $street);
        $this->assertInstanceOf(ReflectionProperty::class, $number);
        $this->assertInstanceOf(ReflectionProperty::class, $main);
        $this->assertEquals('street', $street->name);
        $this->assertEquals('number', $number->name);
        $this->assertEquals('main', $main->name);
    }

    #[Test]
    public function when_constructor_has_non_promoted_properties_then_props_are_null(): void
    {
        $reflection = new ReflectionClass(User::class);

        $params = $reflection->getConstructor()->getParameters();

        $name = $params[0]->getProperty();
        $email = $params[1]->getProperty();

        $this->assertNull($name);
        $this->assertNull($email);
    }
}
