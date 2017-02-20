<?php
/**
 * @package   Atan\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atan\Dependency;

/**
 * Atan\Dependency\Definition
 *
 * A definition for a lazy loaded `Container` entry.
 *
 * Provides a fluent interface to define multiple post-instantiation method
 * calls.
 */
interface Definition
{
    /**
     * Get the definition cargo.
     *
     * @internal For use by `Container`.
     *
     * @return callable|string Definition callable or class name.
     */
    public function getCargo();

    /**
     * Get the methods to call on the object after instantiation.
     *
     * @internal For use by `Container`.
     *
     * @return array Parameters.
     */
    public function getMethods(): array;

    /**
     * Get the parameters to pass.
     *
     * @internal For use by `Container`.
     *
     * @return array Constructor parameters.
     */
    public function getParameters(): array;

    /**
     * Get the registration flag.
     *
     * @internal For use by `Container`.
     *
     * @return bool Registration flag.
     */
    public function getRegister(): bool;

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
    public function method(string $name, ...$parameters): Definition;
}