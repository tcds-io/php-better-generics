<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Reflection\Type;

use ReflectionFunctionAbstract;
use ReflectionType as OriginalReflectionType;
use Tcds\Io\Generic\BetterGenericException;
use Tcds\Io\Generic\Reflection\Annotation;
use Tcds\Io\Generic\Reflection\ReflectionClass;
use Tcds\Io\Generic\Reflection\ReflectionMethod;
use Tcds\Io\Generic\Reflection\ReflectionParameter;
use Tcds\Io\Generic\Reflection\ReflectionProperty;

class ReflectionType extends OriginalReflectionType
{
    public function __construct(public ReflectionClass $reflection, public readonly string $type)
    {
    }

    public function getName(): string
    {
        return $this->type;
    }

    public static function create(ReflectionProperty|ReflectionParameter|ReflectionMethod $context): self
    {
        $type = match ($context::class) {
            ReflectionProperty::class => self::getTypeForParamOrProperty(
                functionOrMethod: $context->getConstructor(),
                paramOrProperty: $context,
            ),
            ReflectionParameter::class => self::getTypeForParamOrProperty(
                functionOrMethod: $context->getDeclaringFunction(),
                paramOrProperty: $context,
            ),
            ReflectionMethod::class => self::getReturnTypeForMethod(
                method: $context,
            ),
            default => throw new BetterGenericException(sprintf('Unknown context `%s`', $context::class)),
        };

        $reflection = $context->reflection;
        $type = $reflection->aliases[$type] ?? $type;
        $type = $reflection->templates[$type] ?? $type;

        return match (true) {
            class_exists($type) => new ClassReflectionType($reflection, $type),
            enum_exists($type) => new EnumReflectionType($reflection, $type),
            self::isPrimitive($type) => new PrimitiveReflectionType($reflection, $type),
            self::isShape($type) => ShapeReflectionType::from($reflection, $type),
            self::isGeneric($type) => GenericReflectionType::from($reflection, $type),
            default => throw new BetterGenericException("Unknown type `$type`"),
        };
    }

    private static function getTypeForParamOrProperty(
        ReflectionMethod|ReflectionFunctionAbstract $functionOrMethod,
        ReflectionProperty|ReflectionParameter $paramOrProperty,
    ): string {
        return Annotation::param(
            docblock: $functionOrMethod->getDocComment() ?: '',
            name: $paramOrProperty->name,
        ) ?: $paramOrProperty->getOriginalType()->getName();
    }

    private static function getReturnTypeForMethod(ReflectionMethod $method): string
    {
        return Annotation::return(
            docblock: $method->getDocComment() ?: '',
        ) ?: $method->getOriginalReturnType()->getName();
    }

    private static function isPrimitive(string $type): bool
    {
        $simpleNodeTypes = ['int', 'float', 'string', 'bool', 'boolean', 'mixed'];
        $types = explode('|', str_replace('&', '|', $type));

        $notScalar = array_filter($types, fn($t) => !in_array($t, $simpleNodeTypes, true));

        if (count($types) > 1 && !empty($notScalar)) {
            return false;
        }

        return empty($notScalar);
    }

    public static function isShape(?string $type): bool
    {
        return str_starts_with($type ?? '', 'array{') || str_starts_with($type ?? '', 'object{');
    }

    public static function isGeneric(string $type): bool
    {
        [, $generics] = Annotation::typesOf($type);

        return !empty($generics);
    }

    public static function isResolvedType(string $type): bool
    {
        return class_exists($type) ||
            enum_exists($type) ||
            self::isPrimitive($type);
    }
}
