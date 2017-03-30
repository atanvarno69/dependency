<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Dependency;

/**
 * Atanvarno\Dependency\Definition
 *
 * A definition for a lazy loaded `Container` entry.
 *
 * Provides a fluent interface to define multiple post-instantiation method
 * calls.
 */
interface Definition
{
    /**
     * Builds the entry
     *
     * @internal For use by `Container`.
     *
     * @param ContainerInterface $container Container to resolve dependencies.
     *
     * @return mixed The entry.
     */
    public function build(ContainerInterface $container);

    /**
     * Get the registration flag.
     *
     * @internal For use by `Container`.
     *
     * @return bool Registration flag.
     */
    public function isRegistered(): bool;

    /**
     * Adds a method to call after object instantiation.
     *
     * @api
     *
     * @param string $name       Method name to call.
     * @param array  $parameters Parameters to pass to the method. To use an
     *      entry defined in the container, use `Container::entry()`.
     *
     * @return $this Fluent interface, allowing multiple calls to be chained.
     */
    public function method(string $name, array $parameters = []): Definition;
    
    /**
     * Sets a property after object instantiation.
     *
     * @api
     *
     * @param string $name  Property name to set.
     * @param mixed  $value Value to set.
     *
     * @return $this Fluent interface, allowing multiple calls to be chained.
     */
    public function property(string $name, $value = null): Definition;
}
