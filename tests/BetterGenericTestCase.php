<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\ArrayList;
use Tcds\Io\Generic\Reflection\ReflectionParameter;
use Tcds\Io\Generic\Reflection\ReflectionProperty;
use Throwable;

class BetterGenericTestCase extends TestCase
{
    /**
     * @template T of Throwable
     * @param class-string<T> $expected
     * @return T
     */
    public function expectThrows(string $expected, callable $action): Throwable
    {
        try {
            $action();
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (Throwable $exception) {
            Assert::assertInstanceOf($expected, $exception);

            return $exception;
        }

        throw new AssertionFailedError('Failed asserting that an exception was thrown');
    }

    /**
     * @param array<string, array{ 0: string, 1: string }> $expected
     * @param list<ReflectionProperty|ReflectionParameter> $paramsOrProperties
     */
    protected function assertParams(array $expected, array $paramsOrProperties): void
    {
        $this->assertEquals(
            $expected,
            new ArrayList($paramsOrProperties)
                ->indexedBy(fn (ReflectionProperty|ReflectionParameter $param) => $param->name)
                ->mapValues(fn (ReflectionProperty|ReflectionParameter $prop) => [
                    get_class($prop->getType()),
                    $prop->getType()->getName(),
                ])
                ->entries(),
        );
    }
}
