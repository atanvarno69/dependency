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
use Atanvarno\Dependency\{
    Definition, Exception\ConfigurationException
};

class ObjectDefinition implements Definition
{
    use DefinitionTrait;

    private $className, $parameters;
    
    public function __construct(
        string $className,
        array $parameters,
        bool $register
    ) {
        if (!class_exists($className)) {
            $msg = sprintf('%s is not a valid class name', $className);
            throw new ConfigurationException($msg);
        }
        $this->className = $className;
        $this->parameters = $parameters;
        $this->register = $register;
    }
    
    protected function factoryMethod(ContainerInterface $container)
    {
        $parameters = $this->resolveParameter($this->parameters, $container);
        return new $this->className(...$parameters);
    }
}
