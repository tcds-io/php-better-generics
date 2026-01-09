<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\Fixtures\Bar;
use Tcds\Io\Generic\Fixtures\Foo;
use Tcds\Io\Generic\Lazy;

class LazyTest extends TestCase
{
    #[Test] public function shouldAccessLazyMethodWithoutAnnotations(): void
    {
        $bar = Lazy::of(Bar::class)->create(fn() => new Bar('bar'));

        $this->assertEquals("bar-bar", $bar->getDuplicateName());
    }

    #[Test] public function shouldCreateObjectInjectingLazyProperties(): void
    {
        $initialized = 0;
        $bar = Lazy::of(Bar::class)->create(function () use (&$initialized) {
            $initialized++;

            return new Bar('bar');
        });

        $this->assertEquals(0, $initialized);

        $foo = new Foo('foo', $bar);
        $foo->bar->name;
        $foo->bar->name;
        $foo->bar->name;

        $this->assertEquals(1, $initialized);
        $this->assertEquals(new Bar('bar'), $foo->bar);
    }
}
