<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Reflection;

use Override;
use ReflectionClass as OriginalReflectionClass;
use ReflectionProperty as OriginalReflectionProperty;
use ReturnTypeWillChange;
use Tcds\Io\Generic\BetterGenericException;
use Tcds\Io\Generic\Reflection\Type\Parser\DocBlockTypeResolver;
use Tcds\Io\Generic\Reflection\Type\TypeContext;

/**
 * @extends OriginalReflectionClass<object>
 */
class ReflectionClass extends OriginalReflectionClass
{
    /** @var list<string> */
    public array $generics;

    /** @var array<string, string> */
    public array $templates;

    /** @var array<string, string> */
    public array $aliases;

    public function __construct(string $type)
    {
        /**
         * @var class-string $class
         */
        [$class, $generics] = DocBlockTypeResolver::instance()->genericTypeParts($type);

        parent::__construct($class);

        $this->generics = $generics;
        $this->templates = $this->templates(generics: $generics);
        $this->aliases = $this->aliases();
    }

    /**
     * @template T of object
     * @param OriginalReflectionClass<T> $original
     */
    public static function fromOriginal(OriginalReflectionClass $original): self
    {
        return new self($original->name);
    }

    #[Override]
    public function getMethod(string $name): ReflectionMethod
    {
        return new ReflectionMethod($this, $name);
    }

    #[Override]
    public function getConstructor(): ReflectionMethod
    {
        return $this->getMethod('__construct');
    }

    #[Override]
    #[ReturnTypeWillChange]
    public function getParentClass(): ?self
    {
        $parent = parent::getParentClass();

        if (!$parent) {
            return null;
        }

        return new self($parent->name);
    }

    #[Override]
    public function getProperty(string $name): ReflectionProperty
    {
        return new ReflectionProperty($this, $name);
    }

    /**
     * @return list<ReflectionProperty>
     */
    #[Override]
    public function getProperties(?int $filter = null): array
    {
        return array_map(
            fn(OriginalReflectionProperty $prop) => $this->getProperty($prop->name),
            parent::getProperties(),
        );
    }

    public function typeContext(): TypeContext
    {
        return new TypeContext(
            namespace: $this->getNamespaceName(),
            filename: $this->getFileName() ?: '',
            templates: $this->templates,
            aliases: $this->aliases,
            scopeClass: $this->name,
        );
    }

    /**
     * Maps each declared @template name to the concrete type bound at
     * construction time via the positional $generics argument. Bounds
     * (`@template T of Foo`) are dropped here — preserved by the resolver
     * but not yet consumed by the rest of the layer; that's a follow-up.
     *
     * @param list<string> $generics
     * @return array<string, string>
     */
    private function templates(array $generics = []): array
    {
        $declared = DocBlockTypeResolver::instance()->templates($this->getDocComment() ?: '');
        $names = array_keys($declared);
        $resolved = [];

        foreach ($names as $position => $name) {
            $resolved[$name] = $generics[$position] ?? throw new BetterGenericException(
                "No generic defined for template `$name`",
            );
        }

        return $resolved;
    }

    /**
     * @return array<string, string>
     */
    private function aliases(): array
    {
        return DocBlockTypeResolver::instance()->typeAliases($this->getDocComment() ?: '');
    }
}
