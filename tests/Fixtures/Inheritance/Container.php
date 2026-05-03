<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Fixtures\Inheritance;

/**
 * @template TItem
 */
interface Container
{
    /**
     * @return TItem
     */
    public function pick(): mixed;
}
