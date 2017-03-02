<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Dependency;

/** SPL use block. */
use InvalidArgumentException;

/**
 * Atanvarno\Dependency\Definition
 *
 * A definition for a lazy loaded `Container` entry.
 *
 * Provides a fluent interface to define a class.
 */
class ClassDefinition implements Definition
{
    /** Trait use block. */
    use DefinitionTrait;

    /**
     * ClassDefinition constructor.
     *
     * A class name must be given. Constructor parameters and register flag
     * for the defined class are optional.
     *
     * @internal For use by `Container::define()`.
     *
     * @param bool   $register      Whether the instance should be registered.
     * @param string $className     Name of the defined class.
     * @param mixed  ...$parameters Parameters to pass to the constructor.
     *
     * @throws InvalidArgumentException The given class name does not exist.
     */
    public function __construct(
        bool $register,
        string $className,
        ...$parameters
    ) {
        if (!class_exists($className)) {
            $msg = "A class named '$className' does not exist";
            throw new InvalidArgumentException($msg);
        }
        $this->register = $register;
        $this->cargo = $className;
        $this->parameters = $parameters;
        $this->methods = [];
    }
}
