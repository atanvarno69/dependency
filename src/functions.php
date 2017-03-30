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
     * @param string $id Container entry identifier.
     *
     * @return Entry
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
     * @param callable $callable
     * @param array    $parameters
     * @param bool     $register
     *
     * @return FactoryDefinition
     */
    function factory(
        callable $callable,
        array $parameters = [],
        bool $register = true
    ): FactoryDefinition {
        return new FactoryDefinition($callable, $parameters, $register);
    }
}

if (!function_exists('Atanvarno\Dependency\object')) {
    /**
     * Helper for defining an object container entry.
     *
     * @param string $className
     * @param array  $parameters
     * @param bool   $register
     *
     * @return ObjectDefinition
     */
    function object(
        string $className,
        array $parameters = [],
        bool $register = true
    ): ObjectDefinition {
        return new ObjectDefinition($className, $parameters, $register);
    }
}

if (!function_exists('Atanvarno\Dependency\value')) {
    /**
     * Helper for defining a generic value container entry.
     *
     * @param mixed $value
     * @param bool  $register
     *
     * @return ValueDefinition
     */
    function object($value, bool $register = true): ValueDefinition
    {
        return new ValueDefinition($value, $register);
    }
}
