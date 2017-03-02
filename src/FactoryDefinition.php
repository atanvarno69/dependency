<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Dependency;

/**
 * Atanvarno\Dependency\FactoryDefinition
 */
class FactoryDefinition implements Definition
{
    /** Trait use block. */
    use DefinitionTrait;

    /**
     * FactoryDefinition constructor.
     *
     * @internal For use by `Container::factory()`.
     *
     * @param bool     $register      Whether the instance should be registered.
     * @param callable $callable      Callable to use.
     * @param array    ...$parameters Parameters to pass to the callable.
     */
    public function __construct(
        bool $register,
        callable $callable,
        ...$parameters
    ) {
        $this->register = $register;
        $this->cargo = $callable;
        $this->parameters = $parameters;
        $this->methods = [];
    }
}
