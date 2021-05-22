<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2021 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

declare(strict_types = 1);

namespace Atanvarno\Dependency\Definition;

use Psr\Container\ContainerInterface as Container;
use Atanvarno\Dependency\Exception\ConfigurationException;

/**
 * Method call after instantiation.
 *
 * @internal
 */
class CallMethod implements InstanceAction
{
    use ResolveParametersTrait;

    public function __construct(private string $name, private array $parameters)
    {}

    /**
     * @throws ConfigurationException Method does not exist on given object.
     */
    public function __invoke(object $object, Container $container): object
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
