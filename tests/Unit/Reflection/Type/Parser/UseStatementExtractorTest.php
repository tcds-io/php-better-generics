<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Unit\Reflection\Type\Parser;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tcds\Io\Generic\Reflection\Type\Parser\UseStatementExtractor;
use Test\Tcds\Io\Generic\Fixtures\UseStatements\AliasedImports;
use Test\Tcds\Io\Generic\Fixtures\UseStatements\ClosureUseClause;
use Test\Tcds\Io\Generic\Fixtures\UseStatements\GroupedAliasedImports;
use Test\Tcds\Io\Generic\Fixtures\UseStatements\GroupedImports;
use Test\Tcds\Io\Generic\Fixtures\UseStatements\MixedImports;
use Test\Tcds\Io\Generic\Fixtures\UseStatements\PlainImports;

class UseStatementExtractorTest extends TestCase
{
    private UseStatementExtractor $extractor;

    protected function setUp(): void
    {
        UseStatementExtractor::clearCache();
        $this->extractor = new UseStatementExtractor();
    }

    #[Test] public function extracts_plain_imports_keyed_by_short_name(): void
    {
        $result = $this->extractor->extract($this->fileOf(PlainImports::class));

        $this->assertSame([
            'Bar' => 'Test\\Tcds\\Io\\Generic\\Fixtures\\Bar',
            'Foo' => 'Test\\Tcds\\Io\\Generic\\Fixtures\\Foo',
        ], $result);
    }

    #[Test] public function alias_replaces_short_name_with_alias(): void
    {
        $result = $this->extractor->extract($this->fileOf(AliasedImports::class));

        $this->assertSame([
            'Renamed' => 'Test\\Tcds\\Io\\Generic\\Fixtures\\Bar',
            'Foo' => 'Test\\Tcds\\Io\\Generic\\Fixtures\\Foo',
        ], $result);
    }

    #[Test] public function group_use_expands_each_member_with_prefix(): void
    {
        $result = $this->extractor->extract($this->fileOf(GroupedImports::class));

        $this->assertSame([
            'Bar' => 'Test\\Tcds\\Io\\Generic\\Fixtures\\Bar',
            'Foo' => 'Test\\Tcds\\Io\\Generic\\Fixtures\\Foo',
        ], $result);
    }

    #[Test] public function group_use_supports_per_member_alias(): void
    {
        $result = $this->extractor->extract($this->fileOf(GroupedAliasedImports::class));

        $this->assertSame([
            'RenamedBar' => 'Test\\Tcds\\Io\\Generic\\Fixtures\\Bar',
            'Foo' => 'Test\\Tcds\\Io\\Generic\\Fixtures\\Foo',
        ], $result);
    }

    #[Test] public function function_and_const_imports_are_skipped(): void
    {
        $result = $this->extractor->extract($this->fileOf(MixedImports::class));

        $this->assertSame([
            'Foo' => 'Test\\Tcds\\Io\\Generic\\Fixtures\\Foo',
        ], $result);
    }

    #[Test] public function closure_use_clauses_are_not_treated_as_imports(): void
    {
        $result = $this->extractor->extract($this->fileOf(ClosureUseClause::class));

        $this->assertSame([
            'Foo' => 'Test\\Tcds\\Io\\Generic\\Fixtures\\Foo',
        ], $result);
    }

    #[Test] public function returns_empty_for_nonexistent_file(): void
    {
        $this->assertSame([], $this->extractor->extract('/no/such/file.php'));
        $this->assertSame([], $this->extractor->extract(''));
    }

    #[Test] public function caches_per_realpath(): void
    {
        $file = $this->fileOf(PlainImports::class);
        $first = $this->extractor->extract($file);
        $second = $this->extractor->extract($file);

        $this->assertSame($first, $second);
    }

    /**
     * @param class-string $class
     */
    private function fileOf(string $class): string
    {
        return new ReflectionClass($class)->getFileName() ?: '';
    }
}
