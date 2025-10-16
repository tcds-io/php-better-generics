<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Reflection\Type;

class TypeParser
{
    public static function param(string $docblock, string $name): ?string
    {
        return self::extract(
            docblock: $docblock,
            pattern: sprintf('/@param\s+([^\n]+?)\s+\$%s/s', $name),
        );
    }

    public static function return(string $docblock): ?string
    {
        return self::extract(docblock: $docblock, pattern: '/@return\s+([\s\S]*)/');
    }

    /**
     * @return array{ 0: string, 1: list<string> }
     */
    public static function typesOf(string $type): array
    {
        if (str_ends_with($type, '[]')) {
            $type = sprintf('list<%s>', str_replace('[]', '', $type));
        }

        $pattern = '~^(.*?)<(.*?)>\s*$~';

        if (!preg_match($pattern, $type, $matches)) {
            return [$type, []];
        }

        $type = trim($matches[1]);
        $generics = array_map('trim', explode(',', $matches[2]));

        if ($type === 'array' && count($generics) === 1) {
            $type = 'list';
        }

        return [$type, $generics];
    }

    /**
     * @return array{ 0: string, 1: array<string, string> }
     */
    public static function shapeParamMap(string $shape): array
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

    private static function extract(string $docblock, string $pattern, int $matchIndex = 1): ?string
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
