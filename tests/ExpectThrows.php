<?php

declare(strict_types=1);

namespace Tcds\Io\Generic;

use PHPUnit\Framework\AssertionFailedError;
use Throwable;

trait ExpectThrows
{
    public function expectThrows(callable $action): Throwable
    {
        try {
            $action();
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (Throwable $exception) {
            return $exception;
        }

        throw new AssertionFailedError('Failed asserting that an exception was thrown');
    }
}
