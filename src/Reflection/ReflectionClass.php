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
     * construction time via the positional $generics argument, then
     * augments the map with bindings inherited via @extends/@implements.
     *
     * Bounds (`@template T of Foo`) are dropped here — preserved by the
     * resolver but not yet consumed by the rest of the layer; that's a
     * follow-up.
     *
     * Inheritance handling: for each `@extends Parent<Arg, ...>` (or
     * `@implements`) the args are resolved through this class's own
     * already-bound templates and `use` statements, then a recursive
     * ReflectionClass for the parent is constructed and its templates
     * are merged in. The child's own templates take precedence on name
     * collision; parent templates fill in everything else.
     *
     * @param list<string> $generics
     * @return array<string, string>
     */
    private function templates(array $generics = []): array
    {
        $resolver = DocBlockTypeResolver::instance();
        $docblock = $this->getDocComment() ?: '';

        $declared = $resolver->templates($docblock);
        $names = array_keys($declared);
        $resolved = [];

        foreach ($names as $position => $name) {
            $resolved[$name] = $generics[$position] ?? throw new BetterGenericException(
                "No generic defined for template `$name`",
            );
        }

        $inheritanceContext = new TypeContext(
            namespace: $this->getNamespaceName(),
            filename: $this->getFileName() ?: '',
            templates: $resolved,
            aliases: [],
            scopeClass: $this->name,
        );

        foreach ($resolver->inheritedGenerics($docblock, $inheritanceContext) as $parentFqn => $args) {
            if ($args === []) {
                continue;
            }

            $parentType = sprintf('%s<%s>', $parentFqn, implode(', ', $args));
            $parentReflection = new self($parentType);

            // Child bindings win on name collision.
            $resolved += $parentReflection->templates;
        }

        // Transitive inheritance: a class without its own @extends still
        // inherits whatever bindings its PHP parent class resolved (e.g.
        // AdminUserList extends UserListBase where UserListBase has
        // `@extends Collection<User>`). If the parent itself has unbound
        // templates (raw generic class with no args), it's not constructible
        // and we just skip — no inheritance is possible at that level.
        $phpParent = parent::getParentClass();
        if ($phpParent !== false) {
            try {
                $resolved += new self($phpParent->name)->templates;
            } catch (BetterGenericException $e) {
                // Parent declares @template but provides no @extends args
                // for them — leave its templates unresolved here.
                unset($e);
            }
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
