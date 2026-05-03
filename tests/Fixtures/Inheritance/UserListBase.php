<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Fixtures\Inheritance;

use Test\Tcds\Io\Generic\Fixtures\User;

/**
 * Intermediate node in an inheritance chain — fixes Collection's T to User
 * but adds no own template. Subclasses inherit T => User transitively.
 *
 * @extends Collection<User>
 */
class UserListBase extends Collection
{
}
