# Atan\Dependency\Container
A basic container implementing [PSR-11 `ContainerInterface`](http://www.php-fig.org/psr/psr-11/#21-psrcontainercontainerinterface).
```php
class Container implements ArrayAccess, ContainerInterface {

    /* Methods */
    public __construct        ( [ array $entries = [] [, string $id =  'container' ] ] )
    public void       add     ( string $id, mixed $value )
    public Definition class   ( string $className, ...$parameters )
    public void       delete  ( string $id )
    public EntryProxy entry   ( string $id )
    public Definition factory ( string $className, ...$parameters )
    public mixed      get     ( string $id )
    public bool       has     ( string $id )
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
Note that unlike a normal array, only `string` identifiers will be accepted by the array syntax (as [PSR-11](http://www.php-fig.org/psr/psr-11/#21-psrcontainercontainerinterface) only permits `string` identifiers); using `int` (or other) identifer types with array syntax will silently fail.

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
public __construct ( [ array $entries = [] [, string $id =  'container' ] ] )
```
### Parameters
#### entries
Optional. Defaults to an empty array.

An `array` of `mixed` entry values indexed by `string` identifiers. See [`add()`](#add).

#### id
Optional. Defaults to `string 'container'`.

An identifier for the container to retreive itself via [`get()`](#get).

### Throws
Nothing is thrown.

### Returns
A `Container` instance.

## add
Adds an entry to the container.
```php
public void add ( string $id, mixed $value )
```
### Parameters
#### id
A `string` identifier for the entry.

#### value
A `mixed` value for the entry. To define a lazy loaded class, give an instance of the [`Definition`](Definition.md) class.

### Throws
Nothing is thrown.

### Returns
`void`

## class
Adds a registered class definition for lazy loading.
```php
public Definition class ( string $className, mixed ...$parameters )
```
After first instantiation, the same instance will be returned by [`get()`](#get) on each call. If this is not the desired behaviour, you should use [`factory()`](#factory).

### Parameters
#### className
The `string` name of the class to register. Using the [`::class` keyword](http://php.net/manual/en/language.oop5.basic.php#language.oop5.basic.class.class) is recommended.

#### parameters
An `array` of values to pass to the defined class's constructor. To pass a container entry, use [`entry()`](#entry).

### Throws
`InvalidArgumentException` if the given class name does not exist.

### Returns
A [`Definition`](Definition.md) instance.

## delete
Deletes an entry from the container.
```php
public void delete ( string $id )
```
### Parameters
#### id
The `string` identifier of the entry to delete.

### Throws
Nothing is thrown.

### Returns
`void`

## entry
Use a container entry as a parameter.
```php
public EntryProxy entry ( string $id )
```
### Parameters
#### id
The `string` identifier of the entry to reference.

### Throws
Nothing is thrown.

### Returns
An `EntryProxy` instance. When an `EntryProxy` is encountered in a parameter list while resolving a definition it is replaced with the container entry with the given identifier.

## factory
Adds a registered class definition for lazy loading.
```php
public Definition class ( string $className, mixed ...$parameters )
```
A new instance will be returned by [`get()`](#get) on each call. If this is not the desired behaviour, you should use [`class()`](#class).

### Parameters
#### className
The `string` name of the class to register. Using the [`::class` keyword](http://php.net/manual/en/language.oop5.basic.php#language.oop5.basic.class.class) is recommended.

#### parameters
An `array` of values to pass to the defined class's constructor. To pass a container entry, use [`entry()`](#entry).

### Throws
`InvalidArgumentException` if the given class name does not exist.

### Returns
A [`Definition`](Definition.md) instance.

## get
Finds an entry of the container by its identifier and returns it.
```php
public mixed get( string $id )
```
### Parameters
#### id
The `string` identifier of the entry to look for.

### Throws
#### `NotFoundExceptionInterface`
No entry was found for this identifier.
#### `ContainerExceptionInterface`
Error while retrieving the entry.

### Returns
The `mixed` entry.

## has
Checks whether the container can return an entry for the given identifier.
```php
public bool has( string $id )
```
`has($id)` returning true does not mean that [`get($id)`](#get) will not throw an exception. It does however mean that [`get($id)`](#get) will not throw a `NotFoundExceptionInterface`.
### Parameters
#### id
The `string` identifier of the entry to look for.

### Throws
Nothing is thrown.

### Returns
`true` if the container can return an entry for the given identifier. `false` otherwise.
