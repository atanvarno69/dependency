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
use BadFunctionCallException, InvalidArgumentException, Throwable;

/**
 * ParentAwareContainer class
 */
class ParentAwareContainer extends Container
{
    /** Traits use block */
    use ContainerTrait, LoggerAwareTrait, ParentAwareTrait;
    
    /**
     * Constructor
     */
    public function __construct(
        array $definitions = [],
        ContainerInterface $parent = null,
        LoggerInterface $logger = null
    ) {
        if (isset($parent)) {
            $this->parent = $parent;
        }
        parent::__construct($definitions, $logger);
    }
    
    /**
     * Check if a string parameter is a valid entry and resolve it. Does this
     * recursively for array parameters. Other parameters pass straight through.
     *
     * @param  mixed $parameter Parameter to resolve
     * @return mixed            Resolved parameter
     */
    protected function resolveParameter($parameter)
    {
        $return = $parameter;
        if (is_string($parameter)) {
            if (strpos($parameter, ':') === 0) {
                $id = substr($parameter, 1);
                if ($this->parent->has($id)) {
                    $return = $this->parent->get($id);
                }
            }
        }
        if (is_array($parameter)) {
            $return = [];
            foreach ($parameter as $key => $item) {
                $return[$key] = $this->resolveParameter($item);
            }
        }
        return $return;
    }
}
