<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Fixtures\Inheritance;

use Test\Tcds\Io\Generic\Fixtures\User;

/**
 * @implements Container<User>
 */
class UserContainer implements Container
{
    public function __construct(public User $user)
    {
    }

    public function pick(): User
    {
        return $this->user;
    }
}
