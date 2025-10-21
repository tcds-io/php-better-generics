<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Reflection\Type\Parser;

use InvalidArgumentException;

class ShapeTypeParser
{
    /**
     * @return array<mixed>
     */
    public static function parse(string $type): array
    {
        $mainType = str_starts_with($type, 'object') ? 'object' : 'array';

        $type = trim($type);

        if (!str_starts_with($type, "$mainType{") || !str_ends_with($type, '}')) {
            throw new InvalidArgumentException("Not a shaped $mainType type: '$type'");
        }

        $inner = substr($type, strlen("$mainType{"), -1);

        $params = [];
        $buf = '';
        $depthCurly = 0;
        $depthAngle = 0;

        $len = strlen($inner);

        for ($i = 0; $i < $len; $i++) {
            $ch = $inner[$i];

            if ($ch === '{') {
                $depthCurly++;
                $buf .= $ch;
            } elseif ($ch === '}') {
                $depthCurly--;
                $buf .= $ch;
            } elseif ($ch === '<') {
                $depthAngle++;
                $buf .= $ch;
            } elseif ($ch === '>') {
                $depthAngle--;
                $buf .= $ch;
            } elseif ($ch === ',' && $depthCurly === 0 && $depthAngle === 0) {
                [$key, $value] = self::extractPair($buf);
                $params[$key] = $value;
                $buf = '';
            } else {
                $buf .= $ch;
            }
        }

        if (strlen(trim($buf)) > 0) {
            [$key, $value] = self::extractPair($buf);
            $params[$key] = $value;
        }

        if ($depthCurly !== 0) {
            throw new InvalidArgumentException("Unbalanced curly braces in '{$type}'");
        }

        if ($depthAngle !== 0) {
            throw new InvalidArgumentException("Unbalanced angle brackets in '{$type}'");
        }

        return [$mainType, $params];
    }

    /**
     * @return array{ 0: string, 1: string}
     */
    private static function extractPair(string $buf): array
    {
        $buf = trim($buf);
        if ($buf === '') {
            throw new InvalidArgumentException("Empty shaped array entry");
        }

        $colonPos = strpos($buf, ':');

        if ($colonPos === false) {
            throw new InvalidArgumentException("Invalid shaped array entry: '{$buf}'");
        }

        $key = trim(substr($buf, 0, $colonPos));
        $value = trim(substr($buf, $colonPos + 1));

        return [$key, $value];
    }
}
