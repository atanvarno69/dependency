<?php
/**
 * ParentAwareContainer class file
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

/** PSR-3 Logger Interface use block */
use Psr\Log\{
    LoggerAwareInterface,
    LoggerInterface,
    LogLevel
};

/**
 * PSR-11 Container Interface use block
 *
 * @todo Update to `Psr` namespace when PSR-11 available
 */
use Interop\Container\ContainerInterface;

/** SPL use block */
use BadFunctionCallException, InvalidArgumentException, Throwable;

/**
 * ParentAwareContainer class
 * 
 * Provides a simple container able to use the container lookup feature.
 */
class ParentAwareContainer extends Container
{    
    /** @var ContainerInterface $parent Parent container */
    protected $parent;
    
    /**
     * Constructor
     *
     * Optionally set a parent container and a PSR-3 logger.
     *
     * Registers itself as 'Container'.
     *
     * @param  ContainerInterface $parent Parent container
     * @param  LoggerInterface    $logger PSR-3 logger
     */
    public function __construct(
        ContainerInterface $parent = null,
        LoggerInterface $logger = null
    ) {
        if (isset($parent)) {
            $this->parent = $parent;
        }
        parent::__construct($logger);
    }
    
    /**
     * Resolve a parameter
     *
     * If it is a string starting with ':' attempt to resolve it as a container
     * entry. Otherwise return it.
     *
     * @param  mixed $parameter Parameter to resolve
     * @return mixed            Resolved parameter
     */
    protected function resolveParameter($parameter)
    {
        if (isset($this->parent)) {
            if (is_string($parameter) && strpos($parameter, ':') === 0) {
                $id = substr($parameter, 1);
                $return = (this->parent->has($id)) ? $this->parent->get($id) : $parameter;
            } else {
                $return = $parameter;
            }
        } else {
            $return = parent::resolveParameter($parameter);
        }
        return $return;
    }
}