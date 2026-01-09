<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Unit\Map;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\Fixtures\Bar;

class MapTest extends TestCase
{
    #[Test] public function given_a_map_then_get_its_entries(): void
    {
        $mutableMap = mapOf([
            "one" => new Bar("1"),
            "two" => new Bar("2"),
            "three" => new Bar("3"),
            "four" => new Bar("4"),
        ]);

        $entries = $mutableMap->entries();

        $this->assertEquals([
            "one" => new Bar("1"),
            "two" => new Bar("2"),
            "three" => new Bar("3"),
            "four" => new Bar("4"),
        ], $entries);
    }

    #[Test] public function given_a_map_then_get_its_keys(): void
    {
        $mutableMap = mapOf([
            "one" => new Bar("1"),
            "two" => new Bar("2"),
            "three" => new Bar("3"),
            "four" => new Bar("4"),
        ]);

        $entries = $mutableMap->keys();

        $this->assertEquals([
            "one",
            "two",
            "three",
            "four",
        ], $entries);
    }

    #[Test] public function given_a_map_then_get_its_values(): void
    {
        $mutableMap = mapOf([
            "one" => new Bar("1"),
            "two" => new Bar("2"),
            "three" => new Bar("3"),
            "four" => new Bar("4"),
        ]);

        $entries = $mutableMap->values();

        $this->assertEquals([
            new Bar("1"),
            new Bar("2"),
            new Bar("3"),
            new Bar("4"),
        ], $entries);
    }
}
