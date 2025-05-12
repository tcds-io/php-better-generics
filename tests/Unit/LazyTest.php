<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\Fixtures\Bar;
use Tcds\Io\Generic\Fixtures\Foo;

class LazyTest extends TestCase
{
    #[Test] public function should_access_lazy_method_without_annotations(): void
    {
        $bar = lazyOf(Bar::class, fn() => new Bar('bar'));

        $this->assertEquals("bar-bar", $bar->getDuplicateName());
    }

    #[Test] public function should_create_object_injecting_lazy_properties(): void
    {
        $initialized = 0;
        $bar = lazyOf(Bar::class, function () use (&$initialized) {
            $initialized++;

            return new Bar('bar');
        });

        $this->assertEquals(0, $initialized);

        $foo = new Foo('foo', $bar);
        $string = "{$foo->bar->value}-{$foo->bar->value}-{$foo->bar->value}";

        $this->assertEquals(1, $initialized);
        $this->assertEquals(new Bar('bar'), $foo->bar);
        $this->assertEquals("bar-bar-bar", $string);
    }

    #[Test] public function initialize_lazy_object(): void
    {
        $bar = lazyOf(Bar::class, fn() => new Bar('bar'));
        $this->assertEmptyLazyObject($bar);

        initializeLazyObject($bar);

        $this->assertInitializedLazyObject($bar);
    }

    private function assertEmptyLazyObject(object $object): void
    {
        $this->assertEquals(
            <<<STRING
            Tcds\Io\Generic\Fixtures\Bar Object
            (
            )
            STRING,
            trim(print_r($object, true)),
        );
    }

    private function assertInitializedLazyObject(object $object): void
    {
        $this->assertEquals(
            <<<STRING
            Tcds\Io\Generic\Fixtures\Bar Object
            (
                [instance] => Tcds\Io\Generic\Fixtures\Bar Object
                    (
                        [value] => bar
                    )

            )
            STRING,
            trim(print_r($object, true)),
        );
    }
}
