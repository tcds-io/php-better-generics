<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Reflection\Type;

use Tcds\Io\Generic\Reflection\Type\Parser\UseStatementExtractor;

readonly class TypeContext
{
    /**
     * @param array<string, string> $templates
     * @param array<string, string> $aliases
     * @param class-string|null $scopeClass FQN of the enclosing class, used to resolve `self`/`static`/`parent`.
     */
    public function __construct(
        public string $namespace,
        public string $filename,
        public array $templates,
        public array $aliases,
        public ?string $scopeClass = null,
        private ?UseStatementExtractor $extractor = null,
    ) {
    }

    public function fqnOf(string $type): string
    {
        if ($type === 'self' || $type === 'static') {
            return $this->scopeClass ?? $type;
        }

        if ($type === 'parent') {
            $parent = $this->scopeClass !== null ? get_parent_class($this->scopeClass) : false;

            return $parent !== false ? $parent : $type;
        }

        $imports = ($this->extractor ?? new UseStatementExtractor())->extract($this->filename);

        // Direct hit on a use statement (handles `use A\B as C`, group uses, etc.)
        $head = self::head($type);
        if (isset($imports[$head])) {
            $tail = substr($type, strlen($head));
            $candidate = $imports[$head] . $tail;

            if (class_exists($candidate) || enum_exists($candidate) || interface_exists($candidate)) {
                return $candidate;
            }
        }

        // Same-namespace fallback.
        $candidate = $this->namespace !== '' ? $this->namespace . '\\' . $type : $type;
        if (class_exists($candidate) || enum_exists($candidate) || interface_exists($candidate)) {
            return $candidate;
        }

        // Already a resolvable global / FQN.
        if (class_exists($type) || enum_exists($type) || interface_exists($type)) {
            return $type;
        }

        return $type;
    }

    public function type(string $type): string
    {
        if (str_contains($type, '|')) {
            $types = explode('|', $type);
            $resolved = array_map($this->type(...), $types);

            return join('|', $resolved);
        }

        $type = $this->aliases[$type] ?? $type;
        $type = $this->templates[$type] ?? $type;

        return $this->fqnOf($type);
    }

    /**
     * Returns the first segment of a (possibly nested) class name. For `Foo\Bar\Baz`
     * returns `Foo`; for `Bar` returns `Bar`. Used to look up imports keyed by the
     * short name brought into scope by a `use` statement.
     */
    private static function head(string $type): string
    {
        $position = strpos($type, '\\');

        return $position === false ? $type : substr($type, 0, $position);
    }
}
