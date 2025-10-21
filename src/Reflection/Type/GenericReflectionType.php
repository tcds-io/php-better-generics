<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Reflection\Type;

use Tcds\Io\Generic\Reflection\ReflectionClass;
use Tcds\Io\Generic\Reflection\Type\Parser\TypeParser;

class GenericReflectionType extends ReflectionType
{
    /**
     * @param list<string> $generics
     */
    public function __construct(ReflectionClass $reflection, string $type, public readonly array $generics)
    {
        parent::__construct($reflection, $type);
    }

    public static function from(ReflectionClass $reflection, string $type): self
    {
        [$type, $generics] = TypeParser::getGenericTypes($type);
        $type = $reflection->templates[$type] ?? $type;
        $resolved = [];

        foreach ($generics as $generic) {
            $genericType = $reflection->templates[$generic] ?? $generic;

            $resolved[] = $reflection->fqnOf($genericType);
        }

        return new self($reflection, $type, $resolved);
    }

    public function getName(): string
    {
        return generic($this->type, $this->generics);
    }
}
