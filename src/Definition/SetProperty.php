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

class SetProperty implements InstanceAction
{
    use ResolveParametersTrait;

    private $name, $value;

    public function __construct(string $propertyName, $value)
    {
        $this->name = $propertyName;
        $this->value = $value;
    }

    public function __invoke($object, ContainerInterface $container)
    {
        if (!property_exists($object, $this->name)) {
            $msg = sprintf(
                'Property %s does not exist on %s class objects',
                $this->name,
                get_class($object)
            );
            throw new ConfigurationException($msg);
        }
        $object->{$this->name} = $this->resolveParameter(
            $this->value, $container
        );
        return $object;
    }
}
