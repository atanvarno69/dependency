# Atan\Dependency
[PSR-11 (container-interop)](https://github.com/container-interop/container-interop) dependency injection container.

## Requirements
*PHP >= 7.0* is required to use Atan\Dependency but the latest stable version of PHP is recommended.

## Installation
Add the following to your composer.json:
```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/atanvarno69/dependency.git"
        }
    ],
    "require": {
        "atan/dependency": "^0.2.0"
    }
}
```
Then:
```
$ composer install
```
Alternatively, add the repository above to your composer.json and:
```
$ composer require atan/dependency
```

## Container
[PSR-11](https://github.com/container-interop/container-interop) dependency injection container.
```php
use Atan\Dependency\Container;

$container = new Container();

// Define entries to be lazy loaded
$container->define('ID', ClassName::class, ['Constructor', 'Params', ':OtherID']);

// Register already instantiated entries
$container->register('OtherID', $dependency);

// Get an entry
$entry = $container->get('ID');
```
Container marks all entries as registered (the same entity will always be returned) upon first instantiation, unless told otherwise. Container can provide objects from a class name, the output of a callable, or any other non-`null` type.

Container implements the *delegate lookup* feature and can act as a composite container (with entries) and/or a child container, using its `setParent()`, `appendChild()` and `prependChild()` methods.

See the [API](https://github.com/atanvarno69/dependency/blob/master/doc/Container.md).

## Full API
* [Atan\Dependency\Container](https://github.com/atanvarno69/dependency/blob/master/doc/Container.md).
* [Atan\Dependency\Exception\ContainerException](https://github.com/atanvarno69/dependency/blob/master/doc/ContainerException.md).
* [Atan\Dependency\Exception\NotFoundException](https://github.com/atanvarno69/dependency/blob/master/doc/NotFoundException.md).
