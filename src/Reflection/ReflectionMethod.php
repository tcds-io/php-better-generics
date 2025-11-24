<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Reflection;

use Override;
use ReflectionMethod as OriginalReflectionMethod;
use ReflectionParameter as OriginalReflectionParameter;
use ReturnTypeWillChange;
use Tcds\Io\Generic\Reflection\Type\Parser\OriginalTypeParser;
use Tcds\Io\Generic\Reflection\Type\ReflectionType;
use Tcds\Io\Generic\Reflection\Type\TypeContext;

class ReflectionMethod extends OriginalReflectionMethod
{
    public function __construct(public readonly ReflectionClass $reflection, string $method)
    {
        parent::__construct($reflection->name, $method);
    }

    /**
     * @return list<ReflectionMethodParameter>
     */
    #[Override]
    public function getParameters(): array
    {
        return array_map(
            fn(OriginalReflectionParameter $param) => new ReflectionMethodParameter($this, $param),
            parent::getParameters(),
        );
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

    public function getOriginalReturnType(): string
    {
        return OriginalTypeParser::parse(parent::getReturnType());
    }

    public function typeContext(): TypeContext
    {
        return $this->reflection->typeContext();
    }
}
