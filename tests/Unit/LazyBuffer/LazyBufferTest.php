<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Unit\LazyBuffer;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\Fixtures\Bar;
use Tcds\Io\Generic\LazyBuffer;

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
            fn(array $values) => $this->loadEntriesByValue($values),
        );
    }

    #[Test] public function given_a_lazy_buffer_when_values_are_not_initialized_accessed_then_buffer_and_do_not_load(): void
    {
        $this->buffer->get('first');
        $this->buffer->get('second');

        $this->assertEquals(['first' => 'first', 'second' => 'second'], $this->buffer->buffered);
        $this->assertEquals([], $this->buffer->loaded);
        $this->assertEquals([], $this->loads);
    }

    #[Test] public function given_a_lazy_buffer_when_values_any_value_is_initialized_then_load_and_reset_cache(): void
    {
        $first = $this->buffer->get('first');
        $this->buffer->get('second');

        initializeLazyObject($first);

        $this->assertEquals([], $this->buffer->buffered);
        $this->assertEquals(['first' => new Bar('first'), 'second' => new Bar('second')], $this->buffer->loaded);
        $this->assertEquals([['first', 'second']], $this->loads);
    }

    #[Test] public function given_a_lazy_buffer_when_values_are_loaded_then_do_not_load_twice(): void
    {
        $first = $this->buffer->get('first');
        $this->buffer->get('second');
        initializeLazyObject($first);

        $this->buffer->get('first');
        $this->buffer->get('second');

        $this->assertEquals([], $this->buffer->buffered);
        $this->assertEquals(['first' => new Bar('first'), 'second' => new Bar('second')], $this->buffer->loaded);
        $this->assertEquals([['first', 'second']], $this->loads);
    }

    /**
     * @param list<string> $values
     * @return array<string, Bar>
     */
    private function loadEntriesByValue(array $values): array
    {
        $this->loads[] = $values;

        return listOf(new Bar('first'), new Bar('second'))
            ->filter(fn(Bar $bar) => in_array($bar->value, $values, true))
            ->indexedBy(fn(Bar $bar) => $bar->value)
            ->entries();
    }
}
