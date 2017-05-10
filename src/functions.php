<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

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
     * Helper for referencing a container entry in a definition.
     *
     * @api
     *
     * @param string $id Container entry identifier to reference.
     *
     * @return Entry A reference to the container entry.
     */
    function entry(string $id): Entry
    {
        return new Entry($id);
    }
}

if (!function_exists('Atanvarno\Dependency\factory')) {

    /**
     * Helper for defining a container entry using a factory function/callable.
     *
     * @api
     *
     * @param callable $callable   A callable that returns the desired value.
     * @param array    $parameters A list of parameters to pass to the given
     *      callable.
     * @param bool     $register   Whether the entry returned should be
     *      registered by the container.
     *
     * @return Definition A container definition.
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
     * Helper for defining an object container entry.
     *
     * @api
     *
     * @param string $className  The class name of the object to define. Use of
     *      the`::class` constant is recommended.
     * @param array  $parameters A list of parameters to pass to the given
     *      class's constructor.
     * @param bool   $register   Whether the entry returned should be
     *      registered by the container.
     *
     * @return Definition A container definition.
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
     * Helper for defining a generic value container entry.
     *
     * @api
     *
     * @param mixed $value    The value the container should return.
     * @param bool  $register Whether the value returned should be
     *      registered by the container.
     *
     * @return Definition A container definition.
     */
    function value($value, bool $register = true): Definition
    {
        return new ValueDefinition($value, $register);
    }
}
