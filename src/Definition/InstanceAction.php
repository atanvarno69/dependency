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
 * Defines an action for the container to take on a newly instantiated object to
 * configure it before it is returned.
 *
 * @internal
 */
interface InstanceAction
{
    /**
     * Invokable configuration action.
     */
    public function __invoke(object $object, Container $container): object;
}
