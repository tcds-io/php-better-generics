<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\Fixtures\Bar;
use Tcds\Io\Generic\MutableMap;

class MutableMapTest extends TestCase
{
    #[Test] public function given_multiple_key_value_then_create_mutable_map(): void
    {
        $mutableMap = mutableMapOf([
            "one" => new Bar("1"),
            "two" => new Bar("2"),
            "three" => new Bar("3"),
            "four" => new Bar("4"),
        ]);

        $this->assertEquals(
            new MutableMap([
                "one" => new Bar("1"),
                "two" => new Bar("2"),
                "three" => new Bar("3"),
                "four" => new Bar("4"),
            ]),
            $mutableMap,
        );
    }

    #[Test] public function put_value_to_map(): void
    {
        /** @var MutableMap<string, Bar> $map */
        $map = mutableMapOf(["one" => new Bar("1")]);

        $map->put("two", new Bar("2"));
        $map["three"] = new Bar("3");

        $this->assertEquals(
            mutableMapOf([
                "one" => new Bar("1"),
                "two" => new Bar("2"),
                "three" => new Bar("3"),
            ]),
            $map,
        );
    }

    #[Test] public function put_all_values_to_map(): void
    {
        /** @var MutableMap<string, Bar> $map */
        $map = mutableMapOf(["one" => new Bar("1")]);
        /** @var MutableMap<string, Bar> $another */
        $another = mapOf(["two" => new Bar("2"), "three" => new Bar("3")]);

        $map->putAll($another);

        $this->assertEquals(
            mutableMapOf([
                "one" => new Bar("1"),
                "two" => new Bar("2"),
                "three" => new Bar("3"),
            ]),
            $map,
        );
    }

    #[Test] public function unset_value_on_map(): void
    {
        /** @var MutableMap<string, Bar> $map */
        $map = mutableMapOf([
            "one" => new Bar("1"),
            "two" => new Bar("2"),
            "three" => new Bar("3"),
        ]);

        $map->remove("two");
        unset($map["three"]);

        $this->assertEquals(
            mutableMapOf([
                "one" => new Bar("1"),
            ]),
            $map,
        );
    }
}
