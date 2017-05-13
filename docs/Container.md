# Atanvarno\Dependency\Container
A basic container implementing 
[PSR-11 `ContainerInterface`](http://www.php-fig.org/psr/psr-11/#21-psrcontainercontainerinterface).
```php
class Container implements ArrayAccess, ContainerInterface
{
    public function __construct(array $definitions = [], $cache = null, string $cacheKey = 'container')
    public function addChild(ContainerInterface $child): Container
    public function clearCache(): Container
    public function delete(string $id): Container
    public function get(string $id)
    public function has(string $id): bool
    public function offsetExists($offset): bool
    public function offsetGet($offset)
    public function offsetSet($offset, $value)
    public function offsetUnset($offset)
    public function set(string $id, $value): Container
    public function setDelegate(ContainerInterface $delegate): Container
    public function setSelfId(string $id): Container
}
```
The container may contain and return any PHP type. These container entries 
are associated with a unique user-defined `string` identifier.

By default, a `Container` instance will associate itself with the identifier 
`container`. Use the method [`setSelfId()`](#setSelfId) to change this value.

`Container` implements [PSR-11 `ContainerInterface`](http://www.php-fig.org/psr/psr-11/#21-psrcontainercontainerinterface) 
and thus uses the method [`get()`](#get) is used to retrieve an entry and the 
method [`has()`](#has) is used to check if an entry exists.

Entries are added using the [`set()`](#set) method. This accepts any value. To 
define an entry that will be lazy loaded (only instantiated when 
[`get()`](#get) is first called), pass [`set()`](#set) a 
[`Definition`](#Definition.md) instance. The helper functions 
[`factory()`](Functions.md#factory), [`object()`](Functions.md#object) and 
[`value()`](Functions.md#value) can be used to 
provide a [`Definition`](#Definition.md) instance for [`set()`](#set).

Entries are removed using the [`delete()`](#delete) method.

`Container` implements the 
[Delegate Lookup Feature](https://github.com/container-interop/container-interop/blob/master/docs/Delegate-lookup.md). 
To use a `Container` instance as a composite container, use the 
[`addChild()`](#addChild) method. To use a `Container` instance as a child 
container, add it to the composite container and use the 
[`setDelegate()`](#setDelegate) method to register the composite container for 
dependency resolution. 

As `Container` implements 
[`ArrayAccess`](http://php.net/manual/en/class.arrayaccess.php), it can be used 
with array syntax:
```php
# Array syntax              # Alias of
$container['ID'] = $value;  $container->add('ID', $value);
$item = $container['ID'];   $item = $container->get('ID');
isset($container['ID']);    $container->has('ID');
unset($container['ID']);    $container->delete('ID');
```
Unlike a normal array, non-`string` offsets will be accepted by the array 
syntax. However, as [PSR-11](http://www.php-fig.org/psr/psr-11) only permits 
`string` identifiers, `int` (or other) offset types used with array syntax will 
be silently cast to `string`.

`Container` can cache its contained entries. To use caching, provide a 
[PSR-16 `CacheInterface`](http://www.php-fig.org/psr/psr-16/#cacheinterface) 
instance to the constructor, optionally with a key to use for its cache entry.

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
Builds a `Container` instance.
```php
public function __construct(array $definitions = [], $cache = null, string $cacheKey = 'container')
```
Optionally accepts an array of [`Definition`](#Definition.md) instances indexed 
by entry identifiers. These will be added to the container. This array can be 
returned from a configuration file.

Optionally accepts a 
[PSR-16 `CacheInterface`](http://www.php-fig.org/psr/psr-16/#cacheinterface) 
instance; or an `Entry` instance that refers to a PSR-16 cache instance. Thus, 
a cache can be gotten from the provided definitions array. If this is the case, 
the container will be updated with the values from the cache.

Optionally accepts a cache key to store the container's data.

### Parameters
* [`Definition`](#Definition.md)`[]` **$definitions**

  Optional. Defaults to `[]`. Entry definitions indexed by identifiers.
  
* `mixed` **$cache**

  Optional. Defaults to `null`. [PSR-16](http://www.php-fig.org/psr/psr-16/) 
  cache.

* `string` **$cacheKey**

  Optional. Defaults to `container`. Cache key for cached data.

### Throws
* [`ConfigurationException`](ConfigurationException.md)

  Definitions array does not contain only [`Definition`](Definition.md) 
  instances.
  
* [`InvalidArgumentException`](InvalidArgumentException.md)

  Given cache is not a valid type.
  
* [`InvalidArgumentException`](InvalidArgumentException.md)

  Given cache key is an empty string.
  
* [`RuntimeException`](RuntimeException.md)

  Error building cache instance.
  
* [`RuntimeException`](RuntimeException.md)

  Error getting data from cache.
  
* [`UnexpectedValueException`](UnexpectedValueException.md)

  Built cache instance is not a [PSR-16](http://www.php-fig.org/psr/psr-16/) 
  cache.
  
* [`UnexpectedValueException`](UnexpectedValueException.md)

  Invalid data returned from cache.

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

  Required. Child container to add.

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
* [`RuntimeException`](RuntimeException.md)

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
* [`InvalidArgumentException`](InvalidArgumentException.md)

  Given identifier is an empty string.

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

  Required. Entry to retrieve.

### Throws
* [`InvalidArgumentException`](InvalidArgumentException.md)

  Given identifier is an empty string.

* [`NotFoundException`](NotFoundException.md)

  No entry was found for this identifier.

* [`RuntimeException`](RuntimeException.md)

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

  Required. Entry to check for.

### Throws
* [`InvalidArgumentException`](InvalidArgumentException.md)

  Given identifier is an empty string.

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

  Required. Offset (entry) to check for. The value will be cast to `string`.

### Throws
* [`InvalidArgumentException`](InvalidArgumentException.md)

  Given offset resolves to an empty string.

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

  Required. Offset (entry) to retrieve. The value will be cast to `string`.

### Throws
* [`InvalidArgumentException`](InvalidArgumentException.md)

  Given offset resolves to an empty string.

* [`NotFoundException`](NotFoundException.md)

  No entry was found for this offset.

* [`RuntimeException`](RuntimeException.md)

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

  Required. Offset (identifier) to add. The value will be cast to `string`.

* `mixed` **&value**
  
  Required. Entry value.

### Throws
* [`InvalidArgumentException`](InvalidArgumentException.md)

  Given offset resolves to an empty string.

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

  Required. Offset (identifier) to unset (delete). The value will be cast to 
  `string`.

### Throws
* [`InvalidArgumentException`](InvalidArgumentException.md)

  Given offset resolves to an empty string.

### Returns
* `void`

## set
Assign a value to the specified identifier.
```php
public function set(string $id, $value): Container
```

### Parameters
* `string` **$id**

  Required. Identifier to assign.

* `mixed` **&value**
  
  Required. Entry value.

### Throws
* [`InvalidArgumentException`](InvalidArgumentException.md)

  Given identifier is an empty string.

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

  Required. Delegate container.

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

  Required. Identifier to assign to the container itself.

### Throws
* [`InvalidArgumentException`](InvalidArgumentException.md)

  Given identifier is an empty string.

### Returns
* `Container` **$this**

  [fluent interface](https://en.wikipedia.org/wiki/Fluent_interface), allowing 
  multiple calls to be chained.