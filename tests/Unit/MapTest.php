<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Unit;

use LogicException;
use PHPUnit\Framework\Attributes\Test;
use Tcds\Io\Generic\BetterGenericTestCase;
use Tcds\Io\Generic\Fixtures\Bar;
use Tcds\Io\Generic\Fixtures\Foo;
use Tcds\Io\Generic\Map;

class MapTest extends BetterGenericTestCase
{
    #[Test] public function given_multiple_key_value_then_create_map(): void
    {
        /** @var Map<string, Bar> $map */
        $map = mapOf([
            "one" => new Bar("1"),
            "two" => new Bar("2"),
            "three" => new Bar("3"),
            "four" => new Bar("4"),
        ]);

        $this->assertEquals(
            new Map([
                "one" => new Bar("1"),
                "two" => new Bar("2"),
                "three" => new Bar("3"),
                "four" => new Bar("4"),
            ]),
            $map,
        );
    }

    #[Test] public function given_a_map_then_get_its_entries(): void
    {
        /** @var Map<string, Bar> $map */
        $map = mapOf([
            "one" => new Bar("1"),
            "two" => new Bar("2"),
            "three" => new Bar("3"),
            "four" => new Bar("4"),
        ]);

        $entries = $map->entries();

        $this->assertEquals([
            "one" => new Bar("1"),
            "two" => new Bar("2"),
            "three" => new Bar("3"),
            "four" => new Bar("4"),
        ], $entries);
    }

    #[Test] public function given_a_map_then_get_its_keys(): void
    {
        /** @var Map<string, Bar> $map */
        $map = mapOf([
            "one" => new Bar("1"),
            "two" => new Bar("2"),
            "three" => new Bar("3"),
            "four" => new Bar("4"),
        ]);

        $keys = $map->keys();

        $this->assertEquals([
            "one",
            "two",
            "three",
            "four",
        ], $keys);
    }

    #[Test] public function given_a_map_then_get_its_values(): void
    {
        /** @var Map<string, Bar> $map */
        $map = mapOf([
            "one" => new Bar("1"),
            "two" => new Bar("2"),
            "three" => new Bar("3"),
            "four" => new Bar("4"),
        ]);

        $values = $map->values();

        $this->assertEquals([
            new Bar("1"),
            new Bar("2"),
            new Bar("3"),
            new Bar("4"),
        ], $values);
    }

    #[Test] public function contains_key(): void
    {
        /** @var Map<string, Bar> $map */
        $map = mapOf([
            "one" => new Bar("1"),
            "two" => new Bar("2"),
            "three" => new Bar("3"),
            "four" => new Bar("4"),
        ]);

        $this->assertTrue($map->containsKey("one"));
        $this->assertTrue(isset($map["one"]));

        $this->assertFalse($map->containsKey("ten"));
        $this->assertFalse(isset($map["ten"]));
    }

    #[Test] public function get_item_by_key(): void
    {
        /** @var Map<string, Bar> $map */
        $map = mapOf([
            "one" => new Bar("1"),
            "two" => new Bar("2"),
            "three" => new Bar("3"),
            "four" => new Bar("4"),
        ]);

        $this->assertEquals(new Bar("1"), $map->get("one"));
        $this->assertEquals(null, $map->get("ten"));

        $this->assertEquals(new Bar("1"), $map["one"]);
        $this->assertEquals(null, $map["ten"]);
    }

    #[Test] public function given_a_map_then_transform_its_values(): void
    {
        /** @var Map<string, Foo> $map */
        $map = mapOf([
            "one" => new Foo("1", new Bar("1")),
            "two" => new Foo("2", new Bar("2")),
            "three" => new Foo("3", new Bar("3")),
            "four" => new Foo("4", new Bar("4")),
        ]);

        $entries = $map->mapValues(fn(Foo $value) => $value->bar);

        $this->assertEquals(
            mapOf([
                "one" => new Bar("1"),
                "two" => new Bar("2"),
                "three" => new Bar("3"),
                "four" => new Bar("4"),
            ]),
            $entries,
        );
    }

    #[Test] public function given_a_map_then_transform_its_keys(): void
    {
        /** @var Map<string, Bar> $map */
        $map = mapOf([
            "one" => new Bar("1"),
            "two" => new Bar("2"),
            "three" => new Bar("3"),
            "four" => new Bar("4"),
        ]);

        $entries = $map->mapKeys(fn(string $key) => "$key.$key");

        $this->assertEquals(
            mapOf([
                "one.one" => new Bar("1"),
                "two.two" => new Bar("2"),
                "three.three" => new Bar("3"),
                "four.four" => new Bar("4"),
            ]),
            $entries,
        );
    }

    #[Test] public function given_a_map_then_return_a_mutable_map(): void
    {
        /** @var Map<string, Bar> $map */
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

    #[Test] public function prevent_set_value_to_non_mutable_map(): void
    {
        /** @var Map<string, Bar> $map */
        $map = mapOf([
            "one" => new Bar("1"),
            "two" => new Bar("2"),
            "three" => new Bar("3"),
            "four" => new Bar("4"),
        ]);

        $exception = $this->expectThrows(LogicException::class, fn() => $map["ten"] = new Bar("10"));

        $this->assertEquals(new LogicException("Cannot set values on a non-mutable map"), $exception);
    }

    #[Test] public function prevent_unset_value_to_non_mutable_map(): void
    {
        /** @var Map<string, Bar> $map */
        $map = mapOf([
            "one" => new Bar("1"),
            "two" => new Bar("2"),
            "three" => new Bar("3"),
            "four" => new Bar("4"),
        ]);

        $exception = $this->expectThrows(LogicException::class, function () use ($map): void {
            unset($map["ten"]);
        });

        $this->assertEquals(new LogicException("Cannot unset values on a non-mutable map"), $exception);
    }
}
