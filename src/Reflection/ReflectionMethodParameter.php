<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Reflection;

use Override;
use ReflectionParameter as OriginalReflectionParameter;
use ReflectionProperty as OriginalReflectionProperty;
use ReturnTypeWillChange;
use Tcds\Io\Generic\Reflection\Type\Parser\OriginalTypeParser;
use Tcds\Io\Generic\Reflection\Type\ReflectionType;
use Tcds\Io\Generic\Reflection\Type\TypeContext;

class ReflectionMethodParameter extends OriginalReflectionParameter
{
    public function __construct(
        private readonly ReflectionMethod $method,
        private readonly OriginalReflectionParameter $original,
    ) {
        parent::__construct([$method->reflection->name, $method->name], $original->name);
    }

    #[ReturnTypeWillChange]
    #[Override] public function getType(): ReflectionType
    {
        return ReflectionType::createTypeForParamOrProperty(
            functionOrMethod: $this->getDeclaringFunction(),
            paramOrProperty: $this,
            context: $this->typeContext(),
        );
    }

    public function getOriginalType(): string
    {
        return OriginalTypeParser::parse(parent::getType());
    }

    public function typeContext(): TypeContext
    {
        return $this->method->typeContext();
    }

    public function getProperty(): ?ReflectionProperty
    {
        $prop = $this->getRelatedProperty();

        return $prop !== null
            ? ReflectionProperty::fromOriginal($prop)
            : null;
    }

    private function getRelatedProperty(): ?OriginalReflectionProperty
    {
        return $this->original->isPromoted()
            ? $this->original->getDeclaringClass()?->getProperty($this->name)
            : null;
    }
}
