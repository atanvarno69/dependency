<?php
/**
 * @package   Atan\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atan\Dependency;

/** SPL use block. */
use InvalidArgumentException;

/**
 * Atan\Dependency\Definition
 *
 * A definition for a lazy loaded `Container` entry.
 *
 * Provides a fluent interface to define a class.
 */
class Definition
{
    /**
     * @var string  $className  Name of defined class.
     * @var mixed[] $methods    Methods to call after instantiation.
     * @var mixed[] $parameters Constructor parameters for defined class.
     * @var bool    $register   Whether an instance should be registered.
     */
    private $className, $methods, $parameters, $register;

    /**
     * Definition constructor.
     *
     * A class name must be given. Constructor parameters and register flag
     * for the defined class are optional.
     *
     * @internal For use by `Container::class()` and `Container::factory()`.
     *
     * @param string $className  Name of the defined class.
     * @param array  $parameters Parameters to pass to the constructor.
     * @param bool   $register   Whether the instance should be registered.
     */
    public function __construct(
        bool $register,
        string $className,
        array $parameters = []
    ) {
        $this->register = $register;
        $this->className = $className;
        $this->parameters = $parameters;
        $this->methods = [];
    }

    /**
     * Get the defined class's name.
     *
     * @internal For use by `Container`.
     *
     * @return string Defined class's name.
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * Get the methods to call on the defined class after instantiation.
     *
     * @internal For use by `Container`.
     *
     * @return array Method name indexed parameters.
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * Get the parameters to pass to the defined class's constructor.
     *
     * @internal For use by `Container`.
     *
     * @return array Constructor parameters.
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Get the registration flag.
     *
     * @internal For use by `Container`.
     *
     * @return bool Registration flag.
     */
    public function getRegister(): bool
    {
        return $this->register;
    }

    /**
     * Add a method to call after class instantiation.
     *
     * @api
     *
     * @param string $name       Method name to call.
     * @param array  $parameters Parameters to pass to the method.
     *
     * @return $this Fluent interface.
     */
    public function method(string $name, ...$parameters): Definition
    {
        $parameters = $parameters ?? [];
        $this->methods[$name] = $parameters;
        return $this;
    }
}
