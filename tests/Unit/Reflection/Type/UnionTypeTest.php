<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Unit\Reflection\Type;

use PHPUnit\Framework\Attributes\Test;
use Tcds\Io\Generic\Reflection\ReflectionClass;
use Tcds\Io\Generic\Reflection\Type\PrimitiveReflectionType;
use Test\Tcds\Io\Generic\BetterGenericTestCase;
use Test\Tcds\Io\Generic\Fixtures\UnionType;

class UnionTypeTest extends BetterGenericTestCase
{
    #[Test]
    public function reflection_with_union_types(): void
    {
        $reflection = new ReflectionClass(UnionType::class);
        $constructor = $reflection->getConstructor();

        $params = $constructor->getParameters();

        $this->assertParams(
            [
                'value' => [PrimitiveReflectionType::class, 'string|int'],
            ],
            $params,
        );
    }
}
