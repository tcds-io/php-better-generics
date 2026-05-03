<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Generic\Unit\Reflection;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\Reflection\ReflectionClass;
use Tcds\Io\Generic\Reflection\Type\ClassReflectionType;
use Tcds\Io\Generic\Reflection\Type\GenericReflectionType;
use Test\Tcds\Io\Generic\Fixtures\Inheritance\AdminUserList;
use Test\Tcds\Io\Generic\Fixtures\Inheritance\StringUserPair;
use Test\Tcds\Io\Generic\Fixtures\Inheritance\UserCollection;
use Test\Tcds\Io\Generic\Fixtures\Inheritance\UserContainer;
use Test\Tcds\Io\Generic\Fixtures\User;

class InheritedGenericsTest extends TestCase
{
    #[Test] public function extends_single_template_resolves_parent_T_to_concrete_type(): void
    {
        $reflection = new ReflectionClass(UserCollection::class);

        $this->assertSame([
            'T' => User::class,
        ], $reflection->templates);
    }

    #[Test] public function extends_multi_template_resolves_each_position(): void
    {
        $reflection = new ReflectionClass(StringUserPair::class);

        $this->assertSame([
            'K' => 'string',
            'V' => User::class,
        ], $reflection->templates);
    }

    #[Test] public function transitive_inheritance_pulls_bindings_through_php_parent(): void
    {
        // AdminUserList extends UserListBase, which has `@extends Collection<User>`.
        // AdminUserList itself has no @extends — but should still see T=>User.
        $reflection = new ReflectionClass(AdminUserList::class);

        $this->assertSame([
            'T' => User::class,
        ], $reflection->templates);
    }

    #[Test] public function implements_resolves_interface_template(): void
    {
        $reflection = new ReflectionClass(UserContainer::class);

        $this->assertSame([
            'TItem' => User::class,
        ], $reflection->templates);
    }

    #[Test] public function inherited_method_return_type_resolves_via_extends(): void
    {
        // The whole point: a method declared on Collection with `@return list<T>`
        // resolves to `list<User>` when reflected through UserCollection.
        $reflection = new ReflectionClass(UserCollection::class);
        $type = $reflection->getMethod('items')->getReturnType();

        $this->assertInstanceOf(GenericReflectionType::class, $type);
        $this->assertSame('list', $type->type);
        $this->assertSame([User::class], $type->generics);
    }

    #[Test] public function inherited_method_parameter_resolves_via_extends(): void
    {
        $reflection = new ReflectionClass(UserCollection::class);
        $params = $reflection->getMethod('add')->getParameters();
        $type = $params[0]->getType();

        $this->assertInstanceOf(ClassReflectionType::class, $type);
        $this->assertSame(User::class, $type->getName());
    }

    #[Test] public function inherited_method_works_in_chain(): void
    {
        $reflection = new ReflectionClass(AdminUserList::class);
        $type = $reflection->getMethod('items')->getReturnType();

        $this->assertInstanceOf(GenericReflectionType::class, $type);
        $this->assertSame('list', $type->type);
        $this->assertSame([User::class], $type->generics);
    }
}
