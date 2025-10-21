<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tcds\Io\Generic\LazyBuffer;
use Test\Tcds\Io\Generic\Fixtures\Bar;

class LazyBufferTest extends TestCase
{
    /** @var array<list<string>> */
    private array $loads = [];

    /** @var LazyBuffer<string, Bar> */
    private LazyBuffer $buffer;

    protected function setUp(): void
    {
        $this->buffer = lazyBufferOf(
            Bar::class,
            fn (array $values) => $this->loadEntriesByValue($values),
        );
    }

    #[Test] public function given_a_lazy_buffer_when_values_are_not_initialized_accessed_then_buffer_and_do_not_load(): void
    {
        $this->buffer->lazyOf('first');
        $this->buffer->lazyOf('second');

        $this->assertEquals(['first' => 'first', 'second' => 'second'], $this->buffered());
        $this->assertEquals([], $this->loaded());
        $this->assertEquals([], $this->loads);
    }

    #[Test] public function given_a_lazy_buffer_when_values_any_value_is_initialized_then_load_and_reset_cache(): void
    {
        $first = $this->buffer->lazyOf('first');
        $this->buffer->lazyOf('second');

        initializeLazyObject($first);

        $this->assertEquals([], $this->buffered());
        $this->assertEquals(['first' => new Bar('first'), 'second' => new Bar('second')], $this->loaded());
        $this->assertEquals([['first', 'second']], $this->loads);
    }

    #[Test] public function given_a_lazy_buffer_when_values_are_loaded_then_do_not_load_twice(): void
    {
        $first = $this->buffer->lazyOf('first');
        $this->buffer->lazyOf('second');
        initializeLazyObject($first);

        $this->buffer->lazyOf('first');
        $this->buffer->lazyOf('second');

        $this->assertEquals([], $this->buffered());
        $this->assertEquals(['first' => new Bar('first'), 'second' => new Bar('second')], $this->loaded());
        $this->assertEquals([['first', 'second']], $this->loads);
    }

    #[Test] public function given_a_lazy_buffer_max_buffer_size_is_reached_then_flush_buffer(): void
    {
        $buffer = lazyBufferOf(
            Bar::class,
            fn (array $values) => $this->loadEntriesByValue($values),
            maxBufferSize: 3,
        );

        $buffer->lazyOf('first');
        $buffer->lazyOf('second');
        $this->assertEquals([], $this->loads);

        $buffer->lazyOf('third');
        $this->assertEquals([['first', 'second', 'third']], $this->loads);
    }

    /**
     * @param list<string> $values
     * @return array<string, Bar>
     */
    private function loadEntriesByValue(array $values): array
    {
        $this->loads[] = $values;

        return listOf(new Bar('first'), new Bar('second'))
            ->filter(fn (Bar $bar) => in_array($bar->value, $values, true))
            ->indexedBy(fn (Bar $bar) => $bar->value)
            ->entries();
    }

    /**
     * @return array<string, string>
     */
    private function buffered(): array
    {
        $reflection = new ReflectionClass($this->buffer);
        $property = $reflection->getProperty('buffered');

        /** @var array<string, string> */
        return $property->getValue($this->buffer);
    }

    /**
     * @return array<string, Bar>
     */
    private function loaded(): array
    {
        $reflection = new ReflectionClass($this->buffer);
        $property = $reflection->getProperty('loaded');

        /** @var array<string, Bar> */
        return $property->getValue($this->buffer);
    }
}
