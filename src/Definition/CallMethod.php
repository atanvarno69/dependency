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
use Atanvarno\Dependency\Exception\ConfigurationException;

/** @internal */
class CallMethod implements InstanceAction
{
    use ResolveParametersTrait;

    private $name, $parameters;

    public function __construct(string $methodName, array $parameters)
    {
        $this->name = $methodName;
        $this->parameters = $parameters;
    }

    public function __invoke($object, ContainerInterface $container)
    {
        if (!method_exists($object, $this->name)) {
            $msg = sprintf(
                'Method %s does not exist on %s class objects',
                $this->name,
                get_class($object)
            );
            throw new ConfigurationException($msg);
        }
        $parameters = $this->resolveParameter($this->parameters, $container);
        $object->{$this->name}(...$parameters);
        return $object;
    }
}
