<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Unit\Reflection;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\Reflection\ReflectionFunction;
use Tcds\Io\Generic\Reflection\Type\ClassReflectionType;
use Tcds\Io\Generic\Reflection\Type\PrimitiveReflectionType;
use Test\Tcds\Io\Generic\Fixtures\Address;

class ReflectionFunctionTest extends TestCase
{
    #[Test] public function get_parameters_returns_wrapped_function_parameters(): void
    {
        $reflection = new ReflectionFunction(static fn (int $count, string $label): bool => $count > 0);

        $params = $reflection->getParameters();

        $this->assertCount(2, $params);
        $this->assertSame('count', $params[0]->name);
        $this->assertSame('label', $params[1]->name);
    }

    #[Test] public function get_parameter_names_returns_only_names(): void
    {
        $reflection = new ReflectionFunction(static fn (int $a, string $b): bool => true);

        $this->assertSame(['a', 'b'], $reflection->getParameterNames());
    }

    #[Test] public function get_return_type_resolves_native_return(): void
    {
        $reflection = new ReflectionFunction(static fn (int $x): string => (string) $x);

        $type = $reflection->getReturnType();

        $this->assertInstanceOf(PrimitiveReflectionType::class, $type);
        $this->assertSame('string', $type->getName());
    }

    #[Test] public function call_invokes_closure_with_named_params_filtered(): void
    {
        $closure = static fn (int $a, int $b): int => $a + $b;

        $result = ReflectionFunction::call($closure, ['a' => 2, 'b' => 3, 'extra' => 99]);

        $this->assertSame(5, $result);
    }

    #[Test] public function get_original_return_type_returns_native_string(): void
    {
        $reflection = new ReflectionFunction(static fn (): int => 1);

        $this->assertSame('int', $reflection->getOriginalReturnType());
    }

    #[Test] public function type_context_carries_scope_for_first_class_callable(): void
    {
        // First-class callable from a static method preserves the scope class,
        // so `self`/`static` resolve correctly inside the closure.
        $reflection = new ReflectionFunction(Address::copy(...));
        $params = $reflection->getParameters();

        $type = $params[0]->getType();

        $this->assertInstanceOf(ClassReflectionType::class, $type);
        $this->assertSame(Address::class, $type->getName());
    }
}
