<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Unit\Reflection\Type;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tcds\Io\Generic\Reflection\Type\Parser\UseStatementExtractor;
use Tcds\Io\Generic\Reflection\Type\TypeContext;
use Test\Tcds\Io\Generic\Fixtures\Bar;
use Test\Tcds\Io\Generic\Fixtures\Foo;
use Test\Tcds\Io\Generic\Fixtures\UseStatements\AliasedImports;
use Test\Tcds\Io\Generic\Fixtures\UseStatements\PlainImports;

class TypeContextTest extends TestCase
{
    protected function setUp(): void
    {
        UseStatementExtractor::clearCache();
    }

    #[Test] public function fqn_of_resolves_short_name_via_use_statement(): void
    {
        $context = $this->contextFor(PlainImports::class);

        $this->assertSame(Foo::class, $context->fqnOf('Foo'));
        $this->assertSame(Bar::class, $context->fqnOf('Bar'));
    }

    #[Test] public function fqn_of_honors_use_alias(): void
    {
        $context = $this->contextFor(AliasedImports::class);

        // `use Bar as Renamed` — the alias should resolve to Bar's FQN.
        $this->assertSame(Bar::class, $context->fqnOf('Renamed'));
    }

    #[Test] public function fqn_of_resolves_self_to_scope_class(): void
    {
        $context = $this->contextFor(PlainImports::class);

        $this->assertSame(PlainImports::class, $context->fqnOf('self'));
        $this->assertSame(PlainImports::class, $context->fqnOf('static'));
    }

    #[Test] public function fqn_of_returns_self_literal_when_scope_unknown(): void
    {
        $context = new TypeContext(
            namespace: '',
            filename: '',
            templates: [],
            aliases: [],
            scopeClass: null,
        );

        $this->assertSame('self', $context->fqnOf('self'));
    }

    #[Test] public function fqn_of_falls_back_to_same_namespace(): void
    {
        $context = new TypeContext(
            namespace: 'Test\\Tcds\\Io\\Generic\\Fixtures',
            filename: '',
            templates: [],
            aliases: [],
        );

        $this->assertSame(Bar::class, $context->fqnOf('Bar'));
    }

    #[Test] public function fqn_of_returns_input_for_unknown_type(): void
    {
        $context = new TypeContext(
            namespace: '',
            filename: '',
            templates: [],
            aliases: [],
        );

        $this->assertSame('UnknownType', $context->fqnOf('UnknownType'));
    }

    #[Test] public function type_resolves_via_aliases_then_templates_then_fqn(): void
    {
        $context = new TypeContext(
            namespace: 'Test\\Tcds\\Io\\Generic\\Fixtures',
            filename: '',
            templates: ['T' => 'Bar'],
            aliases: ['MyAlias' => 'T'],
        );

        // MyAlias -> T -> Bar -> Test\...\Bar
        $this->assertSame(Bar::class, $context->type('MyAlias'));
    }

    /**
     * @param class-string $class
     */
    private function contextFor(string $class): TypeContext
    {
        $reflection = new ReflectionClass($class);

        return new TypeContext(
            namespace: $reflection->getNamespaceName(),
            filename: $reflection->getFileName() ?: '',
            templates: [],
            aliases: [],
            scopeClass: $reflection->name,
        );
    }
}
