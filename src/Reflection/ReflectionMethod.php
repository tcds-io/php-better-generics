<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Reflection;

use Override;
use ReflectionMethod as OriginalReflectionMethod;
use ReflectionNamedType as OriginalReflectionNamedType;
use ReflectionParameter as OriginalReflectionParameter;
use ReturnTypeWillChange;
use Tcds\Io\Generic\Reflection\Type\ReflectionType;

class ReflectionMethod extends OriginalReflectionMethod
{
    public function __construct(public readonly ReflectionClass $reflection, string $method)
    {
        parent::__construct($reflection->name, $method);
    }

    /**
     * @return list<ReflectionParameter>
     */
    #[Override]
    public function getParameters(): array
    {
        return array_map(
            fn(OriginalReflectionParameter $param) => new ReflectionParameter($this, $param->name),
            parent::getParameters(),
        );
    }

    #[Override]
    #[ReturnTypeWillChange]
    public function getReturnType(): ReflectionType
    {
        return ReflectionType::create($this);
    }

    public function getOriginalReturnType(): OriginalReflectionNamedType
    {
        $type = parent::getReturnType();

        return $type instanceof OriginalReflectionNamedType
            ? $type
            : new OriginalReflectionNamedType();
    }
}
