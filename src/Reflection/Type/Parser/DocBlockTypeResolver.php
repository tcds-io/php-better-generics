<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Reflection\Type\Parser;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\ObjectShapeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser as PhpDocTypeParser;
use PHPStan\PhpDocParser\ParserConfig;
use Tcds\Io\Generic\Reflection\Type\TypeContext;

/**
 * Facade over phpstan/phpdoc-parser. Hides the AST machinery from the rest
 * of the reflection layer and exposes string-shaped methods compatible with
 * the legacy TypeParser/ShapeTypeParser/GenericTypeParser API. Later commits
 * will introduce node-aware methods that return ReflectionType directly.
 *
 * Single shared instance per process. Both PhpDocNode and TypeNode parses
 * are memoised by input hash to avoid re-tokenising frequently re-used
 * docblocks/types (every reflection of the same class hits the same docblock).
 */
final class DocBlockTypeResolver
{
    private static ?self $instance = null;

    private readonly Lexer $lexer;
    private readonly PhpDocParser $phpDocParser;
    private readonly PhpDocTypeParser $typeParser;

    /** @var array<string, PhpDocNode> */
    private array $docCache = [];

    /** @var array<string, TypeNode> */
    private array $typeCache = [];

    public function __construct()
    {
        $config = new ParserConfig(usedAttributes: []);
        $constExprParser = new ConstExprParser($config);
        $this->typeParser = new PhpDocTypeParser($config, $constExprParser);
        $this->phpDocParser = new PhpDocParser($config, $this->typeParser, $constExprParser);
        $this->lexer = new Lexer($config);
    }

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    /**
     * Returns the rendered type string for the @param annotation matching $paramName,
     * or null if the docblock does not document that parameter.
     */
    public function paramTypeStringFromDocblock(string $docblock, string $paramName): ?string
    {
        if (trim($docblock) === '') {
            return null;
        }

        $needle = '$' . $paramName;

        foreach ($this->parseDoc($docblock)->getParamTagValues() as $param) {
            if ($param->parameterName === $needle) {
                return self::renderType($param->type);
            }
        }

        return null;
    }

    /**
     * Returns the rendered type string for the @return annotation, or null if absent.
     */
    public function returnTypeStringFromDocblock(string $docblock): ?string
    {
        if (trim($docblock) === '') {
            return null;
        }

        $tags = $this->parseDoc($docblock)->getReturnTagValues();

        return $tags === [] ? null : self::renderType($tags[0]->type);
    }

    /**
     * Splits a generic type string into its main type and its generic arguments,
     * preserving the legacy normalisations:
     *   - `Foo[]` => ['list', ['Foo']]
     *   - `array<X>` => ['list', ['X']]
     *   - `Foo<A, B>` => ['Foo', ['A', 'B']]
     *   - `Foo` (non-generic) => ['Foo', []]
     *   - `Foo|Bar` (no <...>) => ['Foo|Bar', []]
     *
     * @return array{string, list<string>}
     */
    public function genericTypeParts(string $type): array
    {
        $type = trim($type);

        if ($type === '') {
            return ['', []];
        }

        $node = $this->parseType($type);

        if ($node instanceof ArrayTypeNode) {
            return ['list', [self::renderType($node->type)]];
        }

        if ($node instanceof GenericTypeNode) {
            $main = $node->type->name;
            $generics = array_values(array_map(self::renderType(...), $node->genericTypes));

            if ($main === 'array' && count($generics) === 1) {
                $main = 'list';
            }

            return [$main, $generics];
        }

        // Non-generic identifiers, unions, intersections, callables — leave whole.
        return [self::renderType($node), []];
    }

    /**
     * Returns the array shape kind (`array`, `list`, …) and an ordered map of
     * member name => member type string. For unkeyed items the index is used as
     * the key, mirroring the legacy ShapeTypeParser output.
     *
     * @return array{string, array<string, string>}
     */
    public function shapeMemberStrings(string $shape): array
    {
        $node = $this->parseType($shape);

        if ($node instanceof ObjectShapeNode) {
            $members = [];

            foreach ($node->items as $item) {
                $members[(string) $item->keyName] = self::renderType($item->valueType);
            }

            return ['object', $members];
        }

        if (!$node instanceof ArrayShapeNode) {
            return [self::renderType($node), []];
        }

        $members = [];
        $autoIndex = 0;

        foreach ($node->items as $item) {
            if ($item->keyName !== null) {
                $key = (string) $item->keyName;
            } else {
                $key = (string) $autoIndex;
                $autoIndex += 1;
            }

            $members[$key] = self::renderType($item->valueType);
        }

        return [$node->kind, $members];
    }

    /**
     * Returns @template declarations as a map of name => bound (rendered as a
     * string, or null when unbounded). Bounds are kept as strings — resolution
     * to FQNs happens at the caller via TypeContext.
     *
     * @return array<string, ?string>
     */
    public function templates(string $docblock): array
    {
        if (trim($docblock) === '') {
            return [];
        }

        $templates = [];

        foreach ($this->parseDoc($docblock)->getTemplateTagValues() as $template) {
            $templates[$template->name] = $template->bound !== null ? self::renderType($template->bound) : null;
        }

        return $templates;
    }

    /**
     * Returns parents declared via `extends`/`implements` PHPDoc tags with
     * their generic arguments fully FQN-resolved (and recursively walked
     * through nested generics) against the given context. Used by
     * ReflectionClass to inherit template bindings from a parent.
     *
     * @return array<class-string, list<string>>
     */
    public function inheritedGenerics(string $docblock, TypeContext $context): array
    {
        if (trim($docblock) === '') {
            return [];
        }

        $doc = $this->parseDoc($docblock);
        $clauses = [];

        foreach ($doc->getExtendsTagValues() as $tag) {
            $entry = $this->resolveClause($tag->type, $context);

            if ($entry === null) {
                continue;
            }

            [$parentFqn, $args] = $entry;
            $clauses[$parentFqn] = $args;
        }

        foreach ($doc->getImplementsTagValues() as $tag) {
            $entry = $this->resolveClause($tag->type, $context);

            if ($entry === null) {
                continue;
            }

            [$parentFqn, $args] = $entry;
            $clauses[$parentFqn] = $args;
        }

        return $clauses;
    }

