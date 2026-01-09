<?php

declare(strict_types=1);

namespace Tcds\Io\Generic\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RunTest extends TestCase
{
    #[Test] public function given_a_lambda_then_run_and_return_value(): void
    {
        $value = 'foo';

        $new = run(fn() => "$value-bar");

        $this->assertEquals('foo-bar', $new);
    }
}
