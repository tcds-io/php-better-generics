<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Reflection;

use Override;
use ReflectionNamedType as OriginalReflectionNamedType;
use ReflectionParameter as OriginalReflectionParameter;
use ReturnTypeWillChange;
use Tcds\Io\Generic\Reflection\Type\ReflectionType;

class ReflectionParameter extends OriginalReflectionParameter
{
    public readonly ReflectionClass $reflection;

    public function __construct(private readonly ReflectionMethod $method, string $param)
    {
        $this->reflection = $this->method->reflection;

        parent::__construct(
            [$method->reflection->name, $method->name],
            $param,
        );
    }

    #[ReturnTypeWillChange]
    #[Override] public function getType(): ReflectionType
    {
        return ReflectionType::create($this);
    }

    public function getOriginalType(): OriginalReflectionNamedType
    {
        $type = parent::getType();

        return $type instanceof OriginalReflectionNamedType
            ? $type
            : new OriginalReflectionNamedType();
    }
}
