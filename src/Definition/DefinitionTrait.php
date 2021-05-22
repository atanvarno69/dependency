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

/**
 * Trait providing `Definition` implementation functionality.
 */
trait DefinitionTrait
{
    /** Trait use block. */
    use ResolveParametersTrait;

    /** @var InstanceAction[] $actions */
    private array $actions;
    private bool $register;

    /** @inheritdoc */
    public function build(Container $container): mixed
    {
        $return = $this->factoryMethod($container);
        if (!is_object($return)) {
            return $return;
        }
        if (!empty($this->actions)) {
            foreach ($this->actions as $action) {
                $return = $action($return, $container);
            }
        }
        return $return;
    }

    /** @inheritdoc */
    public function isRegistered(): bool
    {
        return $this->register;
    }

    /** @inheritdoc */
    public function method(string $name, array $parameters = []): static
    {
        $this->actions[] = new CallMethod($name, $parameters);
        return $this;
    }

    /** @inheritdoc */
    public function property(string $name, mixed $value = null): static
    {
        $this->actions[] = new SetProperty($name, $value);
        return $this;
    }

    /**
     * Resolve entry to a returnable value.
     *
     * @internal
     */
    abstract protected function factoryMethod(Container $container): mixed;
}
