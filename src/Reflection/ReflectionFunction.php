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
     * @template T
     * @param (Closure(...$n): T) $closure
     * @return T
     */
    public static function call(Closure $closure, mixed $args): mixed
    {
        $reflection = new self($closure);
        $names = $reflection->getParameterNames();
        $params = array_intersect_key($args, array_flip($names));

        return $reflection->invoke(...$params);
    }

    public function getOriginalReturnType(): string
    {
        return OriginalTypeParser::parse(parent::getReturnType());
    }

    public function typeContext(): TypeContext
    {
        return new TypeContext(
            namespace: $this->getNamespaceName(),
            filename: $this->getFileName() ?: '',
            templates: [],
            aliases: [],
        );
    }
}
