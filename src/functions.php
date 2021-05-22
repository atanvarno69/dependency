<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2021 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

declare(strict_types = 1);

namespace Atanvarno\Dependency;

/** Package use block. */
use Atanvarno\Dependency\Definition\{
    Entry,
    FactoryDefinition,
    ObjectDefinition,
    ValueDefinition
};

if (!function_exists('Atanvarno\Dependency\entry')) {

    /**
     * Reference a container entry by its identifier in a definition.
     *
     * @api
     */
    function entry(string $id): Entry
    {
        return new Entry($id);
    }
}

if (!function_exists('Atanvarno\Dependency\factory')) {

    /**
     * Define a container entry using a factory callable.
     *
     * Optionally accepts an array of parameters to pass to the factory. These
     * may be other container entries by using the `entry()` helper function.
     *
     * Optionally set whether the entry should be registered by the container,
     * that is the same instance is returned by each container `get()` call.
     * Defaults to `true`.
     *
     * @api
     */
    function factory(
        callable $callable,
        array $parameters = [],
        bool $register = true
    ): Definition {
        return new FactoryDefinition($callable, $parameters, $register);
    }
}

if (!function_exists('Atanvarno\Dependency\object')) {
    /**
     * Define an object container entry from a class name.
     *
     * Use of the `::class` constant is recommended for the class name.
     *
     * Optionally accepts an array of parameters to pass to the object's
     * constructor. These may be other container entries by using the `entry()`
     * helper function.
     *
     * Optionally set whether the entry should be registered by the container,
     * that is the same instance is returned by each container `get()` call.
     * Defaults to `true`.
     *
     * @api
     *
     * @throws Exception\ConfigurationException Given class does not exist.
     */
    function object(
        string $className,
        array $parameters = [],
        bool $register = true
    ): Definition {
        return new ObjectDefinition($className, $parameters, $register);
    }
}

if (!function_exists('Atanvarno\Dependency\value')) {

    /**
     * Define an arbitrary value container entry.
     *
     * Optionally set whether the entry should be registered by the container,
     * that is the same instance is returned by each container `get()` call.
     * Defaults to `true`.
     *
     * @api
     */
    function value(mixed $value, bool $register = true): Definition
    {
        return new ValueDefinition($value, $register);
    }
}
