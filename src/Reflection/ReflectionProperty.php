<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Reflection;

use Override;
use ReflectionMethod as OriginalReflectionMethod;
use ReflectionProperty as OriginalReflectionProperty;
use ReturnTypeWillChange;
use Tcds\Io\Generic\Reflection\Type\Parser\OriginalTypeParser;
use Tcds\Io\Generic\Reflection\Type\ReflectionType;
use Tcds\Io\Generic\Reflection\Type\TypeContext;

class ReflectionProperty extends OriginalReflectionProperty
{
    public function __construct(public readonly ReflectionClass $reflection, string $property)
    {
        parent::__construct($reflection->name, $property);
    }

    #[ReturnTypeWillChange]
    #[Override] public function getType(): ReflectionType
    {
        return ReflectionType::createTypeForParamOrProperty(
            functionOrMethod: $this->getConstructor(),
            paramOrProperty: $this,
            context: $this->typeContext(),
        );
    }

    public function getOriginalType(): string
    {
        return OriginalTypeParser::parse(parent::getType());
    }

    public function getConstructor(): OriginalReflectionMethod
    {
        return $this->reflection->getConstructor();
    }

    public function typeContext(): TypeContext
    {
        return $this->reflection->typeContext();
    }
}
