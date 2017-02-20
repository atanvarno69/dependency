# Atan\Dependency
A bare bones [PSR-11](http://www.php-fig.org/psr/psr-11/) dependency injection container, implementing `ContainerInterface` and `ArrayAccess`.

## Requirements
**PHP >= 7.0** is required, but the latest stable version of PHP is recommended.

## Installation
Add the following to your `composer.json` file:
```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/atanvarno69/dependency.git"
        }
    ],
    "require": {
        "atan/dependency": "^0.3.0"
    }
}
```
Then:
```bash
$ composer install
```

## Basic Usage
```php
use Atan\Dependency\{
    Container, Definition
};

// Create the container:
$container = new Container();

// Add an entery (which can be of any type):
$container->add('ID', $someEntry);

// Get an entry
$entry = $container->get('ID');

// To define a class to be lazy loaded, use the `define()` method:
$container->add(
    'Lazy', $container->define(ClassName::class, ...$constructorParameters)
);

// To pass a container entry as a constructor parameter use the `entry()` method:
$container->add('parameter', $value);
$container->add(
    'Lazy', $container->define(ClassName::class, $container->entry('parameter'))
);

// The same entry for a lazy loaded class is always returned after it has been
// created the first time. You can instead return a new instance on each `get()` 
// call by using `factory()` instead of `define()`:
$container->add(
    'Lazy', $container->factory(ClassName::class, ...$constructorParameters)
);

// Check the container has an entry for a given identifier using `has()`:
$item = $container->has('ID') ? $container->get('ID') : 'Not set';

// Delete an entry from the container:
$container->delete('ID');

// You can use array syntax instead:
$container['ID'] = $someEntry; # Add an entry
$array = $container['ID'];     # Get an entry
isset($container['ID']);       # Check an entry
unset($container['ID']);       # Delete an entry
```

## Full API
See [API](https://github.com/atanvarno69/dependency/blob/master/docs/API.md).
