# Atanvarno\Dependency\Container
A basic container implementing [PSR-11 `ContainerInterface`](http://www.php-fig.org/psr/psr-11/#21-psrcontainercontainerinterface).
```php
class Container implements ArrayAccess, ContainerInterface
{
    // Methods
    public function __construct(string $id = 'container', CacheInterface $cache = null)
    public function add(string $id, mixed $value): Container
    public function define(string $className, array $parameters, bool $register = true): Definition
    public function delete(string $id): Container
    public function entry(string $id): EntryProxy
    public function factory(string $className, ...$parameters): Definition
    public function get(string $id): mixed
    public function has(string $id): bool
}
```
The container may contain and return any PHP type. These container entries are associated with a unique user-defined `string` identifier. All entries, except those defined with [`factory()`](#factory), are registered, that is a call to [`get()`](#get) with the identifier will always return the same value.

Entries can be defined using the [`add()`](#add) method. Lazy loaded classes are defined using [`define()`](#define) (for registered classes) or [`factory()`] (#factory) (for unregistered classes).

As `Container` implements [`ArrayAccess`](http://php.net/manual/en/class.arrayaccess.php), it can be used with array syntax:
```php
# Array syntax              # Alias of
$container['ID'] = $value;  $container->add('ID', $value);
$item = $container['ID'];   $item = $container->get('ID');
isset($container['ID']);    $container->has('ID');
unset($container['ID']);    $container->delete('ID');
```
Note that unlike a normal array, only `string` identifiers will be accepted by the array syntax (as [PSR-11](http://www.php-fig.org/psr/psr-11/#21-psrcontainercontainerinterface) only permits `string` identifiers); using `int` (or other) identifier types with array syntax will silently fail.

* [__construct](#__construct)
* [add](#add)
* [define](#define)
* [delete](#delete)
* [entry](#entry)
* [factory](#factory)
* [get](#get)
* [has](#has)

## __construct
```php
__construct(string $id = 'container', CacheInterface $cache = null)
```
### Parameters
* `string` **$id**

  Optional. Defaults to `'container'`. An identifier for the container to retreive itself via [`get()`](#get).

* [`CacheInterface`](http://www.php-fig.org/psr/psr-16/#cacheinterface)

  Optional. Defaults to `null`. A [PSR-16](http://www.php-fig.org/psr/psr-16/) cache for the container to use.

### Throws
Nothing is thrown.

### Returns
A `Container` instance.

## add
Adds an entry to the container.
```php
add(string $id, mixed $value): Container
```
An entry can be of any type. To define a lazy loaded class, use [`define()`](#define) or [`factory()`](#factory).

### Parameters
* `string` **$id**

  Identifier of the entry to set.

* `mixed` **$value**

   Value of the entry to set.

### Throws
Nothing is thrown.

### Returns
* `Definition` **$this**

  [fluent interface](https://en.wikipedia.org/wiki/Fluent_interface), allowing multiple calls to be chained.

## define
Adds a class definition for lazy loading.
```php
define(string $className, array $parameters, bool $register = true): Definition
```
After first instantiation, the same instance will be returned by `get()` on each call. If this is not the desired behaviour, you should set the third parameter to `false`.
### Parameters
* `string` **$className**

  The name of the defined class. Using the [`::class` keyword](http://php.net/manual/en/language.oop5.basic.php#language.oop5.basic.class.class) is recommended.

* `mixed` **...$parameters**

  Optional. Defaults to `[]`. Values to pass to the class constructor.

* `bool` **$register**

  Optional. Defaults to `true`. Whether the entry should be registered.

### Throws
* `InvalidArgumentException`

  The given class name does not exist.

### Returns
* [`Definition`](Definition.md)

## delete
Deletes an entry from the container.
```php
delete(string $id): Container
```
### Parameters
* `string` **$id**

  Identifier of the entry to delete.

### Throws
Nothing is thrown.

### Returns
* `Definition` **$this**

  [fluent interface](https://en.wikipedia.org/wiki/Fluent_interface), allowing multiple calls to be chained.

## entry
Use a container entry as a parameter for a lazy loading definition.
```php
entry(string $id): EntryProxy
```
### Parameters
* `string` **$id**

  Identifier of the entry to reference.

### Throws
Nothing is thrown.

### Returns
* `EntryProxy`

  When an `EntryProxy` is encountered in a parameter list while resolving a definition it is replaced with the container entry with the given identifier.

## factory
Adds a factory callable for lazy loading.
```php
factory(callable $callable, array $parameters = [], $register = true): Definition
```
After first instantiation, the same instance will be returned by `get()` on each call. If this is not the desired behaviour, you should set the third parameter to `false`.

### Parameters
* `string` **$className**

  The name of the defined class. Using the [`::class` keyword](http://php.net/manual/en/language.oop5.basic.php#language.oop5.basic.class.class) is recommended.

* `mixed` **...$parameters**

  Optional. Defaults to `[]`. Values to pass to the class constructor.

* `bool` **$register**

  Optional. Defaults to `true`. Whether the entry should be registered.

### Throws
Nothing is thrown.

### Returns
* [`Definition`](Definition.md)

## get
Finds an entry of the container by its identifier and returns it.
```php
get(string $id): mixed
```
### Parameters
* `string` **$id**

  Identifier of the entry to look for.

### Throws
* `NotFoundExceptionInterface`

  No entry was found for this identifier.

* `ContainerExceptionInterface`

  Error while retrieving the entry.

### Returns
* `mixed`

  Entry.

## has
Returns `true` if the container can return an entry for the given identifier. Returns `false` otherwise.
```php
has(string $id): bool
```
`has($id)` returning `true` does not mean that [`get($id)`](#get) will not throw an exception. It does however mean that [`get($id)`](#get) will not throw a `NotFoundExceptionInterface`.
### Parameters
#### id
* `string` **$id**

  Identifier of the entry to look for.

### Throws
Nothing is thrown.

### Returns
* `bool`

  `true` if the container can return an entry for the given identifier. `false` otherwise.
