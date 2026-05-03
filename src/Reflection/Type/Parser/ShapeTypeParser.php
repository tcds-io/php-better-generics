<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Reflection\Type\Parser;

/**
 * @deprecated Thin shim over {@see DocBlockTypeResolver}. Will be removed
 * once all internal call sites switch to the resolver directly.
 */
class ShapeTypeParser
{
    /**
     * @return array{string, array<string, string>}
     */
    public static function parse(string $type): array
    {
        return DocBlockTypeResolver::instance()->shapeMemberStrings($type);
    }
}
