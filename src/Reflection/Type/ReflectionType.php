<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Reflection\Type;

use BackedEnum;
use ReflectionFunctionAbstract;
use ReflectionType as OriginalReflectionType;
use Tcds\Io\Generic\BetterGenericException;
use Tcds\Io\Generic\Reflection\ReflectionClass;
use Tcds\Io\Generic\Reflection\ReflectionMethod;
use Tcds\Io\Generic\Reflection\ReflectionParameter;
use Tcds\Io\Generic\Reflection\ReflectionProperty;
use Tcds\Io\Generic\Reflection\Type\Parser\TypeParser;
use Traversable;

class ReflectionType extends OriginalReflectionType
{
    public function __construct(public ReflectionClass $reflection, public readonly string $type)
    {
    }

    public function getName(): string
    {
        return $this->type;
    }

    public static function create(ReflectionProperty|ReflectionParameter|ReflectionMethod $context): ?self
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
            $type === null => null,
            class_exists($type) => new ClassReflectionType($reflection, $type),
            enum_exists($type) => new EnumReflectionType($reflection, $type),
            interface_exists($type) => new self($reflection, $type),
            self::isPrimitive($type) => new PrimitiveReflectionType($reflection, $type),
            self::isShape($type) => ShapeReflectionType::from($reflection, $type),
            self::isGeneric($type) => GenericReflectionType::from($reflection, $type),
            default => new UnknownReflectionType($reflection, $type),
        };
    }

    private static function getTypeForParamOrProperty(
        ReflectionMethod|ReflectionFunctionAbstract $functionOrMethod,
        ReflectionProperty|ReflectionParameter $paramOrProperty,
    ): string {
        return TypeParser::getParamFromDocblock(
            docblock: $functionOrMethod->getDocComment() ?: '',
            name: $paramOrProperty->name ?: '',
        ) ?: $paramOrProperty->getOriginalType();
    }

    private static function getReturnTypeForMethod(ReflectionMethod $method): string
    {
        return TypeParser::getReturnFromDocblock(
            docblock: $method->getDocComment() ?: '',
        ) ?? $method->getOriginalReturnType();
    }

    public static function isPrimitive(string $type): bool
    {
        $simpleNodeTypes = ['int', 'integer', 'float', 'double', 'string', 'bool', 'boolean', 'mixed'];
        $types = explode('|', str_replace('&', '|', $type));

        $notScalar = array_filter($types, fn ($t) => !in_array($t, $simpleNodeTypes, true));

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
        [, $generics] = TypeParser::getGenericTypes($type);

        return !empty($generics);
    }

    public static function isArray(string $type): bool
    {
        return str_starts_with($type, 'array') || str_starts_with($type, 'map');
    }

    public static function isList(string $type): bool
    {
        [$type] = TypeParser::getGenericTypes($type);

        return ($type === 'list')
            || ($type === 'iterable')
            || ($type === Traversable::class);
    }

    /**
     * @return ($type is class-string ? true : false)
     */
    public static function isClass(string $type): bool
    {
        return class_exists($type);
    }

    /**
     * @return ($type is class-string<BackedEnum> ? true : false)
     */
    public static function isEnum(string $type): bool
    {
        return enum_exists($type);
    }

    public static function isResolvedType(string $type): bool
    {
        return self::isClass($type) ||
            self::isEnum($type) ||
            self::isPrimitive($type);
    }
}
