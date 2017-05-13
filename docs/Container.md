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

Entries can be defined using the [`add()`](#add) method. Lazy loaded classes are defined using [`define()`](#define) (for registered classes) or [`factory()`](#factory) (for unregistered classes).

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
* [addChild](#addChild)
* [clearCache](#clearCache)
* [delete](#delete)
* [get](#get)
* [has](#has)
* [offsetExists](#offsetExists)
* [offsetGet](#offsetGet)
* [offsetSet](#offsetSet)
* [offsetUnset](#offsetUnset)
* [set](#set)
* [setDelegate](#setDelegate)
* [setSelfId](#setSelfId)

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

## addChild
Adds a child container.
```php
public function addChild(ContainerInterface $child): Container
```
This will make the container act as a composite container.

### Parameters
* [`ContainerInterface`](http://www.php-fig.org/psr/psr-11/#21-psrcontainercontainerinterface) **$child**

  Child container to add.

### Throws
Nothing is thrown.

### Returns
* `Container` **$this**

  [fluent interface](https://en.wikipedia.org/wiki/Fluent_interface), allowing 
  multiple calls to be chained.

## clearCache
Clear the container's cached values.
```php
public function clearCache(): Container
```
If no cache has been set, this method will do nothing.

### Parameters
* `void`

### Throws
* [`ContainerException`](ConfigurationException.md)

  Unable to clear cache.

### Returns
* `Container` **$this**

  [fluent interface](https://en.wikipedia.org/wiki/Fluent_interface), allowing 
  multiple calls to be chained.

## delete
Deletes an entry from the container.
```php
public function delete(string $id): Container
```
### Parameters
* `string` **$id**

  Entry to delete.

### Throws
Nothing is thrown.

### Returns
* `Container` **$this**

  [fluent interface](https://en.wikipedia.org/wiki/Fluent_interface), allowing 
  multiple calls to be chained.

## get
Retrieves an entry from the container.
```php
public function get(string $id)
```
### Parameters
* `string` **$id**

  Entry to retrieve.

### Throws
* [`NotFoundException`](NotFoundException.md)

  No entry was found for this identifier.

* [`ContainerException`](ConfigurationException.md)

  Error while retrieving the entry.

### Returns
* `mixed`

  The entry.

## has
Checks if an entry exists.
```php
public function has(string $id): bool
```
If the container is acting as a composite container (it has children), this 
method will check for a matching entry in itself first, then in its children.

### Parameters
#### id
* `string` **$id**

  Entry to check for.

### Throws
Nothing is thrown.

### Returns
* `bool`

  `true` if the entry exists, `false` otherwise.

## offsetExists
Checks if an entry (offset) exists.
```php
public function offsetExists($offset): bool
```
[`ArrayAccess`](http://php.net/manual/en/class.arrayaccess.php) method executed 
when using [`isset()`](http://php.net/manual/en/function.isset.php) and 
[`empty()`](http://php.net/manual/en/function.empty.php) on a `Container` 
object using array syntax.

Calls [`has()`](#has) internally.

### Parameters
#### id
* `mixed` **$offset**

  Offset (entry) to check for. The value will be cast to `string`.

### Throws
Nothing is thrown.

### Returns
* `bool`

  `true` if the offset (entry) exists, `false` otherwise.
  
## offsetGet
Retrieves an offset (entry) from the container.
```php
public function offsetGet($offset)
```
[`ArrayAccess`](http://php.net/manual/en/class.arrayaccess.php) method executed 
when using array syntax on a `Container` object.

Calls [`get()`](#get) internally.

### Parameters
* `mixed` **$offset**

  Offset (entry) to retrieve. The value will be cast to `string`.

### Throws
* [`NotFoundException`](NotFoundException.md)

  No entry was found for this offset.

* [`ContainerException`](ConfigurationException.md)

  Error while retrieving the offset.

### Returns
* `mixed`

  The entry.

## offsetSet
Assigns a value to the specified offset (identifier).
```php
public function offsetSet($offset, $value)
```
[`ArrayAccess`](http://php.net/manual/en/class.arrayaccess.php) method executed 
when using array syntax on a `Container` object.

Calls [`set()`](#set) internally.

### Parameters
* `mixed` **$offset**

  Offset (identifier) to add. The value will be cast to `string`.

* `mixed` **&value**
  
  Entry value.

### Throws
Nothing is thrown.

### Returns
* `void`

## offsetUnset
Unsets (deletes) an offset (entry) from the container.
```php
public function offsetUnset($offset)
```
[`ArrayAccess`](http://php.net/manual/en/class.arrayaccess.php) method executed 
when using [`unset()`](http://php.net/manual/en/function.unset.php) on a 
`Container` object using array syntax.

Calls [`delete()`](#delete) internally.

### Parameters
* `mixed` **$offset**

  Offset (identifier) to unset (delete). The value will be cast to `string`.

### Throws
Nothing is thrown.

### Returns
* `void`

## set
Assign a value to the specified identifier.
```php
public function set(string $id, $value): Container
```

### Parameters
* `string` **$id**

  Identifier to assign.

* `mixed` **&value**
  
  Entry value.

### Throws
Nothing is thrown.

### Returns
* `Container` **$this**

  [fluent interface](https://en.wikipedia.org/wiki/Fluent_interface), allowing 
  multiple calls to be chained.

## setDelegate
Sets a container to delegate dependency resolution to.
```php
public function setDelegate(ContainerInterface $delegate): Container
```

### Parameters
* [`ContainerInterface`](http://www.php-fig.org/psr/psr-11/#21-psrcontainercontainerinterface) **$delegate**

  Delegate container.

### Throws
Nothing is thrown.

### Returns
* `Container` **$this**

  [fluent interface](https://en.wikipedia.org/wiki/Fluent_interface), allowing 
  multiple calls to be chained.

## setSelfId
Sets the entry identifier for the container itself.
```php
public function setSelfId(string $id): Container
```
When instantiated, the container self identifier will be `container`. Use this 
method when a different identifier is required.

### Parameters
* `string` **$id**

  Identifier to assign to the container.

### Throws
* [`ConfigurationException`](ConfigurationException.md)

  Given identifier is an empty string.

### Returns
* `Container` **$this**

  [fluent interface](https://en.wikipedia.org/wiki/Fluent_interface), allowing 
  multiple calls to be chained.