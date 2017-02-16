# Atan\Dependency\Container
A basic container implementing [PSR-11 `ContainerInterface`](http://www.php-fig.org/psr/psr-11/#21-psrcontainercontainerinterface).
```php
class Container implements ArrayAccess, ContainerInterface {

    /* Methods */
    public __construct  ( [ array $entries = [] [, string $id =  'container' ] ] )
    public void  add    ( string $id, mixed $value )
    public void  delete ( string $id )
    public mixed get    ( string $id )
    public bool  has    ( string $id )
}
```
The container may contain and return any PHP type. These container entries are associated with a unique user-defined `string` identifier. All entries, except defined classes, are registered, that is a call to [`get()`](#get) with the identifier will always return the same value.

Defined classes are added using an instance of the `Definition` class. Defined classes are registered by default, so that after the first call to [`get()`](#get), when the object is lazy loaded, [`get()`](#get) will always return the same instance. If a new instance is required from each call to [`get()`](#get), that must be explicitly indicated in the [`Definition`](Definition.md) class.

As the class implements [`ArrayAccess`](http://php.net/manual/en/class.arrayaccess.php), it can be used with array syntax.

* [__construct](#__construct)
* [add](#add)
* [delete](#delete)
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
