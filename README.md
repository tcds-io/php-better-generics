# php-better-generics

PHP 8.4+ library for working with generics at runtime: typed collections, lazy object proxies, and a reflection layer that understands `@template`, `@extends`, `@implements`, generic types, and array shapes from PHPDoc.

## Why

PHP doesn't have native generics — only static analyzers (PHPStan, Psalm) do. This library bridges that gap at runtime by:

- Reading the PHPDoc you already write for static analysis (`@template T`, `@param list<Foo> $items`, `@extends Collection<User>`) and exposing it through a `ReflectionClass` API that resolves real FQNs.
- Providing thin, well-typed `ArrayList<T>` / `Map<K, V>` collections so `@template`-style code is also nice to write.
- Offering `lazyOf()` proxies on top of PHP 8.4's native lazy objects.

## Install

```bash
composer require tcds-io/php-better-generics
```

Single runtime dependency: [`phpstan/phpdoc-parser`](https://github.com/phpstan/phpdoc-parser) (zero deps of its own).

## Typed collections

```php
use function Tcds\Io\Generic\listOf;
use function Tcds\Io\Generic\mapOf;

$users = listOf($alice, $bob, $carol);

$names = $users
    ->filter(fn (User $u) => $u->isActive())
    ->map(fn (User $u) => $u->name);

$byEmail = $users->indexedBy(fn (User $u) => $u->email);
// Map<string, User>

$first = $users->first(fn (User $u) => $u->isAdmin());
$count = $users->count(fn (User $u) => $u->isActive());
```

`MutableArrayList` adds `push`, `pop`, `set`, `removeAt`, `clear`. `Map` implements `ArrayAccess` and `Countable`; `MutableMap` adds `put`, `putAll`, `remove`, `clear`.

## Lazy proxies

```php
use function lazyOf;
use function lazyBufferOf;

// Single proxy: $user is fully typed as User; constructor only runs on first access.
$user = lazyOf(User::class, fn () => $userRepository->find($id));

// Buffered loader: collects keys, then loads in one batch when the buffer fills.
$loader = lazyBufferOf(
    User::class,
    fn (array $ids) => $userRepository->findMany($ids),
    maxBufferSize: 50,
);

$users = $orderIds->map(fn (string $orderId) => $loader->lazyOf($orderId));
// Each access fetches up to 50 users at once instead of N+1.
```

## Generic-aware reflection

Read the PHPDoc, get back resolved types.

```php
use Tcds\Io\Generic\Reflection\ReflectionClass;

/** @template T */
class Collection {
    /** @return list<T> */
    public function items(): array { /* ... */ }
}

/** @extends Collection<User> */
class UserCollection extends Collection {}

$reflection = new ReflectionClass(UserCollection::class);

$type = $reflection->getMethod('items')->getReturnType();
// GenericReflectionType('list', [User::class])
// — `T` resolved through `@extends Collection<User>`.
```

Supported out of the box:

- `@template T` and `@template T of Foo` (bounds parsed but not yet enforced)
- `@param`, `@return`, `@var`
- `@extends Foo<X>`, `@implements Foo<X>` — including transitive inheritance through PHP `extends`
- `@phpstan-type` aliases
- Generics, array shapes (`array{name: string}`), unions, intersections, nullables, `Foo[]`
- Short-name resolution through `use` statements (including `use A as B`, `use A\{B, C}`, grouped imports)
- `self`, `static`, `parent` resolution against the declaring class

## Requirements

- PHP 8.4+

## Development

```bash
composer install
composer tests   # cs:check + test:stan + test:unit
```

## License

MIT — see `composer.json`.
