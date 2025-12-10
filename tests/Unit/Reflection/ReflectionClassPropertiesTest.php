<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Unit\Reflection;

use PHPUnit\Framework\Attributes\Test;
use Tcds\Io\Generic\ArrayList;
use Tcds\Io\Generic\Reflection\ReflectionClass;
use Tcds\Io\Generic\Reflection\Type\ClassReflectionType;
use Tcds\Io\Generic\Reflection\Type\GenericReflectionType;
use Tcds\Io\Generic\Reflection\Type\PrimitiveReflectionType;
use Tcds\Io\Generic\Reflection\Type\ShapeReflectionType;
use Test\Tcds\Io\Generic\BetterGenericTestCase;
use Test\Tcds\Io\Generic\Fixtures\Address;
use Test\Tcds\Io\Generic\Fixtures\Company;
use Test\Tcds\Io\Generic\Fixtures\Order;
use Test\Tcds\Io\Generic\Fixtures\Pair;
use Test\Tcds\Io\Generic\Fixtures\RequestPayload;

class ReflectionClassPropertiesTest extends BetterGenericTestCase
{
    #[Test] public function get_pair_inputs(): void
    {
        $type = generic(Pair::class, ['string', 'string']);
        $reflection = new ReflectionClass($type);

        $properties = $reflection->getProperties();

        $this->assertParams(
            [
                'key' => [PrimitiveReflectionType::class, 'string'],
                'value' => [PrimitiveReflectionType::class, 'string'],
            ],
            $properties,
        );
    }

    #[Test] public function get_address_inputs(): void
    {
        $reflection = new ReflectionClass(Address::class);

        $properties = $reflection->getProperties();

        $this->assertParams(
            [
                'street' => [PrimitiveReflectionType::class, 'string'],
                'number' => [PrimitiveReflectionType::class, 'int'],
                'main' => [PrimitiveReflectionType::class, 'bool'],
            ],
            $properties,
        );
    }

    #[Test] public function get_array_list_inputs(): void
    {
        $type = generic(ArrayList::class, [Address::class]);
        $reflection = new ReflectionClass($type);

        $properties = $reflection->getProperties();

        $this->assertParams(
            [
                'items' => [GenericReflectionType::class, generic('list', [Address::class])],
            ],
            $properties,
        );
    }

    #[Test] public function get_with_shape_inputs(): void
    {
        $reflection = new ReflectionClass(RequestPayload::class);

        $properties = $reflection->getProperties();

        $this->assertParams(
            [
                'data' => [
                    ShapeReflectionType::class,
                    shape('array', [
                        'company' => Company::class,
                        'address' => Address::class,
                        'description' => 'string',
                        'previous' => shape('array', [
                            'company' => Company::class,
                            'address' => Address::class,
                        ]),
                    ]),
                ],
                'payload' => [
                    ShapeReflectionType::class,
                    shape('object', [
                        'company' => Company::class,
                        'address' => Address::class,
                        'description' => 'string',
                        'previous' => shape('object', [
                            'company' => Company::class,
                            'address' => Address::class,
                        ]),
                    ]),
                ],
            ],
            $properties,
        );
    }

    #[Test] public function get_with_private_constructor_properties(): void
    {
        $reflection = new ReflectionClass(Company::class);

        $properties = $reflection->getProperties();

        $this->assertParams(
            [
                'businessName' => [PrimitiveReflectionType::class, 'string'],
                'registrationName' => [PrimitiveReflectionType::class, 'string'],
                'active' => [PrimitiveReflectionType::class, 'bool'],
                'addresses' => [GenericReflectionType::class, generic('list', [Address::class])],
            ],
            $properties,
        );
    }

    #[Test] public function get_types_from_class_with_unnecessary_annotations(): void
    {
        $reflection = new ReflectionClass(Order::class);

        $properties = $reflection->getProperties();

        $this->assertParams(
            [
                'userId' => [PrimitiveReflectionType::class, 'string'],
                'delivery' => [ClassReflectionType::class, Address::class],
            ],
            $properties,
        );
    }

}
