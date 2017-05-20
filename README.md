# Atanvarno\Dependency
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/atanvarno69/dependency/blob/master/LICENSE)
[![Latest Version](https://img.shields.io/github/release/atanvarno69/dependency.svg?style=flat-square)](https://github.com/atanvarno69/dependency/releases)
[![Build Status](https://img.shields.io/travis/atanvarno69/dependency/master.svg?style=flat-square)](https://travis-ci.org/atanvarno69/dependency)
[![Coverage Status](https://img.shields.io/coveralls/atanvarno69/dependency/master.svg?style=flat-square)](https://coveralls.io/r/atanvarno69/dependency?branch=master)

A bare bones [PSR-11](http://www.php-fig.org/psr/psr-11/) dependency injection 
container, implementing [`ArrayAccess`](http://php.net/manual/en/class.arrayaccess.php).

## Features
+ Interoperability using [PSR-11](http://www.php-fig.org/psr/psr-11/)
+ Implements `container-interop`'s [delegate lookup feature](https://github.com/container-interop/container-interop/blob/master/docs/Delegate-lookup.md)
+ [Lazy loading](https://en.wikipedia.org/wiki/Lazy_loading) 
+ Optionally use [PSR-16](http://www.php-fig.org/psr/psr-16/) caching
+ Optionally use array syntax to access the container

`Atanvarno\Dependency` does **not** provide auto-wiring via reflection. It is a 
bare bones container only.

## Requirements
**PHP >= 7.0** is required, but the latest stable version of PHP is recommended.

## Installation
Atanvarno\Dependency is available on [Packagist](https://packagist.org/packages/atanvarno/dependency) 
and can be installed using [Composer](https://getcomposer.org/):
```bash
$ composer require atanvarno/dependency
```

## Basic Usage
```php
<?php
use Atanvarno\Dependency\Container;
use function Atanvarno\Dependency\{entry, factory, object, value};

$container = new Container();

// Add a value to the container
$container['value ID'] = 'your value';

// Add a lazy loaded class to the container
$container['class ID'] = object(YourClass::class, ['argument1', entry('value ID')]);

// Get a value from the container
$value = $container['value ID'];
var_dump($value === 'your value'); // true

// Get a class instance from the container
$instance = $container['class ID'];
var_dump($instance instanceof YourClass::class); // true
```
## Usage
### Retrieving Entries
There are two ways to retrieve an entry:
```php
// Using array syntax
$item1 = $container['ID'];

// Calling get()
$item2 = $container->get('ID');
```

Entries retrieved from an ID will, by default, be the same instance:
```php
var_dump($item1 === $item2); // true
```

You can specify that each time you get a particular ID it will be a new 
instance when you define your entries (see 
[lazy loaded entries](#lazy-loaded-entries)).

### Checking Entries
There are two ways to check if an entry is available, each returns `bool`:
```php
// Using array syntax
isset($container['ID']);

// Calling has()
$container->has('ID');
```

### Removing Entries
There are two ways to remove an entry:
```php
// Using array syntax
unset($container['ID']);

// Calling delete()
$container->delete('ID');
```

### Adding entries
There are several ways to add an entry:
```php
// Using array syntax
$container['ID'] = $entry;

// Calling set()
$container->set('ID', $entry);
```

`$entry` can be any PHP value. `Atanvarno\Dependency\Container` when used like 
this is a simple key-value store, not particularly different from an array.

Instead entries can be defined so that they are lazy loaded.

Entries can also be added via the constructor (see 
[instantiation](#instantiation)).

### Lazy Loading Entries
Any entry can be defined as *lazy loaded*, so it is built only when it is 
accessed.

Two helper functions are included to allow you define lazy loaded entries: 
[`factory()`](docs/Functions.md#factory) and [`object()`](docs/Functions.md#object). 

(The examples use the `set()` method, but array syntax works as well.)

You can return a value from any `callable` using `factory()`:
```php
$container->set('ID', factory(
    function() {
        // ...
    }
));
```

You can return an object from its class name using `object()`:
```php
$container->set('ID', object(ClassName::class));
```

Both `factory()` and `object()` take an array of parameters to pass to the 
`callable` or constructor. These parameters can be any PHP value or can be 
other container entries. Other container entries are referenced by using the 
[`entry()`](docs/Functions.md#entry) function:
```php
// Setting a factory
$callable = function(int $arg1, ClassName $arg2) {
    // ...
};
$container->set('ID', factory($callable, [5, entry('ClassInstance')]));

// Setting an object
class ClassName
{
    public function __construct(int arg1, OtherClass $arg2)
    {
        // ...
    }
}
$container->set('ID', object(ClassName::class, [5, entry('OtherInstance')]));
```

(The container itself can referenced using the default entry ID `container`. If 
you need the container to have a different entry ID, use `setSelfId()`.)

Both `factory()` and `object()` take an optional third `bool` parameter which 
determines whether the first value they return should be registered and 
subsequently always returned when the entry is retrieved (default behaviour), 
or whether a new value should be returned each time (pass `false`).
```php
// A non-registered factory
$container->get('ID', factory(function(){/*...*/}, [], false));

// A non-registered object
$container->get('ID', object(ClassName::class, [], false));
```

### Setting Properties and Calling Methods
You may want to set `public` properties or call methods of newly instantiated 
objects in order to configure them for use.

Both `factory()` and `object()` return a [`Definition`](docs/Definition.md) 
which provides methods with a fluent interface to allow this:
```php
$container->set(
    'ID',
    object(ClassName::class, [$param1, entry('param2')])
        ->method('methodName', [$param3, entry('param4')]) // Call a method with parameters
        ->property('propertyName', 'value') // Then set a property value
        ->property('otherProperty', entry('aValue')) // Then set another property
);
```
 
The example uses `object()`, but will work as well with `factory()`. Note if 
`factory()` does not define a object instance, these methods will do nothing.

### Delegate Lookup Feature
`Atanvarno\Dependency\Container` implements `container-interop`'s 
[delegate lookup feature](https://github.com/container-interop/container-interop/blob/master/docs/Delegate-lookup.md) 
and can act as both a parent/composite/delegate and child container. 

To add child containers (and make `$container` a composite container) use 
`addChild()`:
```php
$container->addChild($otherContainerA);
```

Subsequent calls will add additional children.

To add a parent container (and make `$container` delegate its dependency 
lookups) use `setDelegate()`:
```php
$container->setDelegate($otherContainerB);
```

Subsequent calls will replace the parent.

### Fluent Interface
`Atanvarno\Dependency\Container` provides a 
[fluent interface](https://en.wikipedia.org/wiki/Fluent_interface), allowing 
multiple calls to be chained. These methods each return the `Container` 
instance:
+ `addChild()`
+ `clearCache()`
+ `delete()`
+ `get()`
+ `set()`
+ `setDelegate()`
+ `setSelfId()`

### Instantiation
When the container is instantiated it optionally accepts an array of 
`Definition` instances (returned from `factory()`, `object()` and `value()`) 
which will be added to the container. This array is indexed by entry ID.
```php
$container = new Container([
    'called'
        => factory(
            function(ContainerInterface $c){return $c->get('value');},
            [entry('container')]
        ),
    'object'
        => object(
                ClassName::class,
                [entry('value'), true]
            )
            ->property('name', entry('called')
            ->method('methodName', 500),
    'value' => value('an arbitary PHP value'),
]);
```

You can make a configuration file that returns this array:
```php
<?php // containerConfig.php
use function Atanvarno\Dependency\{entry, factory, object, value};

return [
    'app' =>
        object(
            AppClass::class,
            [
                entry('container'),
                entry('router'),
                entry('cache'),
                entry('logger')
            ],
            false
        ),
    'cache' =>
        object(CacheClass::class, [entry('cache config')]),
    'cache config' =>
        factory(
            function(string $configDir){return $configDir . '/cache.php';},
            [entry('config directory')]
        ),
    'config directory' => 
        value(__DIR__),
    'logger' =>
        object(LoggerClass::class, [entry('log config')])
            ->method('pushHandler', [entry('log handler'), Logger::WARNING]),
    'log config' =>
        factory(
            function(string $configDir){return $configDir . '/logger.php';},
            [entry('config directory')]
        ),
    'log handler' =>
        object(LogHandler::class, [entry('log path')]),
    'response' =>
        factory(
            function(AppClass $app) {
                return $app->getResponse($app->getRequest());
            },
            [entry('app')]
        ),
    'router' =>
        object(RouterClass::class, [entry('router config')]),
    'router config' =>
        factory(
            function(string $configDir){return $configDir . '/routes.php';},
            [entry('config directory')]
        ),
];
```
Then include it in the constructor call:
```php
$container = new Container(include '../config/containerConfig.php');
```
### Caching
`Atanvarno\Dependency\Container` can use a 
[PSR-16](http://www.php-fig.org/psr/psr-16/) cache to persist registered items. 
Note it does not persist definitions; only registered values that have been 
returned at least once.

The cache is invisible to the user; calls to `delete()`,`get()` and `set()` 
will use and update the cache as they require.

If you want to clear the container's cache use `clearCache()`:
```php
// Clears the container's cache; other values stored in the cache are untouched
$container->clearCache();
```

The constructor accepts a cache as its second parameter and an optional key for 
use with the cache as its third parameter (defaults to `container`).
```php
/** @var CacheInterface $cache PSR-16 cache. */
$cache = /* ... */ ;
$container = new Container([], $cache, 'container-cache-key');
```

The constructor also accept an `entry()` that points to a cache defined in the 
first parameter, so the cache instance can be loaded by the container itself.

```php
$container = new Container(
    ['cache' => object(CacheClass::class)],
    entry('cache'),
    'cache-key'
);
```
### Exceptions
All exceptions thrown implement [PSR-11](http://www.php-fig.org/psr/psr-11/)'s 
`ContainerExceptionInterface`.

## Full API
See [API](https://github.com/atanvarno69/dependency/blob/master/docs/API.md).
