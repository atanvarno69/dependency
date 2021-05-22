<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2021 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

declare(strict_types = 1);

namespace Atanvarno\Dependency\Definition;

use Psr\Container\ContainerInterface;
use Atanvarno\Dependency\Exception\ConfigurationException;

/**
 * Public property to set after instantiation.
 *
 * @internal
 */
class SetProperty implements InstanceAction
{
    use ResolveParametersTrait;

    public function __construct(private string $name, private mixed $value)
    {}

    /**
     * @throws ConfigurationException Property does not exist on given object.
     */
    public function __invoke($object, ContainerInterface $container): object
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
