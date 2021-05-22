<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2021 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

declare(strict_types = 1);

namespace Atanvarno\Dependency\Definition;

use Closure, Throwable;
use Psr\Container\ContainerInterface as Container;
use Atanvarno\Dependency\{
    Definition, Exception\RuntimeException
};

/**
 * Definition for a container entry loaded by calling a use-defined callable.
 *
 * Returned by the helper function `factory()`.
 */
class FactoryDefinition implements Definition
{
    use DefinitionTrait;

    public function __construct(
        private Closure $callable,
        private array $parameters,
        private bool $register,
    ) {
    }

    /**
     * @inheritdoc
     *
     * @throws RuntimeException Error encountered calling the user-defined
     *                          callable.
     */
    protected function factoryMethod(Container $container): mixed
    {
        $parameters = $this->resolveParameter($this->parameters, $container);
        try {
            $return = call_user_func($this->callable, ...$parameters);
        } catch (Throwable $caught) {
            $msg = sprintf('Encountered error: %s', $caught->getMessage());
            throw new RuntimeException($msg, $caught->getCode(), $caught);
        }
        return $return;
    }
}
