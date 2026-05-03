<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Reflection\Type\Parser;

/**
 * Extracts `use` import statements from a PHP source file using PHP's native
 * tokenizer. Returns a map of short name => fully-qualified name for class/
 * interface/trait/enum imports. Function and const imports are intentionally
 * skipped because the reflection layer only resolves type names.
 *
 * Closure `use` clauses (`function () use ($x)`) are skipped — they are
 * variable captures, not imports.
 *
 * Cached per realpath: the source of a file does not change while the
 * library is loaded; re-reading would only burn CPU.
 */
final class UseStatementExtractor
{
    private const array NAME_TOKENS = [T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED, T_STRING, T_NS_SEPARATOR];
    private const array SKIP_TOKENS = [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT];

    /** @var array<string, array<string, string>> realpath => (shortName => fqn) */
    private static array $cache = [];

    /**
     * @return array<string, string> shortName => fqn
     */
    public function extract(string $filename): array
    {
        if ($filename === '') {
            return [];
        }

        $realpath = realpath($filename);

        if ($realpath === false) {
            return [];
        }

        if (isset(self::$cache[$realpath])) {
            return self::$cache[$realpath];
        }

        $source = file_get_contents($realpath);

        if ($source === false) {
            return self::$cache[$realpath] = [];
        }

        return self::$cache[$realpath] = self::parseTokens(token_get_all($source));
    }

    /**
     * @param list<array{0: int, 1: string, 2: int}|string> $tokens
     * @return array<string, string>
     */
    private static function parseTokens(array $tokens): array
    {
        $imports = [];
        $count = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];

            if (!is_array($token)) {
                continue;
            }

            if ($token[0] === T_NAMESPACE) {
                // Namespace tracking isn't needed for short-name resolution
                // since `use` already produces FQNs. Still consume so we don't
                // misread `T_USE` inside namespaced bodies differently.
                $i = self::skipUntilTerminator($tokens, $i + 1);

                continue;
            }

            if ($token[0] !== T_USE) {
                continue;
            }

            // Closure capture: `function () use ($x)` — next non-whitespace token is `(`.
            $next = self::peekNext($tokens, $i + 1);
            if ($next !== null && $tokens[$next] === '(') {
                continue;
            }

            [$i, $imports] = self::readUse($tokens, $i + 1, $imports);
        }

        return $imports;
    }

    /**
     * @param list<array{0: int, 1: string, 2: int}|string> $tokens
     */
    private static function skipUntilTerminator(array $tokens, int $start): int
    {
        $count = count($tokens);

        for ($i = $start; $i < $count; $i++) {
            $token = $tokens[$i];

            if ($token === ';' || $token === '{') {
                return $i;
            }
        }

        return $count - 1;
    }

    /**
     * @param list<array{0: int, 1: string, 2: int}|string> $tokens
     * @param array<string, string> $imports
     * @return array{int, array<string, string>}
     */
    private static function readUse(array $tokens, int $start, array $imports): array
    {
        $count = count($tokens);
        // true for `use function` / `use const`
        $skip = false;
        $isGroup = false;
        $prefix = '';
        $alias = null;
        $i = $start;

        for (; $i < $count; $i++) {
            $token = $tokens[$i];

            if ($token === ';') {
                if (!$skip && !$isGroup && $prefix !== '') {
                    $imports = self::addImport($imports, $prefix, $alias);
                }

                return [$i, $imports];
            }

            if ($token === '{') {
                if (!$skip) {
                    [$i, $imports] = self::readGroupUse($tokens, $i + 1, $prefix, $imports);
                }

                $isGroup = true;

                continue;
            }

            if (!is_array($token)) {
                continue;
            }

            $type = $token[0];
            $value = $token[1];

            if ($type === T_FUNCTION || $type === T_CONST) {
                $skip = true;

                continue;
            }

            if ($type === T_AS) {
                [$alias, $i] = self::readIdentifier($tokens, $i + 1);

                continue;
            }

            if (!in_array($type, self::NAME_TOKENS, true)) {
                continue;
            }

            $prefix .= $value;
        }

        return [$i, $imports];
    }

    /**
     * @param list<array{0: int, 1: string, 2: int}|string> $tokens
     * @param array<string, string> $imports
     * @return array{int, array<string, string>}
     */
    private static function readGroupUse(array $tokens, int $start, string $prefix, array $imports): array
    {
        $count = count($tokens);
        $name = '';
        $alias = null;
        // mixed group `use Foo\{function bar, const BAZ, Quux}`
        $skip = false;

        for ($i = $start; $i < $count; $i++) {
            $token = $tokens[$i];

            if ($token === '}') {
                if (!$skip && $name !== '') {
                    $imports = self::addImport($imports, rtrim($prefix, '\\') . '\\' . $name, $alias);
                }

                return [$i, $imports];
            }

            if ($token === ',') {
                if (!$skip && $name !== '') {
                    $imports = self::addImport($imports, rtrim($prefix, '\\') . '\\' . $name, $alias);
                }

                $name = '';
                $alias = null;
                $skip = false;

                continue;
            }

            if (!is_array($token)) {
                continue;
            }

            $type = $token[0];
            $value = $token[1];

            if ($type === T_FUNCTION || $type === T_CONST) {
                $skip = true;

                continue;
            }

            if ($type === T_AS) {
                [$alias, $i] = self::readIdentifier($tokens, $i + 1);

                continue;
            }

            if (!in_array($type, self::NAME_TOKENS, true)) {
                continue;
            }

            $name .= $value;
        }

        return [$i, $imports];
    }

    /**
     * @param list<array{0: int, 1: string, 2: int}|string> $tokens
     * @return array{?string, int}
     */
    private static function readIdentifier(array $tokens, int $start): array
    {
        $count = count($tokens);

        for ($i = $start; $i < $count; $i++) {
            $token = $tokens[$i];

            if (is_array($token) && $token[0] === T_STRING) {
                return [$token[1], $i];
            }

            if (is_array($token) && in_array($token[0], self::SKIP_TOKENS, true)) {
                continue;
            }

            return [null, $i - 1];
        }

        return [null, $count - 1];
    }

    /**
     * @param list<array{0: int, 1: string, 2: int}|string> $tokens
     */
    private static function peekNext(array $tokens, int $start): ?int
    {
        $count = count($tokens);

        for ($i = $start; $i < $count; $i++) {
            $token = $tokens[$i];

            if (is_array($token) && in_array($token[0], self::SKIP_TOKENS, true)) {
                continue;
            }

            return $i;
        }

        return null;
    }

    /**
     * @param array<string, string> $imports
     * @return array<string, string>
     */
    private static function addImport(array $imports, string $fqn, ?string $alias): array
    {
        $fqn = ltrim($fqn, '\\');
        $shortName = $alias ?? self::lastSegment($fqn);
        $imports[$shortName] = $fqn;

        return $imports;
    }

    private static function lastSegment(string $fqn): string
    {
        $position = strrpos($fqn, '\\');

        return $position === false ? $fqn : substr($fqn, $position + 1);
    }

    public static function clearCache(): void
    {
        self::$cache = [];
    }
}
