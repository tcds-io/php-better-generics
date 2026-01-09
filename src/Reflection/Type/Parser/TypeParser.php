<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Reflection\Type\Parser;

class TypeParser
{
    public static function getParamFromDocblock(string $docblock, string $name): ?string
    {
        return self::extractPatterFromDocblock(
            docblock: $docblock,
            pattern: sprintf('/@param\s+([^\n]+?)\s+\$%s/s', $name),
        );
    }

    public static function getReturnFromDocblock(string $docblock): ?string
    {
        return self::extractPatterFromDocblock(docblock: $docblock, pattern: '/@return\s+([\s\S]*)/');
    }

    /**
     * @return array{ 0: string, 1: list<string> }
     */
    public static function getGenericTypes(string $type): array
    {
        if (str_ends_with($type, '[]')) {
            $type = sprintf('list<%s>', str_replace('[]', '', $type));
        }

        [$type, $generics] = str_contains($type, '<')
            ? GenericTypeParser::parse($type)
            : [$type, []];

        if ($type === 'array' && count($generics) === 1) {
            $type = 'list';
        }

        return [$type, $generics];
    }

    /**
     * @return array{ 0: string, 1: array<string, string> }
     */
    public static function getParamMapFromShape(string $shape): array
    {
        return ShapeTypeParser::parse($shape);
    }

    private static function extractPatterFromDocblock(string $docblock, string $pattern, int $matchIndex = 1): ?string
    {
        $docblock = trim($docblock ?: '');
        $docblock = preg_replace('/\/\*\*|\*\/|\*/', '', $docblock) ?: '';
        $docblock = preg_replace('/\s*\n\s*/', ' ', $docblock) ?: '';
        $annotations = array_filter(explode('@', trim($docblock)));
        $docblock = join(PHP_EOL, array_map(fn(string $line) => "@$line", $annotations));
        preg_match($pattern, $docblock, $matches);

        return $matches[$matchIndex] ?? null;
    }
}
