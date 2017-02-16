<?php
/**
 * Definition class file.
 *
 * @package   Atan\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atan\Dependency;

/** SPL use block. */
use InvalidArgumentException;

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
     * are optional.
     *
     * @api
     *
     * @param string $className  Name of the defined class.
     * @param array  $parameters Parameters to pass to the constructor.
     * @param bool   $register   Whether the instance should be registered.
     *
     * @throws InvalidArgumentException Given class name does not exist.
     */
    public function __construct(
        string $className,
        array $parameters = [],
        bool $register = true
    ) {
        if (!class_exists($className)) {
            $msg = "A class named '$className' does not exist";
            throw new InvalidArgumentException($msg);
        }
        $this->className = $className;
        $this->parameters($parameters);
        $this->register($register);
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
    public function method(string $name, array $parameters = []): Definition
    {
        $this->methods[$name] = $parameters;
        return $this;
    }

    /**
     * Add multiple methods to call after class instantiation.
     *
     * Accepts an array, indices are method names and values are parameters
     * to pass.
     *
     * @api
     *
     * @param array $methods Method calls.
     *
     * @return $this Fluent interface.
     */
    public function methods(array $methods): Definition
    {
        foreach ($methods as $name => $parameters) {
            $this->method($name, $parameters);
        }
        return $this;
    }

    /**
     * Add a parameter to pass to the class constructor.
     *
     * To use a container entry as a parameter use its `string` identifier
     * prefixed with ':'.
     *
     * @api
     *
     * @param mixed $parameter Constructor parameter.
     *
     * @return $this Fluent interface.
     */
    public function parameter($parameter): Definition
    {
        $this->parameters[] = $parameter;
        return $this;
    }

    /**
     * Add multiple parameters to pass to the class constructor.
     *
     * To use a container entry as a parameter use its `string` identifier
     * prefixed with ':'.
     *
     * @api
     *
     * @param mixed[] $parameters Constructor parameters.
     *
     * @return $this Fluent interface.
     */
    public function parameters(array $parameters): Definition
    {
        $this->parameters = array_merge($this->parameters, $parameters);
        return $this;
    }

    /**
     * Set the registration flag for the class.
     *
     * @api
     *
     * @param bool $value Register flag.
     *
     * @return $this Fluent interface.
     */
    public function register(bool $value = true): Definition
    {
        $this->register = $value;
        return $this;
    }
}
