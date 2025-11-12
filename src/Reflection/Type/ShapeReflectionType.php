<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Reflection\Type;

use Tcds\Io\Generic\Reflection\Type\Parser\TypeParser;

class ShapeReflectionType extends ReflectionType
{
    /**
     * @param array<string, string> $params
     */
    public function __construct(string $type, public readonly array $params)
    {
        parent::__construct($type);
    }

    public static function from(TypeContext $context, string $type): self
    {
        [$type, $params] = self::shapeFqn($context, $type);

        return new self($type, $params);
    }

    public function getName(): string
    {
        $params = mapOf($this->params)
            ->map(function ($name, $type) {
                return [$name, "$name: $type"];
            })
            ->entries();

        return sprintf('%s{ %s }', $this->type, join(', ', $params));
    }

    /**
     * @return array{ 0: string, 1: array<string, string> }
     */
    private static function shapeFqn(TypeContext $context, string $shape): array
    {
        [$type, $namedParams] = TypeParser::getParamMapFromShape($shape);

        $params = [];

        foreach ($namedParams as $name => $paramType) {
            $paramType = ReflectionType::isShape($paramType)
                ? shape(...self::shapeFqn($context, $paramType))
                : $context->fqnOf($paramType);

            $params[$name] = $paramType;
        }

        return [$type, $params];
    }
}
