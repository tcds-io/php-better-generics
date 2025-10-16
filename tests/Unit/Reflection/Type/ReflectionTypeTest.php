<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Unit\Reflection\Type;

use BackedEnum;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\Fixtures\Address;
use Tcds\Io\Generic\Fixtures\AddressType;
use Tcds\Io\Generic\Fixtures\Company;
use Tcds\Io\Generic\Fixtures\Level;
use Tcds\Io\Generic\Fixtures\Pair;
use Tcds\Io\Generic\Fixtures\Status;
use Tcds\Io\Generic\Reflection\Type\ReflectionType;

class ReflectionTypeTest extends TestCase
{
    #[Test]
    public function is_primitive(): void
    {
        $this->assertTrue(ReflectionType::isPrimitive('int'));
        $this->assertTrue(ReflectionType::isPrimitive('string'));
        $this->assertTrue(ReflectionType::isPrimitive('float'));
        $this->assertTrue(ReflectionType::isPrimitive('bool'));
        $this->assertTrue(ReflectionType::isPrimitive('boolean'));
        $this->assertTrue(ReflectionType::isPrimitive('mixed'));
        $this->assertTrue(ReflectionType::isPrimitive('int|string'));
        $this->assertTrue(ReflectionType::isPrimitive('string|float'));
        $this->assertTrue(ReflectionType::isPrimitive('float|bool'));
        $this->assertTrue(ReflectionType::isPrimitive('bool|mixed'));
        $this->assertTrue(ReflectionType::isPrimitive('int|string|float|bool|mixed'));

        $this->assertFalse(ReflectionType::isPrimitive(Address::class));
        $this->assertFalse(ReflectionType::isPrimitive(Company::class));
        $this->assertFalse(ReflectionType::isPrimitive(BackedEnum::class));
        $this->assertFalse(ReflectionType::isPrimitive('array{ foo: string }'));
        $this->assertFalse(ReflectionType::isPrimitive('array<string>'));
        $this->assertFalse(ReflectionType::isPrimitive('list<float>'));
    }

    #[Test]
    public function is_shape(): void
    {
        $this->assertTrue(ReflectionType::isShape('array{ foo: string }'));
        $this->assertTrue(ReflectionType::isShape('object{ foo: string }'));

        $this->assertFalse(ReflectionType::isShape('array<string>'));
        $this->assertFalse(ReflectionType::isShape('list<float>'));
        $this->assertFalse(ReflectionType::isShape(Address::class));
    }

    #[Test]
    public function is_generic(): void
    {
        $this->assertTrue(ReflectionType::isGeneric('array<string>'));
        $this->assertTrue(ReflectionType::isGeneric('list<float>'));
        $this->assertTrue(ReflectionType::isGeneric('map<string, float>'));
        $this->assertTrue(ReflectionType::isGeneric(generic(Pair::class, ['string', Address::class])));

        $this->assertFalse(ReflectionType::isGeneric(Address::class));
        $this->assertFalse(ReflectionType::isGeneric(BackedEnum::class));
    }

    #[Test]
    public function is_array_map(): void
    {
        $this->assertTrue(ReflectionType::isArray('array<string, string>'));
        $this->assertTrue(ReflectionType::isArray('map<string, string>'));

        $this->assertFalse(ReflectionType::isArray('string'));
        $this->assertFalse(ReflectionType::isArray(Address::class));
    }

    #[Test]
    public function is_list(): void
    {
        $this->assertTrue(ReflectionType::isList('array<string>'));
        $this->assertTrue(ReflectionType::isList('iterable<string>'));
        $this->assertTrue(ReflectionType::isList('Traversable<string>'));

        $this->assertFalse(ReflectionType::isList('array<string, string>'));
        $this->assertFalse(ReflectionType::isList('map<string, string>'));
        $this->assertFalse(ReflectionType::isList(Address::class));
    }

    #[Test]
    public function is_class(): void
    {
        $this->assertTrue(ReflectionType::isClass(Address::class));
        $this->assertTrue(ReflectionType::isClass(Pair::class));

        $this->assertFalse(ReflectionType::isClass(generic(Pair::class, ['string', Address::class])));
        $this->assertFalse(ReflectionType::isClass('string'));
    }

    #[Test]
    public function is_enum(): void
    {
        $this->assertTrue(ReflectionType::isEnum(Status::class));
        $this->assertTrue(ReflectionType::isEnum(Level::class));
        $this->assertTrue(ReflectionType::isEnum(AddressType::class));

        $this->assertFalse(ReflectionType::isEnum(Pair::class));
        $this->assertFalse(ReflectionType::isEnum(Address::class));
        $this->assertFalse(ReflectionType::isEnum('string'));
    }

    #[Test]
    public function is_resolved_type(): void
    {
        $this->assertTrue(ReflectionType::isResolvedType('int'));
        $this->assertTrue(ReflectionType::isResolvedType('string'));
        $this->assertTrue(ReflectionType::isResolvedType(Address::class));
        $this->assertTrue(ReflectionType::isResolvedType(Status::class));
        $this->assertTrue(ReflectionType::isResolvedType(Level::class));
        $this->assertTrue(ReflectionType::isResolvedType(AddressType::class));

        $this->assertFalse(ReflectionType::isResolvedType(generic(Pair::class, ['string', Address::class])));
        $this->assertFalse(ReflectionType::isResolvedType(generic('array', ['string', Address::class])));
        $this->assertFalse(ReflectionType::isResolvedType('array{ foo: string }'));
    }
}
