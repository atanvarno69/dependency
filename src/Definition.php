<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2021 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

declare(strict_types = 1);

namespace Atanvarno\Dependency;

use Atanvarno\Dependency\Exception\RuntimeException;
use Psr\Container\ContainerInterface;

/**
 * A definition for a lazy loaded `Container` entry.
 *
 * Provides a fluent interface to define multiple post-instantiation actions.
 *
 * @api
 */
interface Definition
{
    /**
     * Build the entry.
     *
     * @internal For use by `Container`.
     *
     * @throws RuntimeException Unable to build.
     */
    public function build(ContainerInterface $container): mixed;

    /**
     * Get the registration flag.
     *
     * @internal For use by `Container`.
     */
    public function isRegistered(): bool;

    /**
     * Add a method to call after object instantiation.
     *
     * Pass the method name and optionally an array of parameters to pass to the
     * method.
     *
     * The parameters array may contain raw values or references to other
     * container entries, using the `entry()` helper function.
     *
     * Note: if the definition does not define an object, adding a method to
     * call will do nothing.
     */
    public function method(string $name, array $parameters = []): static;
    
    /**
     * Set a public property after object instantiation.
     *
     * Pass the property name and its value.
     *
     * Note: if the definition does not define an object, setting a property
     * will do nothing.
     */
    public function property(string $name, mixed $value = null): static;
}
