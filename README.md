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
# or
$ composer update
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

// To define a class to be lazy loaded, use the `Definition` class:
$container->add('Lazy', new Definition(SomeClass::class));

// Define a lazy loaded class with values to be passed to its constructor:
$container->add('Lazy',
    new Definition(SomeClass::class, [1, 'two', [3, 'is', 'an', 'array'], null])
);

// Use a container ID string prefixed with ':' to pass a container entry as a parameter:
$container->add('param2', 2);
$container->add('Lazy', new Definition(SomeClass::class, [1, ':param2']));

// By default the same entry for a lazy loaded class is always returned after it
// has been created the first time. You can instead return a new instance on
// each call by passing `false` as the third parameter to the definition:
$container->add('Lazy', new Definition(SomeClass::class, [], false));

// Check the container has an entry for a given identifier:
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
