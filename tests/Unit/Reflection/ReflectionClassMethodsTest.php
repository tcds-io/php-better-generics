<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Unit\Reflection;

use PHPUnit\Framework\Attributes\Test;
use Tcds\Io\Generic\Reflection\ReflectionClass;
use Tcds\Io\Generic\Reflection\Type\ClassReflectionType;
use Tcds\Io\Generic\Reflection\Type\GenericReflectionType;
use Tcds\Io\Generic\Reflection\Type\PrimitiveReflectionType;
use Tcds\Io\Generic\Reflection\Type\ShapeReflectionType;
use Test\Tcds\Io\Generic\BetterGenericTestCase;
use Test\Tcds\Io\Generic\Fixtures\Address;
use Test\Tcds\Io\Generic\Fixtures\Company;
use Test\Tcds\Io\Generic\Fixtures\Pair;
use Test\Tcds\Io\Generic\Fixtures\RequestPayload;

class ReflectionClassMethodsTest extends BetterGenericTestCase
{
    #[Test] public function given_a_class_then_get_its_constructor_params(): void
    {
        $reflection = new ReflectionClass(Company::class);
        $method = $reflection->getConstructor();

        $params = $method->getParameters();

        $this->assertParams(
            [
                'businessName' => [PrimitiveReflectionType::class, 'string'],
                'registrationName' => [PrimitiveReflectionType::class, 'string'],
                'active' => [PrimitiveReflectionType::class, 'bool'],
                'addresses' => [GenericReflectionType::class, generic('list', [Address::class])],
            ],
            $params,
        );
    }

    #[Test] public function given_a_static_method_then_get_its_params(): void
    {
        $reflection = new ReflectionClass(Company::class);
        $method = $reflection->getMethod('create');

        $params = $method->getParameters();

        $this->assertParams(
            [
                'name' => [PrimitiveReflectionType::class, 'string'],
                'active' => [PrimitiveReflectionType::class, 'bool'],
                'addresses' => [GenericReflectionType::class, generic('list', [Address::class])],
            ],
            $params,
        );
    }

    #[Test] public function get_generic_return_type(): void
    {
        $reflection = new ReflectionClass(Company::class);
        $method = $reflection->getMethod('getAddresses');

        $type = $method->getReturnType();

        $this->assertEquals(new GenericReflectionType($reflection, 'list', [Address::class]), $type);
    }

    #[Test] public function get_template_return_type(): void
    {
        $reflection = new ReflectionClass(generic(Pair::class, ['string', Address::class]));

        $keyType = $reflection->getMethod('key')->getReturnType();
        $valueType = $reflection->getMethod('value')->getReturnType();

        $this->assertEquals(new PrimitiveReflectionType($reflection, 'string'), $keyType);
        $this->assertEquals(new ClassReflectionType($reflection, Address::class), $valueType);
    }

    #[Test] public function get_method_annotated_return_type(): void
    {
        $reflection = new ReflectionClass(RequestPayload::class);
        $params = [
            'company' => Company::class,
            'address' => Address::class,
            'description' => 'string',
        ];

        $getData = $reflection->getMethod('getData');
        $getPayload = $reflection->getMethod('getPayload');

        $this->assertEquals(new ShapeReflectionType($reflection, 'array', $params), $getData->getReturnType());
        $this->assertEquals(new ShapeReflectionType($reflection, 'object', $params), $getPayload->getReturnType());
    }
}
