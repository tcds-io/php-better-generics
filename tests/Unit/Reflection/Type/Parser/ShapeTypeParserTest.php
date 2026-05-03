<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Unit\Reflection\Type\Parser;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\Reflection\Type\Parser\DocBlockTypeResolver;
use Test\Tcds\Io\Generic\Fixtures\Address;
use Test\Tcds\Io\Generic\Fixtures\Company;

/**
 * Regression suite that originally exercised the now-deleted
 * ShapeTypeParser. Routes through DocBlockTypeResolver to keep coverage
 * of nested shape parsing across array{...} and object{...} variants.
 */
class ShapeTypeParserTest extends TestCase
{
    private DocBlockTypeResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new DocBlockTypeResolver();
    }

    #[Test]
    public function parse_shape_array(): void
    {
        $params = [
            'company' => Company::class,
            'address' => Address::class,
            'description' => 'string',
        ];
        $type = shape('array', $params);

        $parsed = $this->resolver->shapeMemberStrings($type);

        $this->assertEquals(['array', $params], $parsed);
    }

    #[Test]
    public function parse_nested_shape_array(): void
    {
        $params = [
            'company' => Company::class,
            'address' => Address::class,
            'description' => 'string',
            'nested_object' => shape('object', [
                'company' => Company::class,
                'address' => Address::class,
                'description' => 'string',
            ]),
        ];
        $type = shape('array', $params);

        $parsed = $this->resolver->shapeMemberStrings($type);

        $this->assertEquals(['array', $params], $parsed);
    }

    #[Test]
    public function parse_nested_shape_object(): void
    {
        $params = [
            'company' => Company::class,
            'address' => Address::class,
            'description' => 'string',
            'nested_array' => shape('array', [
                'company' => Company::class,
                'address' => Address::class,
                'description' => 'string',
            ]),
        ];
        $type = shape('object', $params);

        $parsed = $this->resolver->shapeMemberStrings($type);

        $this->assertEquals(['object', $params], $parsed);
    }
}
