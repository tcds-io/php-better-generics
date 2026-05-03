<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Unit\Reflection\Type;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\Reflection\Type\Parser\DocBlockTypeResolver;
use Test\Tcds\Io\Generic\Fixtures\Address;

/**
 * Regression suite that originally exercised the now-deleted TypeParser.
 * Keeps the same inputs and expectations, but routes through the
 * DocBlockTypeResolver so the legacy invariants stay covered.
 */
class TypeParserTest extends TestCase
{
    private DocBlockTypeResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new DocBlockTypeResolver();
    }

    #[Test]
    public function non_generic(): void
    {
        $type = Address::class;

        [$type, $generics] = $this->resolver->genericTypeParts($type);

        $this->assertEquals(Address::class, $type);
        $this->assertEquals([], $generics);
    }

    #[Test]
    public function generic(): void
    {
        $pairType = generic('list', [Address::class]);

        [$type, $generics] = $this->resolver->genericTypeParts($pairType);

        $this->assertEquals('list', $type);
        $this->assertEquals([Address::class], $generics);
    }
}
