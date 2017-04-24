<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Dependency\Definition;

/** PSR-11 use block. */
use Psr\Container\ContainerInterface;

/** Package use block. */
use Atanvarno\Dependency\Definition;

/**
 * Atanvarno\Dependency\Definition\FactoryDefinition
 *
 * Definition for a container entry loaded by calling a use-defined callable.
 */
class FactoryDefinition implements Definition
{
    /** Trait use block. */
    use DefinitionTrait;

    /**
     * @internal Class properties.
     *
     * @var callable $callable
     * @var mixed[]  $parameters
     */
    private $callable, $parameters;
    
    public function __construct(
        $callable,
        array $parameters,
        bool $register
    ) {
        $this->callable = $callable;
        $this->parameters = $parameters;
        $this->register = $register;
    }
    
    protected function factoryMethod(ContainerInterface $container)
    {
        $callable = (is_array($this->callable))
            ? $this->resolveParameter($this->callable, $container)
            : $this->callable;
        $parameters = $this->resolveParameter($this->parameters, $container);
        return call_user_func($callable, ...$parameters);
    }
}
