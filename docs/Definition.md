# Atan\Dependency\Definition
A definition for a lazy loaded [`Container`](Container.md) entry.
```php
class Definition {

    /* Methods */
    public __construct           ( string $className [, array $parameters = [] [, bool $register = true ] ] ] )
    public Definition method     ( string $name [, array $parameters = [] ] )
    public Definition methods    ( array $methods )
    public Definition parameter  ( mixed $parameter )
    public Definition parameters ( array $parameters )
    public Definition register   ( [ bool $register = true ] )
}
```
Provides a [fluent interface](https://en.wikipedia.org/wiki/Fluent_interface) to define a class.

* [__construct](#__construct)
* [method](#method)
* [methods](#methods)
* [parameter](#parameter)
* [parameters](#parameters)
* [register](#register)

## __construct
```php
public __construct ( string $className [, array $parameters = [] [, bool $register = true ] ] ] )
```
### Parameters
#### className
Name of the defined class. Using the [`::class` keyword](http://php.net/manual/en/language.oop5.basic.php#language.oop5.basic.class.class) is recommended. 

#### parameters
Optional. Defaults to `[]`.

An `array` of parameter values to pass to the defined class's constructor. To use an entry defined in the container, use its `string` identifier value prefixed with `:`.

Parameter values can instead be set using the [`parameter()`](#parameter) or [`parameters()`](#parameter) methods.

#### register
Optional. Defaults to `true`.

`true` marks that the container should continue to return the same instance of the defined class each time [`Container::get()`](Container.md#get) is called. `false` indicates that a fresh instance should be returned on each call.

The registration value can instead be set using the [`register()`](#register) method.

### Throws
#### `InvalidArgumentException`
The given class name is not valid.

### Returns
A `Container` instance.