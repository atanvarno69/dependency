# Atanvarno\Dependency
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/atanvarno69/dependency/blob/master/LICENSE)
[![Latest Version](https://img.shields.io/github/release/atanvarno69/dependency.svg?style=flat-square)](https://github.com/atanvarno69/dependency/releases)
[![Build Status](https://img.shields.io/travis/atanvarno69/dependency/master.svg?style=flat-square)](https://travis-ci.org/atanvarno69/dependency)
[![Coverage Status](https://img.shields.io/coveralls/atanvarno69/dependency/master.svg?style=flat-square)](https://coveralls.io/r/atanvarno69/dependency?branch=master)

A bare bones [PSR-11](http://www.php-fig.org/psr/psr-11/) dependency injection container, implementing `ContainerInterface` and `ArrayAccess`.

## Requirements
**PHP >= 7.0** is required, but the latest stable version of PHP is recommended.

## Installation
```bash
$ composer require atanvarno/dependency:^1.1.0
```

## Basic Usage
```php
use Atanvarno\Dependency\Container;

// Create the container:
$container = new Container();

// Add an entery (which can be of any type):
$container->add('ID', $someEntry);

// Get an entry
$entry = $container->get('ID');

// Check the container has an entry for a given identifier using `has()`:
$item = $container->has('ID') ? $container->get('ID') : 'Not set';

// To define a class to be lazy loaded, use the `define()` method:
$constructorParameters = ['some', $parameters];
$container->add(
    'Lazy', $container->define(ClassName::class, $constructorParameters)
);

// To pass a container entry as a constructor parameter use the `entry()` method:
$container->add('parameter', $value);
$container->add(
    'Lazy', $container->define(ClassName::class, [$container->entry('parameter')])
);

// You can give a factory `callable` instead of a definition using the `factory()` method:
$callable = function (...$params) {
    return new ClassName(...$params);
};
$container->add('Lazy', $container->factory($callable, $parameters));

// The same entry for a lazy loaded class is always returned after it has been
// created the first time. You can instead return a new instance on each `get()` 
// call by passing `false` as `define()` or `factory()`'s third parameter:
$container->add(
    'Lazy', $container->define(ClassName::class, $constructorParameters, false)
);

// Delete an entry from the container:
$container->delete('ID');

// You can use array syntax instead:
$container['ID'] = $someEntry; # Add an entry
$object = $container['ID'];    # Get an entry
isset($container['ID']);       # Check an entry
unset($container['ID']);       # Delete an entry
```

## Full API
See [API](https://github.com/atanvarno69/dependency/blob/master/docs/API.md).
