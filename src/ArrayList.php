<?php

declare(strict_types=1);

namespace Tcds\Io\Generic;

use IteratorAggregate;
use OutOfRangeException;
use Traversable;

/**
 * @template GenericItem
 * @implements IteratorAggregate<int, GenericItem>
 */
readonly class ArrayList implements IteratorAggregate
{
    /**
     * @param list<GenericItem> $items
     */
    public function __construct(private array $items)
    {
    }

    /**
     * @return Traversable<GenericItem>
     */
    public function getIterator(): Traversable
    {
        foreach ($this->items as $item) {
            yield $item;
        }
    }

    /**
     * Split an array into chunks
     *
     * @param int<1, max> $length
     * @return self<self<GenericItem>>
     */
    public function chunk(int $length): self
    {
        return new self(
            array_map(
                fn(array $chunk) => new self($chunk),
                array_chunk($this->items, $length),
            ),
        );
    }

    /**
     * @param GenericItem $item
     */
    public function contains($item): bool
    {
        return in_array($item, $this->items, true);
    }

    /**
     * Count the number of items in the current ArrayList
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @param callable(GenericItem $item): void $callable
     */
    public function forEach(callable $callable): void
    {
        foreach ($this->items as $item) {
            $callable($item);
        }
    }

    /**
     * Applies the callback to the elements of current ArrayList
     *
     * @template GenericResult
     * @param callable(GenericItem): GenericResult $callback <p>
     * Callback function to run for each element in each item.
     * </p>
     * @return self<GenericResult> after applying the callback function to each one.
     */
    public function map(callable $callback): self
    {
        return new self(array_map($callback, $this->items));
    }

    /**
     * Applies the callback to the elements of each ArrayList within the ArrayList
     *
     * @template GenericInner
     * @template GenericResult
     * @param callable(GenericInner): GenericResult $callback <p>
     * Callback function to run for each element in each ArrayList.
     * </p>
     * @return self<GenericResult> after applying the callback function to each one.
     */
    public function flatMap(callable $callback): self
    {
        $mapped = [];

        /** @var list<GenericInner> $item */
        foreach ($this->items as $item) {
            foreach ($item as $inner) {
                $mapped[] = $callback($inner);
            }
        }

        return new self($mapped);
    }

    /**
     * @return self<GenericItem>
     */

    /**
     * Return an ArrayList with elements in reverse order
     *
     * @return self<GenericItem>
     */
    public function reverse(): self
    {
        return new self(array_reverse($this->items));
    }

    /**
     * @template GenericResult
     * @param callable(GenericResult $carry, GenericItem $item): GenericResult $callable
     * @param GenericResult $initial
     * @return GenericResult
     */

    /**
     * Iteratively reduce the array to a single value using a callback function
     *
     * @template GenericResult
     * @param callable(GenericResult $carry, GenericItem $item): GenericResult $callable
     * The callback function. Signature is <pre>callback ( GenericResult $carry , GenericItem $item ) : GenericResult</pre>
     * <blockquote>GenericResult <var>$carry</var> <p>The return value of the previous iteration; on the first iteration it holds the value of <var>$initial</var>.</p></blockquote>
     * <blockquote>GenericItem <var>$item</var> <p>Holds the current iteration value of the <var>$input</var></p></blockquote>
     * </p>
     * @param GenericResult $initial <p>
     * The initial value is used at the beginning of the process, or as a final result in case
     * the array is empty.
     * </p>
     * @return GenericResult the resulting value
     */
    public function reduce(callable $callable, $initial)
    {
        return array_reduce($this->items, $callable, $initial);
    }

    /**
     * Returns the first element of the array matching the specified condition
     *
     * @param callable(GenericItem $item): bool|null $condition <p>
     *  When condition is empty, the first element in the array is returned
     *  </p>
     * @return GenericItem|null
     */
    public function first(?callable $condition = null)
    {
        $condition ??= fn($item) => true;

        foreach ($this->items as $item) {
            $passed = $condition($item);

            if ($passed) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Returns the last element of the array matching the specified condition
     *
     * @param callable(GenericItem $item): bool|null $condition <p>
     *  When condition is empty, the last element in the array is returned
     *  </p>
     * @return GenericItem|null
     */
    public function last(?callable $condition = null)
    {
        return $this->reverse()->first($condition);
    }

    /**
     * Verifies if the current array list is empty
     */
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    /**
     * Verifies if the current array list is not empty
     */
    public function isNotEmpty(): bool
    {
        return $this->count() > 0;
    }

    /**
     * @param callable(GenericItem $item): bool|null $callable [optional] <p>
     *  The callback function to use
     *  </p>
     *  <p>
     *  If no callback is supplied, all entries of
     *  input equal to false (see
     *  converting to
     *  boolean) will be removed.
     *  </p>
     * @param int $mode [optional] <p>
     *  Flag determining what arguments are sent to <i>callback</i>:
     *  </p><ul>
     *  <li>
     *  <b>ARRAY_FILTER_USE_KEY</b> - pass key as the only argument
     *  to <i>callback</i> instead of the value</span>
     *  </li>
     *  <li>
     *  <b>ARRAY_FILTER_USE_BOTH</b> - pass both value and key as
     *  arguments to <i>callback</i> instead of the value</span>
     *  </li>
     *  </ul>
     * @return self<GenericItem>
     */
    public function filter(?callable $callable = null, int $mode = 0): self
    {
        $filtered = array_filter($this->items, $callable, $mode);

        return new self(array_values($filtered));
    }

    /**
     * @param self<GenericItem> $other
     * @return self<GenericItem>
     */

    /**
     * Merges the elements of one or more ArrayList together (if the input arrays have the same string keys,
     * then the later value for that key will overwrite the previous one;
     * if the arrays contain numeric keys, the later value will be appended)
     *
     * @param ArrayList<GenericItem> ...$others <p>Variable list of arrays to merge.</p>
     * @return self<GenericItem>
     */
    public function merge(self ...$others): self
    {
        $merged = $this->items;

        foreach ($others as $other) {
            array_push($merged, ...$other->items);
        }

        return new self($merged);
    }

    /**
     * @return GenericItem
     */
    public function get(int $index)
    {
        return $this->items[$index] ?? throw new OutOfRangeException("Index $index does not exist");
    }

    /**
     * @param GenericItem $item
     */
    public function indexOf($item): int
    {
        $index = array_search($item, $this->items, true);

        return $index ?: throw new OutOfRangeException("No matching item found in the list");
    }
}
