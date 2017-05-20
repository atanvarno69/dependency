# Atanvarno\Dependency functions
+ [`entry()`](#entry)
+ [`factory()`](#factory)
+ [`object()`](#object)
+ [`value()`](#value)

## entry()
Helper for referencing a container entry in a definition.
```php
function entry(string $id): Entry
```

### Parameters
+ `string` **$id**

  Required. Container entry identifier to reference.

### Throws
Nothing is thrown.

### Returns
+ [`Entry`](Entry.md)

  A reference to the container entry.

## factory()
Helper for defining a container entry using a factory function/callable.
```php
function factory(callable $callable, array $parameters = [], bool $register = true): Definition
```

### Parameters
+ `callable` **$callable**

  Required. A callable that returns the desired value.

+ `array` **$parameters**

  Optional. Defaults to `[]`. A list of parameters to pass to the given 
  callable.

+ `bool` **$register**

  Optional. Defaults to `true`. Whether the entry returned should be 
  registered by the container.

### Throws
Nothing is thrown.

### Returns
+ [`Definition`](Definition.md)

  A container definition.

## object()
Helper for defining an object container entry.
```php
function object(string $className, array $parameters = [], bool $register = true): Definition
```

### Parameters
+ `string` **$className**

  Required. The class name of the object to define. Use of the 
  [`::class`](http://php.net/manual/en/language.oop5.constants.php) constant 
  is recommended.

+ `array` **$parameters**

  Optional. Defaults to `[]`. A list of parameters to pass to the given class's 
  constructor.

+ `bool` **$register**

  Optional. Defaults to `true`. Whether the entry returned should be 
  registered by the container.

### Throws
Nothing is thrown.

### Returns
+ [`Definition`](Definition.md)

  A container definition.

## value()
Helper for defining a generic value container entry.
```php
function value($value, bool $register = true): Definition
```

### Parameters
+ `mixed` **$value**

  Required. The value the container should return.

+ `bool` **$register**

  Optional. Defaults to `true`. Whether the value returned should be 
  registered by the container.

### Throws
Nothing is thrown.

### Returns
+ [`Definition`](Definition.md)

  A container definition.
