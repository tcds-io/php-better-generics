<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Unit\Reflection\Type;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\Fixtures\Address;
use Tcds\Io\Generic\Reflection\Type\Parser\TypeParser;

class TypeParserTest extends TestCase
{
    #[Test]
    public function non_generic(): void
    {
        $type = Address::class;

        [$type, $generics] = TypeParser::getGenericTypes($type);

        $this->assertEquals(Address::class, $type);
        $this->assertEquals([], $generics);
    }

    #[Test]
    public function generic(): void
    {
        $pairType = generic('list', [Address::class]);

        [$type, $generics] = TypeParser::getGenericTypes($pairType);

        $this->assertEquals('list', $type);
        $this->assertEquals([Address::class], $generics);
    }
}
