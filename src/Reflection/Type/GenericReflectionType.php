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

        return new self(
            type: $context->fqnOf($context->templates[$type] ?? $type),
            generics: array_map(
                fn(string $generic) => $context->fqnOf($context->templates[$generic] ?? $generic),
                $generics,
            ),
        );
    }

    public function getName(): string
    {
        return generic($this->type, $this->generics);
    }
}
