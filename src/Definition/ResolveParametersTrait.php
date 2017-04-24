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

trait ResolveParametersTrait
{
    private function resolveParameter($parameter, ContainerInterface $container)
    {
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
