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
 * Container class
 */
class Container implements ContainerInterface, LoggerAwareInterface
{
    /** Traits use block */
    use ContainerTrait, LoggerAwareTrait;
    
    /**
     * Constructor
     */
    public function __construct(
        array $definitions = [],
        LoggerInterface $logger = null
    ) {
        foreach ($definitions as $id => $def) {
            if (!is_array($def)) {
                $msg = 'Definitions must be an array of definition arrays';
                throw new InvalidArgumentException($msg, 500);
            }
            if (!array_key_exists('entity', $def)) {
                $msg = 'Definition for ' . $id . ' must supply an entity';
                throw new InvalidArgumentException($msg, 500);
            }
            $params = $def['params'] ?? [];
            $register = $def['register'] ?? true;
            $this->define($id, $def['entity'], $params, $register);
        }
        if (isset($logger)) {
            $this->setLogger($logger);
        }
        $this->register('Container', $this);
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
        return (isset($this->registry[$id]) || isset($this->definitions [$id]));
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
            $return = $this->registry[$id] ?? $this->getFromDefinition($id);
        } catch (Throwable $t) {
            $msg = 'Could not resolve ' . $id;
            throw new ContainerException($msg, $t->getCode(), $t);
        }
        return $return;
    }
    
    /**
     * Calls a definition's callable, passing the defined parameters to it
     *
     * Registers the resulting entity if required.
     *
     * @param  string                   $id Identifier of the entry to get
     * @throws BadFunctionCallException     Invalid user defined callable
     * @return mixed                        Entity
     */
    protected function getFromDefinition(string $id)
    {
        $method = $this->definitions[$id]['method'];
        $params = [];
        foreach ($this->definitions[$id]['params'] as $param) {
            $params[] = $this->resolveParameter($param);
        }
        try {
            $return = $method(...$params);
        } catch (Throwable $t) {
            $msg = 'Invalid user defined callable for ' . $id;
            throw new BadFunctionCallException($msg, $t->getCode(), $t);
        }
        if ($this->definitions[$id]['register']) {
            $this->register($id, $return);
        }
        return $return;
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
                if ($this->has($id)) {
                    $return = $this->get($id);
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
