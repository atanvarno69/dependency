<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Dependency\Definition;

/** SPL use block. */
use Throwable;

/** PSR-11 use block. */
use Psr\Container\ContainerInterface;

/** Package use block. */
use Atanvarno\Dependency\{
    Definition, Exception\RuntimeException
};

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
        callable $callable,
        array $parameters,
        bool $register
    ) {
        $this->callable = $callable;
        $this->parameters = $parameters;
        $this->register = $register;
    }
    
    protected function factoryMethod(ContainerInterface $container)
    {
        $parameters = $this->resolveParameter($this->parameters, $container);
        try {
            $return = call_user_func($this->callable, ...$parameters);
        } catch (Throwable $caught) {
            $msg = sprintf('Encountered error: %s', $caught->getMessage());
            throw new RuntimeException($msg, $caught->getCode(), $caught);
        }
        return $return;
    }
}
