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

class FactoryDefinition extends AbstractDefinition
{
    private $callable, $parameters;
    
    public function __construct(
        callable $callable,
        array $parameters,
        bool $register
    ) {
        $this->callable = $callable;
        $this->parameters = $parameters;
        parent::__construct($register);
    }
    
    protected function factoryMethod(ContainerInterface $container)
    {
        $parameters = $this->resolveParameter($this->parameters);
        return call_user_func($this->callable, ...$parameters);
    }
}
