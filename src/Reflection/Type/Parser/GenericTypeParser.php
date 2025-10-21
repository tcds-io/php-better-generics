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
}
