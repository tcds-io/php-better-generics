<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Reflection\Type;

use Tcds\Io\Generic\Reflection\ReflectionClass;

class ShapeReflectionType extends ReflectionType
{
    /**
     * @param array<string, string> $params
     */
    public function __construct(ReflectionClass $reflection, string $type, public readonly array $params)
    {
        parent::__construct($reflection, $type);
    }

    public static function from(ReflectionClass $reflection, string $type): self
    {
        [$type, $params] = self::shapeFqn($reflection, $type);

        return new self($reflection, $type, $params);
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
    private static function shapeFqn(ReflectionClass $reflection, string $shape): array
    {
        [$type, $namedParams] = self::shapeToTypes($shape);

        $params = [];

        foreach ($namedParams as $name => $paramType) {
            $paramType = $reflection->fqnOf($paramType);

            $params[$name] = $paramType;
        }

        return [$type, $params];
    }

    /**
     * @return array{ 0: string, 1: array<string, string> }
     */
    private static function shapeToTypes(string $shape): array
    {
        preg_match_all('/(\w+)\s*:\s*([^,\s}]+)/', $shape, $pairs, PREG_SET_ORDER);

        $params = [];

        foreach ($pairs as $pair) {
            $name = $pair[1];
            $params[$name] = $pair[2];
        }

        $type = match (true) {
            str_starts_with($shape, 'object') => 'object',
            default => 'array',
        };

        return [$type, $params];
    }
}
