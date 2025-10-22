<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Reflection\Type\Parser;

use ReflectionNamedType as OriginalReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

class OriginalTypeParser
{
    public static function parse(?ReflectionType $original): string
    {
        return match (true) {
            $original instanceof OriginalReflectionNamedType => $original->getName(),
            $original instanceof ReflectionUnionType => listOf(...$original->getTypes())
                ->map(fn (ReflectionType $type) => self::parse($type))
                ->join('|'),
            default => 'mixed',
        };
    }
}
