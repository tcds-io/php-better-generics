<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Reflection\Type\Parser;

/**
 * @deprecated Thin shim over {@see DocBlockTypeResolver}. Will be removed
 * once all internal call sites switch to the resolver directly.
 */
class TypeParser
{
    public static function getParamFromDocblock(string $docblock, string $name): ?string
    {
        return DocBlockTypeResolver::instance()->paramTypeStringFromDocblock($docblock, $name);
    }

    public static function getReturnFromDocblock(string $docblock): ?string
    {
        return DocBlockTypeResolver::instance()->returnTypeStringFromDocblock($docblock);
    }

    /**
     * @return array{string, list<string>}
     */
    public static function getGenericTypes(string $type): array
    {
        return DocBlockTypeResolver::instance()->genericTypeParts($type);
    }

    /**
     * @return array{string, array<string, string>}
     */
    public static function getParamMapFromShape(string $shape): array
    {
        return DocBlockTypeResolver::instance()->shapeMemberStrings($shape);
    }
}
