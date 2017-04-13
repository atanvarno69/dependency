<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Dependency\Definition;

/** Package use block. */
use Atanvarno\Dependency\{
    Definition,
    Exception\ContainerException
};

class AbstractDefinition implements Definition
{
    private $actions, $register;
    
    public function __construct(bool $register)
    {
        $this->actions = [];
        $this->register = $register;
    }
    
    public function build(ContainerInterface $container)
    {
        $return = $this->factoryMethod($container);
        if (!is_object($return)) {
            return $return;
        }
        foreach ($this->actions as $action) {
            $parameters = $this->resolveParameter($action[2]);
            if ($action[0] === 'method') {
                $return = $this->resolveCallMethod(
                    $return, $action[1], $parameters
                );
            }
            if ($action[0] === 'property') {
                $return = $this->resolveSetProperty(
                    $return, $action[1], $parameters
                );
            }
        }
        return $return;
    }
    
    public function isRegistered(): bool
    {
        return $this->register;
    }
    
    public function method(string $name, array $parameters = []): Definition
    {
        $this->actions[] = ['method', $name, $parameters];
        return $this;
    }
    
    public function property(string $name, $value = null): Definition
    {
        $this->actions[] = ['property', $name, $value];
        return $this;
    }
    
    abstract protected function factoryMethod(ContainerInterface $container);
    
    protected function resolveParameter($parameter, ContainerInterface $container)
    {
        $return = $parameter;
        if ($parameter instanceof Entry) {
            $return = $container->get((string) $parameter);
        }
        if (is_array($parameter)) {
            $return = [];
            foreach ($parameter as $key => $value) {
                $return[$key] = $this->resolveParameter($value, $container);
            }
        }
        return $return;
    }
    
    private function resolveCallMethod(
        $object, string $methodName, array $parameters = []
    ) {
        if (!method_exists($object, $methodName)) {
            $msg = sprintf(
                'Method %s does not exist on %s class objects',
                $methodName,
                get_class($object)
            );
            throw new ContainerException($msg);
        }
        $object->{$methodName}(...$parameters);
        return $object;
    }
    
    private function resolveSetProperty($object, string $propertyName, $value)
    {
        if (!property_exists($object, $methodName)) {
            $msg = sprintf(
                'Property %s does not exist on %s class objects',
                $propertyName,
                get_class($object)
            );
            throw new ContainerException($msg);
        }
        $object->${$propertyName} = $value;
        return $object;
    }
}
