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
 * Trait providing `resolveParameter` method for building dependencies.
 *
 * @internal
 */
trait ResolveParametersTrait
{
    /**
     * Resolve `Entry` or `Entry` array into a final value.
     */
    private function resolveParameter(
        Entry|array $parameter,
        Container $container,
    ): mixed {
        if ($parameter instanceof Entry) {
            return $container->get((string) $parameter);
        }
        if (is_array($parameter)) {
            $return = [];
            foreach ($parameter as $key => $value) {
                $return[$key] = $this->resolveParameter($value, $container);
            }
            return $return;
        }
        return $parameter;
    }
}
