<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Reflection;

use Closure;
use Override;
use ReflectionFunction as OriginalReflectionFunction;
use ReflectionParameter as OriginalReflectionParameter;
use ReturnTypeWillChange;
use Tcds\Io\Generic\Reflection\Type\Parser\OriginalTypeParser;
use Tcds\Io\Generic\Reflection\Type\ReflectionType;
use Tcds\Io\Generic\Reflection\Type\TypeContext;

class ReflectionFunction extends OriginalReflectionFunction
{
    public function __construct(Closure|string $function)
    {
        parent::__construct($function);
    }

    /**
     * @return list<ReflectionFunctionParameter>
     */
    #[Override]
    public function getParameters(): array
    {
        return array_map(
            fn (OriginalReflectionParameter $param) => new ReflectionFunctionParameter($this, $param->name),
            parent::getParameters(),
        );
    }

    /**
     * @return list<string>
     */
    public function getParameterNames(): array
    {
        return array_map(fn (OriginalReflectionParameter $param) => $param->name, parent::getParameters());
    }

    #[Override]
    #[ReturnTypeWillChange]
    public function getReturnType(): ReflectionType
    {
        return ReflectionType::createReturnTypeForMethod(
            method: $this,
            context: $this->typeContext(),
        );
    }

    /**
     * Invokes a closure passing only the named entries of $params that match
     * the closure's parameter names. The return type is not statically tied
     * to the closure (PHPStan can't bind a variadic-mixed template across
     * arbitrary signatures), so callers should `@var` the result if needed.
     *
     * @param array<string, mixed> $params
     */
    public static function call(Closure $closure, array $params): mixed
    {
        $reflection = new self($closure);
        $names = $reflection->getParameterNames();
        $needed = array_intersect_key($params, array_flip($names));

        return $reflection->invoke(...$needed);
    }

    public function getOriginalReturnType(): string
    {
        return OriginalTypeParser::parse(parent::getReturnType());
    }

    public function typeContext(): TypeContext
    {
        $scope = $this->getClosureScopeClass();

        return new TypeContext(
            namespace: $this->getNamespaceName(),
            filename: $this->getFileName() ?: '',
            templates: [],
            aliases: [],
            scopeClass: $scope?->name,
        );
    }
}
