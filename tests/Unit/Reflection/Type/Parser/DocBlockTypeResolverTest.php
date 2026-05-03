<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Unit\Reflection\Type\Parser;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\Reflection\Type\Parser\DocBlockTypeResolver;

class DocBlockTypeResolverTest extends TestCase
{
    private DocBlockTypeResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new DocBlockTypeResolver();
    }

    #[Test] public function param_type_string_extracts_named_param(): void
    {
        $docblock = <<<'PHP'
        /**
         * @param list<int> $numbers
         * @param string $name
         * @return bool
         */
        PHP;

        $this->assertSame('list<int>', $this->resolver->paramTypeStringFromDocblock($docblock, 'numbers'));
        $this->assertSame('string', $this->resolver->paramTypeStringFromDocblock($docblock, 'name'));
        $this->assertNull($this->resolver->paramTypeStringFromDocblock($docblock, 'missing'));
    }

    #[Test] public function param_type_returns_null_for_empty_docblock(): void
    {
        $this->assertNull($this->resolver->paramTypeStringFromDocblock('', 'x'));
        $this->assertNull($this->resolver->paramTypeStringFromDocblock('   ', 'x'));
    }

    #[Test] public function return_type_string_extracts_return(): void
    {
        $docblock = "/** @return list<\\Foo\\Bar> */";

        $this->assertSame('list<\\Foo\\Bar>', $this->resolver->returnTypeStringFromDocblock($docblock));
    }

    #[Test] public function return_type_returns_null_when_absent(): void
    {
        $this->assertNull($this->resolver->returnTypeStringFromDocblock('/** @param int $x */'));
        $this->assertNull($this->resolver->returnTypeStringFromDocblock(''));
    }

    #[Test] public function generic_type_parts_for_plain_identifier(): void
    {
        $this->assertSame(['Foo', []], $this->resolver->genericTypeParts('Foo'));
        $this->assertSame(['int', []], $this->resolver->genericTypeParts('int'));
    }

    #[Test] public function generic_type_parts_for_array_suffix_normalises_to_list(): void
    {
        $this->assertSame(['list', ['Foo']], $this->resolver->genericTypeParts('Foo[]'));
    }

    #[Test] public function generic_type_parts_for_array_with_one_arg_normalises_to_list(): void
    {
        $this->assertSame(['list', ['int']], $this->resolver->genericTypeParts('array<int>'));
    }

    #[Test] public function generic_type_parts_for_array_with_two_args_keeps_array(): void
    {
        $this->assertSame(['array', ['string', 'int']], $this->resolver->genericTypeParts('array<string, int>'));
    }

    #[Test] public function generic_type_parts_for_nested_generic(): void
    {
        $this->assertSame(
            ['list', ['Foo<int, string>']],
            $this->resolver->genericTypeParts('list<Foo<int, string>>'),
        );
    }

    #[Test] public function generic_type_parts_for_union_keeps_whole_string(): void
    {
        // No `<` in the input, so legacy behaviour is to leave it untouched.
        $this->assertSame(['int|string', []], $this->resolver->genericTypeParts('int|string'));
    }

    #[Test] public function shape_member_strings_extracts_named_keys(): void
    {
        $this->assertSame(
            ['array', ['name' => 'string', 'age' => 'int']],
            $this->resolver->shapeMemberStrings('array{name: string, age: int}'),
        );
    }

    #[Test] public function shape_member_strings_with_nested_shape(): void
    {
        $this->assertSame(
            ['array', ['user' => 'array{ name: string }']],
            $this->resolver->shapeMemberStrings('array{user: array{name: string}}'),
        );
    }

    #[Test] public function templates_returns_name_to_bound_map(): void
    {
        $docblock = <<<'PHP'
        /**
         * @template T
         * @template K of string
         * @template V of \Foo\Bar
         */
        PHP;

        $this->assertSame(
            ['T' => null, 'K' => 'string', 'V' => '\\Foo\\Bar'],
            $this->resolver->templates($docblock),
        );
    }

    #[Test] public function type_aliases_returns_alias_to_type(): void
    {
        $docblock = <<<'PHP'
        /**
         * @phpstan-type Primitive int|string|bool
         * @phpstan-type Entries list<Primitive>
         */
        PHP;

        $this->assertSame(
            ['Primitive' => 'int|string|bool', 'Entries' => 'list<Primitive>'],
            $this->resolver->typeAliases($docblock),
        );
    }

    #[Test] public function instance_returns_singleton(): void
    {
        DocBlockTypeResolver::clearInstance();

        $a = DocBlockTypeResolver::instance();
        $b = DocBlockTypeResolver::instance();

        $this->assertSame($a, $b);
    }
}
