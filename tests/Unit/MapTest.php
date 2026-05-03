<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Unit;

use LogicException;
use OutOfRangeException;
use PHPUnit\Framework\Attributes\Test;
use Tcds\Io\Generic\Map;
use Test\Tcds\Io\Generic\BetterGenericTestCase;
use Test\Tcds\Io\Generic\Fixtures\Bar;
use Test\Tcds\Io\Generic\Fixtures\Foo;

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

    #[Test] public function get_or_throw_returns_value_or_raises(): void
    {
        /** @var Map<string, Bar> $map */
        $map = mapOf(["one" => new Bar("1")]);

        $this->assertEquals(new Bar("1"), $map->getOrThrow("one"));

        $this->expectThrows(OutOfRangeException::class, fn () => $map->getOrThrow("missing"));
    }

    #[Test] public function count_and_emptiness(): void
    {
        /** @var Map<string, Bar> $empty */
        $empty = mapOf([]);
        /** @var Map<string, Bar> $map */
        $map = mapOf(["one" => new Bar("1"), "two" => new Bar("2")]);

        $this->assertSame(0, $empty->count());
        $this->assertCount(0, $empty);
        $this->assertTrue($empty->isEmpty());
        $this->assertFalse($empty->isNotEmpty());

        $this->assertSame(2, $map->count());
        $this->assertCount(2, $map);
        $this->assertFalse($map->isEmpty());
        $this->assertTrue($map->isNotEmpty());
    }

    #[Test] public function filter_keeps_matching_entries(): void
    {
        /** @var Map<string, int> $map */
        $map = mapOf(["one" => 1, "two" => 2, "three" => 3]);

        $result = $map->filter(fn (int $value) => $value > 1);

        $this->assertEquals(["two" => 2, "three" => 3], $result->entries());
    }

    #[Test] public function for_each_iterates_with_value_and_key(): void
    {
        /** @var Map<string, int> $map */
        $map = mapOf(["one" => 1, "two" => 2]);
        $collected = [];

        $map->forEach(function (int $value, string $key) use (&$collected): void {
            $collected[$key] = $value;
        });

        $this->assertEquals(["one" => 1, "two" => 2], $collected);
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

        $entries = $map->mapValues(fn (Foo $value) => $value->bar);

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

        $entries = $map->mapKeys(fn (string $key) => "$key.$key");

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

        $exception = $this->expectThrows(LogicException::class, fn () => $map["ten"] = new Bar("10"));

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
