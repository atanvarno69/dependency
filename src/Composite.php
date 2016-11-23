<?php
/**
 * Composite class file
 * 
 * Copyright (C) 2016 atanvarno.com
 *
 * This file is part of Atan\Dependency.
 *
 * Atan\Dependency is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Atan\Dependency is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Atan\Dependency.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package   Atan\Dependency
 * @author    atan <https://github.com/atanvarno69>
 * @copyright 2016 atanvarno.com
 * @license   http://opensource.org/licenses/GPL-3.0 GNU GPL v3
 */

namespace Atan\Dependency;

/** Package use block */
use Exception\{
    ContainerException,
    NotFoundException
};

/** PSR-3 use block */
use Psr\Log\{
    LoggerAwareInterface,
    LoggerInterface
};

/**
 * PSR-11 use block
 *
 * @todo Change to `Psr` namespace when PSR-11 accepted
 */
use Interop\Container\ContainerInterface;

/** SPL use block */
use Throwable;

/**
 * Composite class
 */
class Composite implements ContainerInterface, LoggerAwareInterface
{
    /** Traits use block */
    use CompositeTrait, LoggerAwareTrait;
    
    /**
     * Constructor
     */
    public function __construct(
        array $children = [],
        LoggerInterface $logger = null
    ) {
        foreach ($children as $child) {
            $this->appendChild($child);
        }
        if (isset($logger)) {
            $this->setLogger($logger);
        }
    }
    
    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     * 
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundException`.
     *
     * @param  string $id Identifier of the entry to look for
     * @return bool
     */
    public function has($id)
    {
        $return = false;
        foreach ($this->children as $child) {
            $return = $child->has($id);
            if ($return) {
                break;
            }
        }
        return $return;
    }
    
    /**
     * Finds an entry of the container by its identifier and returns it
     *
     * @param  string              $id Identifier of the entry to look for
     * @throws NotFoundException       No entry was found for this identifier
     * @throws ContainerException      Error while retrieving the entry
     * @return mixed                   Entry
     */
    public function get($id)
    {
        if (!$this->has($id)) {
            $msg = 'No entry found with identifier ' . $id;
            throw new NotFoundException($msg, 500);
        }
        try {
            foreach ($this->children as $child) {
                if ($child->has($id)) {
                    $return = $child->get($id);
                    break;
                }
            }
        } catch (Throwable $t) {
            $msg = 'Could not resolve ' . $id;
            throw new ContainerException($msg, $t->getCode(), $t);
        }
        return $return;
    }
}
