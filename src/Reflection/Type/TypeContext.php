<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Reflection\Type;

readonly class TypeContext
{
    /**
     * @param array<string, string> $templates
     * @param array<string, string> $aliases
     */
    public function __construct(
        public string $namespace,
        public string $filename,
        public array $templates,
        public array $aliases,
    ) {
    }

    public function fqnOf(string $type): string
    {
        if (ReflectionType::isResolvedType($type)) {
            return $type;
        }

        $source = file_get_contents($this->filename ?: '') ?: '';
        $fqn = $this->namespace . '\\' . $type;
        $pattern = sprintf("~use\s(.*?)%s;~", preg_quote($type, '~'));

        if (preg_match($pattern, $source, $matches)) {
            $fqn = $matches[1] . $type;
        }

        if (class_exists($fqn) || enum_exists($fqn)) {
            return $fqn;
        }

        return $type;
    }

    public function type(string $type): string
    {
        $type = $this->aliases[$type] ?? $type;

        return $this->templates[$type] ?? $type;
    }
}
