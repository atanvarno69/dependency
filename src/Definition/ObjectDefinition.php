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

class ObjectDefinition extends AbstractDefinition
{
    private $className, $parameters;
    
    public function __construct(
        string $className,
        array $parameters,
        bool $register
    ) {
        if (!class_exists($className)) {
            $msg = sprintf('%s is not a valid class name', $className);
            throw new ContainerException($msg);
        }
        $this->className = $className;
        $this->parameters = $parameters;
        parent::__construct($register);
    }
    
    protected function factoryMethod(ContainerInterface $container)
    {
        $parameters = $this->resolveParameter($this->parameters);
        return new $this->className(...$parameters);
    }
}
