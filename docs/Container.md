# Atan\Dependency\Container
A basic container implementing [PSR-11 `ContainerInterface`](http://www.php-fig.org/psr/psr-11/#21-psrcontainercontainerinterface).
```php
class Container implements ArrayAccess, ContainerInterface
{
    // Methods
    public function __construct(string $id = 'container')
    public function add(string $id, mixed $value): void
    public function class(string $className, ...$parameters): Definition
    public function delete(string $id): void
    public function entry(string $id): EntryProxy
    public function factory(string $className, ...$parameters): Definition
    public function get(string $id)
    public function has(string $id): bool
}
```
The container may contain and return any PHP type. These container entries are associated with a unique user-defined `string` identifier. All entries, except those defined with [`factory()`](#factory), are registered, that is a call to [`get()`](#get) with the identifier will always return the same value.

Entries can be defined using the [`add()`](#add) method. Lazy loaded classes are defined using [`class()`](#class) (for registered classes) or [`factory()`](#factory) (for unregistered classes).

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
* [class](#class)
* [delete](#delete)
* [entry](#entry)
* [factory](#factory)
* [get](#get)
* [has](#has)

## __construct
```php
__construct(string $id = 'container')
```
### Parameters
* `string` **$id**

  Optional. Defaults to `'container'`. An identifier for the container to retreive itself via [`get()`](#get).

### Throws
Nothing is thrown.

### Returns
A `Container` instance.

## add
Adds an entry to the container.
```php
add(string $id, mixed $value): void
```
An entry can be of any type. To define a lazy loaded class, use [`class()`](#class) or [`factory()`](#factory).

### Parameters
* `string` **$id**

  Identifier of the entry to set.

* `mixed` **$value**

   Value of the entry to set.

### Throws
Nothing is thrown.

### Returns
* `void`

## class
Adds a registered class definition for lazy loading.
```php
class(string $className, mixed ...$parameters): Definition
```
After first instantiation, the same instance will be returned by [`get()`](#get) on each call. If this is not the desired behaviour, you should use [`factory()`](#factory).

### Parameters
* `string` **$className**

  The name of the defined class. Using the [`::class` keyword](http://php.net/manual/en/language.oop5.basic.php#language.oop5.basic.class.class) is recommended.

* `mixed` **...$parameters**

  Values to pass to the defined class's constructor. To use an entry defined in the container, use [`entry()`](#entry).

### Throws
* `InvalidArgumentException`

  The given class name does not exist.

### Returns
* [`Definition`](Definition.md)

## delete
Deletes an entry from the container.
```php
delete(string $id): void
```
### Parameters
* `string` **$id**

  Identifier of the entry to delete.

### Throws
Nothing is thrown.

### Returns
* `void`

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
Adds a registered class definition for lazy loading.
```php
class(string $className, mixed ...$parameters): Definition
```
A new instance will be returned by [`get()`](#get) on each call. If this is not the desired behaviour, you should use [`class()`](#class).

### Parameters
* `string` **$className**

  The name of the defined class. Using the [`::class` keyword](http://php.net/manual/en/language.oop5.basic.php#language.oop5.basic.class.class) is recommended.

* `mixed` **...$parameters**

  Values to pass to the defined class's constructor. To use an entry defined in the container, use [`entry()`](#entry).

### Throws
* `InvalidArgumentException`

  The given class name does not exist.

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
