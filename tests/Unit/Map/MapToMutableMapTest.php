<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Unit\Map;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\Fixtures\Bar;

class MapToMutableMapTest extends TestCase
{
    #[Test] public function given_a_map_then_return_a_mutable_map(): void
    {
        $map = mapOf([
            "one" => new Bar("1"),
            "two" => new Bar("2"),
            "three" => new Bar("3"),
            "four" => new Bar("4"),
        ]);

        $mutable = $map->toMutableMap();

        $this->assertEquals(
            mutableMapOf([
                "one" => new Bar("1"),
                "two" => new Bar("2"),
                "three" => new Bar("3"),
                "four" => new Bar("4"),
            ]),
            $mutable,
        );
    }
}
