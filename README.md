# Atan\Dependency
[PSR-11](http://www.php-fig.org/psr/psr-11/) dependency injection container.

## Requirements
**PHP >= 7.0** is required, but the latest stable version of PHP is recommended.

## Installation
Add the following to your `composer.json` file:
```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/atanvarno69/dependency.git"
        }
    ],
    "require": {
        "atan/dependency": "^0.3.0"
    }
}
```
Then:
```bash
$ composer install
# or
$ composer update
```

## Basic Usage
```php
use Atan\Dependency\{
    Container, Definition
};

$container = new Container();

// Define a class to be lazy loaded:
$container->add('ID', new Definition(ClassName::class, ['Constructor', 'Params', ':OtherID']));

// Register another value:
$value = ['some', 'array', 10, new ClassName()];
$container->add('OtherID', $value);

// Get an entry
$entry = $container->get('ID');
```
`Container` marks all entries as registered (the same entity will always be returned) upon first instantiation, unless told otherwise in a `Definition`.

## Full API
See [API](https://github.com/atanvarno69/dependency/blob/master/docs/API.md).
