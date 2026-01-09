<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Unit\Map;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\Fixtures\Bar;
use Tcds\Io\Generic\Map;

class MapOfTest extends TestCase
{
    #[Test] public function given_multiple_key_value_then_create_map(): void
    {
        $mutableMap = mapOf([
            "one" => new Bar("1"),
            "two" => new Bar("2"),
            "three" => new Bar("3"),
            "four" => new Bar("4"),
        ]);

        $this->assertInstanceOf(Map::class, $mutableMap);
        $this->assertEquals(
            new Map([
                "one" => new Bar("1"),
                "two" => new Bar("2"),
                "three" => new Bar("3"),
                "four" => new Bar("4"),
            ]),
            $mutableMap,
        );
    }
}
