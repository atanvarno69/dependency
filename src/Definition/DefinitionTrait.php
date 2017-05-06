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
 * Atanvarno\Dependency\Definition\DefinitionTrait
 *
 * Trait providing `Definition` implementation functionality.
 */
trait DefinitionTrait
{
    /** Trait use block. */
    use ResolveParametersTrait;

    /**
     * @internal Class properties.
     *
     * @var InstanceAction[] $actions
     * @var bool             $register
     */
    private $actions, $register;

    /** @internal */
    public function build(ContainerInterface $container)
    {
        $return = $this->factoryMethod($container);
        if (!is_object($return)) {
            return $return;
        }
        if (!empty($this->actions)) {
            /** @var InstanceAction $action */
            foreach ($this->actions as $action) {
                $return = $action($return, $container);
            }
        }
        return $return;
    }

    /** @internal */
    public function isRegistered(): bool
    {
        return $this->register;
    }

    /**
     * Adds a method to call after object instantiation.
     *
     * @param string $name       Method name to call.
     * @param array  $parameters Parameters to pass to the method. To use an
     *      entry defined in the container, use `Container::entry()`.
     *
     * @return $this Fluent interface, allowing multiple calls to be chained.
     */
    public function method(string $name, array $parameters = []): Definition
    {
        $this->actions[] = new CallMethod($name, $parameters);
        return $this;
    }

    /**
     * Sets a property after object instantiation.
     *
     * @param string $name  Property name to set.
     * @param mixed  $value Value to set.
     *
     * @return $this Fluent interface, allowing multiple calls to be chained.
     */
    public function property(string $name, $value = null): Definition
    {
        $this->actions[] = new SetProperty($name, $value);
        return $this;
    }

    abstract protected function factoryMethod(ContainerInterface $container);
}
