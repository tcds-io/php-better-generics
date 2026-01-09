<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Unit\Reflection\Type;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\Reflection\Type\Parser\GenericTypeParser;
use Test\Tcds\Io\Generic\Fixtures\Address;
use Test\Tcds\Io\Generic\Fixtures\Pair;

class GenericTypeParserTest extends TestCase
{
    #[Test]
    public function single_generics(): void
    {
        $pairType = generic('list', [Address::class]);

        [$type, $generics] = GenericTypeParser::parse($pairType);

        $this->assertEquals('list', $type);
        $this->assertEquals([Address::class], $generics);
    }

    #[Test]
    public function multiple_generics(): void
    {
        $pairType = generic(Pair::class, ['string', Address::class]);

        [$type, $generics] = GenericTypeParser::parse($pairType);

        $this->assertEquals(Pair::class, $type);
        $this->assertEquals(['string', Address::class], $generics);
    }

    #[Test]
    public function inner_generic(): void
    {
        $pairType = generic(Pair::class, ['string', Address::class]);
        $listType = generic('list', [$pairType]);

        [$type, $generics] = GenericTypeParser::parse($listType);

        $this->assertEquals('list', $type);
        $this->assertEquals([$pairType], $generics);
    }
}
