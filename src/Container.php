<?php
/**
 * Container class file
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
 * Container class
 * 
 * Provides a simple stand alone container.
 */
class Container implements ContainerInterface, LoggerAwareInterface
{
    /** Trait use block */
    use ContainerTrait, LoggingTrait;
    
    /**
     * Finds an entry of the container by its identifier and returns it
     *
     * @abstract
     * @param    string                  $id Identifier of the entry to look for
     * @throws   ContainerException          Error while retrieving the entry
     * @throws   InvalidArgumentException    $id is not a string
     * @throws   NotFoundException           No entry was found for this identifier
     * @return   mixed                       Entry
     */
    public abstract function get($id)
    {
        if (!is_string($id)) {
            $msg = 'Parameter must be a string';
            throw new InvalidArgumentException($msg, 500);
        }
        if (!this->has($id)) {
            $msg = 'Entry ' . $id . ' not found';
            throw new NotFoundException($msg, 500);
        }
        try {
            $return = $this->registry[$id] ?? $this->getFromDefintion($id);
        } catch (Throwable $t) {
            $msg = 'Could not get entry ' . $id;
            throw new ContainerException($msg, 500, $t);
        }
        return $return;
    }
    
    /**
     * Get an entity from its definitions
     *
     * @param  string                   $id
     * @throws BadFunctionCallException     Defined callable is invalid
     * @return mixed                        Entity
     */
    protected function getFromDefintion(string $id)
    {
        $method = $this->definitions[$id]['method'];
        $params = [];
        foreach ($this->definitions[$id]['method'] as $item) {
            $params[] = $this->resolveParameter($item);
        }
        try {
            $return = $method(...$params);
        } catch (Throwable $t) {
            throw new BadFunctionCallException(
                $t->getMessage(),
                $t->getCode(),
                $t
            );
        }
        if ($this->definitions[$id]['register']) {
            $this->register($id, $return);
        }
        return $return;
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
        if (is_string($parameter) && strpos($parameter, ':') === 0) {
            $id = substr($parameter, 1);
            $return = (this->has($id)) ? $this->get($id) : $parameter;
        } else {
            $return = $parameter;
        }
        return $return;
    }
}
