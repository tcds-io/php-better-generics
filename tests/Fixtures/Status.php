<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Fixtures;

enum Status: string
{
    case ACTIVE = 'ACTIVE';
    case INACTIVE = 'INACTIVE';
}
