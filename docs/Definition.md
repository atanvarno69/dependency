# Atanvarno\Dependency\Definition
A definition for a lazy loaded [`Container`](Container.md) entry.
```php
interface Definition
{
    // Methods
    public function method(string $name, array $parameters = []): Definition
}
```
Provides a [fluent interface](https://en.wikipedia.org/wiki/Fluent_interface) 
to define multiple post-instantiation actions.

* [method](#method)
* [property](#property)

## method
Adds a method to call after object instantiation.
```php
method(string $name, array $parameters = []): Definition
```

Note if the definition does not define an object, adding a method to call 
will do nothing.

### Parameters
* `string` **$name**

  Method name to call.

* `array` **$parameters**

  Optional. Defaults to `[]`. A list of parameters to pass to the method.

### Throws
Nothing is thrown.

### Returns
* `Definition` **$this**

  Fluent interface, allowing multiple calls to be chained.

## property
Sets a public property after object instantiation.
```php
public function property(string $name, $value = null): Definition
```
Note if the definition does not define an object, setting a property will do 
nothing.

### Parameters
* `string` **$name**

  Property name to set.

* `mixed` **$value**

  Optional. Defaults to `null`. Value to set.

### Throws
Nothing is thrown.

### Returns
* `Definition` **$this**

  Fluent interface, allowing multiple calls to be chained.
