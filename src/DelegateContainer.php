<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2021 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

declare(strict_types = 1);

namespace Atanvarno\Dependency;

use Psr\Container\ContainerInterface as Container;

/**
 * Implement `container-interop`'s [delegate lookup feature](https://github.com/container-interop/container-interop/blob/master/docs/Delegate-lookup.md),
 * which allows a container to act as both a parent/composite/delegate and child
 * container.
 *
 * @api
 */
interface DelegateContainer extends Container
{
    /**
     * Add a child container.
     *
     * This will make the container act as a composite container.
     *
     * This method MUST register this container as the delegate of the child
     * container if it is not already the child's delegate.
     */
    public function addChild(DelegateContainer $child): static;


    /**
     * Set a container to delegate dependency resolution to.
     *
     * This method MUST resister this container as a child of the delegate if it
     * is not already a delegate's child.
     */
    public function setDelegate(DelegateContainer $delegate): static;
}