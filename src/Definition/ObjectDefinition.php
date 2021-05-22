<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2021 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

declare(strict_types = 1);

namespace Atanvarno\Dependency\Definition;

use Throwable;
use Psr\Container\ContainerInterface as Container;
use Atanvarno\Dependency\{
    Definition, Exception\ConfigurationException, Exception\RuntimeException
};

/**
 * Definition for an object container entry.
 *
 * Returned by the helper function `object()`.
 */
class ObjectDefinition implements Definition
{
    use DefinitionTrait;

    /**
     * @throws ConfigurationException Given class does not exist.
     */
    public function __construct(
        private string $className,
        private array $parameters,
        private bool $register,
    ) {
        if (!class_exists($className)) {
            $msg = sprintf('%s is not a valid class name', $className);
            throw new ConfigurationException($msg);
        }
    }

    /**
     * @inheritdoc
     *
     * @throws RuntimeException Error encountered calling class's constructor.
     */
    protected function factoryMethod(Container $container): object
    {
        $parameters = $this->resolveParameter($this->parameters, $container);
        try {
            $return = new $this->className(...$parameters);
        } catch (Throwable $caught) {
            $msg = sprintf('Encountered error: %s', $caught->getMessage());
            throw new RuntimeException($msg, $caught->getCode(), $caught);
        }
        return $return;
    }
}
