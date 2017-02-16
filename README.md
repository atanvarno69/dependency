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

// Define some enteries (which can be of any type):
$container->add('ID', ['some', 'values']);
$container['ID'] = ['some', 'values'];     // As above, but using array access.

// Get an entry
$entry = $container->get('ID');
$entry = $container['ID'];      // Using array access.

// Define a class to be lazy loaded
$container->add('Lazy', new Definition(SomeClass::class));
$container['Lazy'] = new Definition(SomeClass::class);     // Using array access.

// Define a lazy loaded class with values to be passed to its constructor:
$container->add('Lazy',
    new Definition(SomeClass::class, [1, 'two', [3, 'is', 'an', 'array'], null])
);

// Use a container ID string prefixed with ':' to pass a container entry as a parameter:
$container['param2'] = 2;
$container['Lazy'] = new Definition(SomeClass::class, [1, ':param2']);

// By default the same entry for a lazy loaded class is always returned after it
// has been created the first time. You can instead return a new instance on
// each call by passing `false` as the third parameter to the definition:
$container['Lazy'] = new Definition(SomeClass::class, [], false);
```

## Full API
See [API](https://github.com/atanvarno69/dependency/blob/master/docs/API.md).
