<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Dependency;

/**
 * Atanvarno\Dependency\DefinitionTrait
 *
 * @internal For use implementing `Definition`.
 */
trait DefinitionTrait
{
    /**
     * @var mixed   $cargo      Definition cargo class name or callable.
     * @var mixed[] $methods    Methods to call after instantiation.
     * @var mixed[] $parameters Constructor parameters for defined class.
     * @var bool    $register   Whether an instance should be registered.
     */
    private $cargo, $methods, $parameters, $register;

    /**
     * Get the definition cargo.
     *
     * @internal For use by `Container`.
     *
     * @return callable|string Definition callable or class name.
     */
    public function getCargo()
    {
        return $this->cargo;
    }

    /**
     * Get the methods to call on the object after instantiation.
     *
     * @internal For use by `Container`.
     *
     * @return array Parameters.
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * Get the parameters to pass.
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
    public function method(string $name, ...$parameters): Definition
    {
        $parameters = $parameters ?? [];
        $this->methods[$name] = $parameters;
        return $this;
    }
}
