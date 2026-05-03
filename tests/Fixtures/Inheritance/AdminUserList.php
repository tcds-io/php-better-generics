<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Fixtures\Inheritance;

/**
 * No own @extends with generics — pulls T => User transitively from
 * UserListBase's own @extends Collection<User>.
 */
class AdminUserList extends UserListBase
{
}
