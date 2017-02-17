# Atan\Dependency\Definition
A definition for a lazy loaded [`Container`](Container.md) entry.
```php
class Definition {

    /* Methods */
    public Definition method ( string $name [, array $parameters = [] ] )
}
```
Provides a [fluent interface](https://en.wikipedia.org/wiki/Fluent_interface) to define a class.

* [method](#method)

## method
Add a method to call after class instantiation.
```php
public Definition method ( string $name [, array $parameters = [] ] )
```
### Parameters
#### name
Name of the method to call.

#### parameters
Optional. Defaults to `[]`.

An `array` of parameter values to pass to the method. To use an entry defined in the container, use [`Container::entry()`](Container.md#entry).

Parameter values can instead be set using the [`parameter()`](#parameter) or [`parameters()`](#parameter) methods.

### Throws
Nothing is thrown.

### Returns
The `Definition` instance. This allows multiple calls to be chained.
