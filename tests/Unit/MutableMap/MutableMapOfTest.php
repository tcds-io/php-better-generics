<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Unit\MutableMap;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\Fixtures\Bar;
use Tcds\Io\Generic\MutableMap;

class MutableMapOfTest extends TestCase
{
    #[Test] public function given_multiple_key_value_then_create_mutable_map(): void
    {
        $mutableMap = mutableMapOf([
            "one" => new Bar("1"),
            "two" => new Bar("2"),
            "three" => new Bar("3"),
            "four" => new Bar("4"),
        ]);

        $this->assertInstanceOf(MutableMap::class, $mutableMap);
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
}
