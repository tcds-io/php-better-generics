<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Unit\Reflection\Type\Parser;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\Reflection\Type\Parser\ShapeTypeParser;
use Test\Tcds\Io\Generic\Fixtures\Address;
use Test\Tcds\Io\Generic\Fixtures\Company;

class ShapeTypeParserTest extends TestCase
{
    #[Test]
    public function parse_shape_array(): void
    {
        $params = [
            'company' => Company::class,
            'address' => Address::class,
            'description' => 'string',
        ];
        $type = shape('array', $params);

        $parsed = ShapeTypeParser::parse($type);

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

        $parsed = ShapeTypeParser::parse($type);

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

        $parsed = ShapeTypeParser::parse($type);

        $this->assertEquals(['object', $params], $parsed);
    }
}
