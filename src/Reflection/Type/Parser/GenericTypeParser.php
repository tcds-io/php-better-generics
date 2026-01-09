<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Reflection\Type\Parser;

use InvalidArgumentException;

class GenericTypeParser
{
    /**
     * @return array{ 0: string, 1: list<string> }
     */
    public static function parse(string $type): array
    {
        if (str_contains($type, '|')) {
            return self::parseUnionTypes($type);
        }

        $type = trim($type);
        $ltPos = strpos($type, '<');

        if ($ltPos === false) {
            throw new InvalidArgumentException("Not generic type: missing opening '<' in '{$type}'");
        }

        if (!str_ends_with($type, '>')) {
            throw new InvalidArgumentException("Unbalanced generic: missing closing '>' in '{$type}'");
        }

        $outer = trim(substr($type, 0, $ltPos));
        $inner = trim(substr($type, $ltPos + 1, -1));

        $args = [];
        $buf = '';
        $depth = 0;

        $len = strlen($inner);
        for ($i = 0; $i < $len; $i++) {
            $ch = $inner[$i];

            if ($ch === '<') {
                $depth++;
                $buf .= $ch;
            } elseif ($ch === '>') {
                $depth--;
                if ($depth < 0) {
                    throw new InvalidArgumentException("Unbalanced generic: extra '>' in '{$type}'");
                }
                $buf .= $ch;
            } elseif ($ch === ',' && $depth === 0) {
                $args[] = trim($buf);
                $buf = '';
            } else {
                $buf .= $ch;
            }
        }

        if ($depth !== 0) {
            throw new InvalidArgumentException("Unbalanced generic: mismatched '<' and '>' in '{$type}'");
        }

        if (strlen(trim($buf)) > 0) {
            $args[] = trim($buf);
        }

        return [$outer, $args];
    }

    /**
     * @return array{ 0: string, 1: list<string> }
     */
    private static function parseUnionTypes(string $type): array
    {
        $types = explode('|', $type);
        $unionTypes = array_map(TypeParser::getGenericTypes(...), $types);

        $mains = [];
        $allGenerics = [];

        foreach ($unionTypes as [$main, $generics]) {
            $mains[$main] = $main;

            // $allGenerics = array_merge($allGenerics, $generics);
            foreach ($generics as $index => $generic) {
                $allGenerics[$index][$generic] = $generic;
            }
        }

        return [
            join('|', $mains),
            array_map(fn($generics) => join('|', $generics), $allGenerics),
        ];
    }
}
