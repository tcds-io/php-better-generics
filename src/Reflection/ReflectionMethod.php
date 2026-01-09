<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Reflection;

use Override;
use ReflectionMethod as OriginalReflectionMethod;
use ReflectionParameter as OriginalReflectionParameter;
use ReturnTypeWillChange;
use Tcds\Io\Generic\BetterGenericException;
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
            fn (OriginalReflectionParameter $param) => new ReflectionMethodParameter($this, $param),
            parent::getParameters(),
        );
    }

    public function getParameter(string $name): ReflectionMethodParameter
    {
        $params = parent::getParameters();

        foreach ($params as $param) {
            if ($param->getName() === $name) {
                return new ReflectionMethodParameter($this, $param);
            }
        }

        throw new BetterGenericException("Method $this->class::$this->name does not have a param named `$name`");
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

    #[Override]
    public static function createFromMethodName(string $method): static
    {
        [$class, $method] = explode('::', $method);
        $reflection = new ReflectionClass($class);

        return $reflection->getMethod($method);
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
