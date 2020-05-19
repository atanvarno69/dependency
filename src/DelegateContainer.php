<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2020 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Dependency;

/** PSR-11 use block. */
use Psr\Container\ContainerInterface as Container;

/**
 * Atanvarno\Dependency\DelegateContainer
 *
 * Interface to implement `container-interop`'s [delegate lookup feature](https://github.com/container-interop/container-interop/blob/master/docs/Delegate-lookup.md),
 * which allows a container to act as both a parent/composite/delegate and child
 * container.
 *
 * @package Atanvarno\Dependency
 * @api
 */
interface DelegateContainer extends Container
{
    /**
     * Adds a child container.
     *
     * This will make the container act as a composite container.
     *
     * This method MUST register this container as the delegate of the child
     * container if it is not already the child's delegate.
     *
     * @param DelegateContainer $child Child container to add.
     *
     * @return $this Fluent interface, allowing multiple calls to be chained.
     */
    public function addChild(DelegateContainer $child): DelegateContainer;


    /**
     * Sets a container to delegate dependency resolution to.
     *
     * This method MUST resister this container as a child of the delegate if it
     * is not already a delegate's child.
     *
     * @param DelegateContainer $delegate Delegate container.
     *
     * @return $this Fluent interface, allowing multiple calls to be chained.
     */
    public function setDelegate(DelegateContainer $delegate): DelegateContainer;
}