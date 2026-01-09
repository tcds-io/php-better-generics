<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Reflection;

use Override;
use ReflectionParameter as OriginalReflectionParameter;
use ReturnTypeWillChange;
use Tcds\Io\Generic\Reflection\Type\Parser\OriginalTypeParser;
use Tcds\Io\Generic\Reflection\Type\ReflectionType;
use Tcds\Io\Generic\Reflection\Type\TypeContext;

class ReflectionMethodParameter extends OriginalReflectionParameter
{
    public function __construct(private readonly ReflectionMethod $method, string $param)
    {
        parent::__construct([$method->reflection->name, $method->name], $param);
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
}
