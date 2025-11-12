<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Reflection\Type;

use Tcds\Io\Generic\Reflection\Type\Parser\TypeParser;

class GenericReflectionType extends ReflectionType
{
    /**
     * @param list<string> $generics
     */
    public function __construct(string $type, public readonly array $generics)
    {
        parent::__construct($type);
    }

    public static function from(TypeContext $context, string $type): self
    {
        [$type, $generics] = TypeParser::getGenericTypes($type);
        $type = $context->templates[$type] ?? $type;
        $resolved = [];

        foreach ($generics as $generic) {
            $genericType = $context->templates[$generic] ?? $generic;
            $resolved[] = $context->fqnOf($genericType);
        }

        return new self($type, $resolved);
    }

    public function getName(): string
    {
        return generic($this->type, $this->generics);
    }
}