    /**
     * @return array{class-string, list<string>}|null
     */
    private function resolveClause(GenericTypeNode $clause, TypeContext $context): ?array
    {
        /** @var class-string $parentFqn */
        $parentFqn = $context->fqnOf($clause->type->name);

        if (!class_exists($parentFqn) && !interface_exists($parentFqn)) {
            return null;
        }

        return [$parentFqn, array_values(array_map(
            fn (TypeNode $a) => $this->resolveTypeNode($a, $context),
            $clause->genericTypes,
        ))];
    }

    /**
     * Walks a TypeNode replacing every identifier with its FQN-resolved form
     * (using `TypeContext::fqnOf`), preserving structure. Used to translate
     * `@extends Foo<User, list<Order>>` written in a child's namespace into
     * a string the parent's ReflectionClass can ingest unambiguously.
     */
    private function resolveTypeNode(TypeNode $node, TypeContext $context): string
    {
        if ($node instanceof IdentifierTypeNode) {
            // Args may reference one of the child's own templates (e.g.
            // `@template U` + `@extends Collection<U>`). Substitute first,
            // then fall back to import-based FQN resolution.
            return $context->templates[$node->name] ?? $context->fqnOf($node->name);
        }

        if ($node instanceof GenericTypeNode) {
            $head = $context->templates[$node->type->name] ?? $context->fqnOf($node->type->name);
            $args = array_map(fn (TypeNode $a) => $this->resolveTypeNode($a, $context), $node->genericTypes);

            return $head . '<' . implode(', ', $args) . '>';
        }

        if ($node instanceof NullableTypeNode) {
            return '?' . $this->resolveTypeNode($node->type, $context);
        }

        if ($node instanceof ArrayTypeNode) {
            return $this->resolveTypeNode($node->type, $context) . '[]';
        }

        if ($node instanceof UnionTypeNode) {
            return implode('|', array_map(fn (TypeNode $a) => $this->resolveTypeNode($a, $context), $node->types));
        }

        if ($node instanceof IntersectionTypeNode) {
            return implode('&', array_map(fn (TypeNode $a) => $this->resolveTypeNode($a, $context), $node->types));
        }

        // Shapes and exotic nodes — fall back to the compact renderer.
        return self::renderType($node);
    }

    /**
     * Returns @phpstan-type aliases as name => raw type string.
     *
     * @return array<string, string>
     */
    public function typeAliases(string $docblock): array
    {
        if (trim($docblock) === '') {
            return [];
        }

        $aliases = [];

        foreach ($this->parseDoc($docblock)->getTypeAliasTagValues() as $alias) {
            $aliases[$alias->alias] = self::renderType($alias->type);
        }

        return $aliases;
    }

    private function parseDoc(string $docblock): PhpDocNode
    {
        $hash = md5($docblock);

        if (isset($this->docCache[$hash])) {
            return $this->docCache[$hash];
        }

        $tokens = new TokenIterator($this->lexer->tokenize($docblock));

        return $this->docCache[$hash] = $this->phpDocParser->parse($tokens);
    }

    private function parseType(string $type): TypeNode
    {
        $hash = md5($type);

        if (isset($this->typeCache[$hash])) {
            return $this->typeCache[$hash];
        }

        $tokens = new TokenIterator($this->lexer->tokenize($type));
        $node = $this->typeParser->parse($tokens);

        return $this->typeCache[$hash] = $node;
    }

    public static function clearInstance(): void
    {
        self::$instance = null;
    }

    /**
     * Compact renderer that mirrors the hand-written legacy format
     * (`int|string`, `Foo<A, B>`, `Foo[]`, `array{name: string}`) instead of
     * phpdoc-parser's spaced-and-parenthesised default (`(int | string)`).
     * Falls back to (string) cast for nodes we don't model explicitly.
     */
    public static function renderType(TypeNode $node): string
    {
        if ($node instanceof IdentifierTypeNode) {
            return $node->name;
        }

        if ($node instanceof UnionTypeNode) {
            return implode('|', array_map(self::renderType(...), $node->types));
        }

        if ($node instanceof IntersectionTypeNode) {
            return implode('&', array_map(self::renderType(...), $node->types));
        }

        if ($node instanceof NullableTypeNode) {
            return '?' . self::renderType($node->type);
        }

        if ($node instanceof ArrayTypeNode) {
            return self::renderType($node->type) . '[]';
        }

        if ($node instanceof GenericTypeNode) {
            $args = implode(', ', array_map(self::renderType(...), $node->genericTypes));

            return $node->type->name . '<' . $args . '>';
        }

        if ($node instanceof ArrayShapeNode) {
            $items = [];

            foreach ($node->items as $item) {
                $key = $item->keyName !== null ? (string) $item->keyName : null;
                $value = self::renderType($item->valueType);
                $items[] = $key !== null ? "$key: $value" : $value;
            }

            return $node->kind . '{ ' . implode(', ', $items) . ' }';
        }

        if ($node instanceof ObjectShapeNode) {
            $items = [];

            foreach ($node->items as $item) {
                $items[] = (string) $item->keyName . ': ' . self::renderType($item->valueType);
            }

            return 'object{ ' . implode(', ', $items) . ' }';
        }

        return (string) $node;
    }
}
