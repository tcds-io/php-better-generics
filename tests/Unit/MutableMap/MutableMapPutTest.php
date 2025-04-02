<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Unit\MutableMap;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\Fixtures\Bar;
use Tcds\Io\Generic\MutableMap;

class MutableMapPutTest extends TestCase
{
    #[Test] public function given__when__then(): void
    {
        /** @var MutableMap<string, Bar> $map */
        $map = mutableMapOf(["one" => new Bar("1")]);

        $map->put("two", new Bar("2"));

        $this->assertEquals(
            mutableMapOf([
                "one" => new Bar("1"),
                "two" => new Bar("2"),
            ]),
            $map,
        );
    }
}
