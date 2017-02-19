# Atan\Dependency\Definition
A definition for a lazy loaded [`Container`](Container.md) entry.
```php
class Definition
{
    // Methods
    public function method(string $name, array $parameters = []): Definition
}
```
Provides a [fluent interface](https://en.wikipedia.org/wiki/Fluent_interface) to define a class.

* [method](#method)

## method
Adds a method to call after class instantiation.
```php
method(string $name, array $parameters = []): Definition
```
### Parameters
* `string` **$name**

  Method name to call.

* `array` **$parameters**

  Optional. Defaults to `[]`. Parameters to pass to the method. To use an 
  entry defined in the container, use [`Container::entry()`](Container.md#entry).

### Throws
Nothing is thrown.

### Returns
* `Definition` **$this**

  Fluent interface, allowing multiple calls to be chained.
