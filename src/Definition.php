<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Dependency;

/** PSR-11 use block. */
use Psr\Container\ContainerInterface;

/**
 * Atanvarno\Dependency\Definition
 *
 * A definition for a lazy loaded `Container` entry.
 *
 * Provides a fluent interface to define multiple post-instantiation actions.
 *
 * @api
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
     * Note if the definition does not define an object, adding a method to
     * call will do nothing.
     *
     * @param string $name       Method name to call.
     * @param array  $parameters A list of parameters to pass to the method.
     *
     * @return $this Fluent interface, allowing multiple calls to be chained.
     */
    public function method(string $name, array $parameters = []): Definition;
    
    /**
     * Sets a public property after object instantiation.
     *
     * Note if the definition does not define an object, setting a property
     * will do nothing.
     *
     * @param string $name  Property name to set.
     * @param mixed  $value Value to set.
     *
     * @return $this Fluent interface, allowing multiple calls to be chained.
     */
    public function property(string $name, $value = null): Definition;
}
