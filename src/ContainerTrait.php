<?php
/**
 * ContainerTrait trait file
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

/** PSR-3 Logger Interface use block */
use Psr\Log\LoggerInterface;

/**
 * ContainerTrait trait
 * 
 * Provides PSR-11 `ContainerInterface` functionality.
 */
trait ContainerTrait
{
    /**
     * Class properties
     *
     * @var array[]              $definitions Entity definitions
     * @var object[]             $registry    Registered objects
     */
    protected $definitions, $registry;
    
    /**
     * Constructor
     *
     * Optionally set a parent container, an array of child containers and a
     * PSR-3 logger.
     *
     * @param  LoggerInterface      $logger   PSR-3 logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        if (isset($logger)) {
            $this->setLogger($logger);
        }
        $this->register('Container', $this);
    }
    
    /**
     * Define an entity
     *
     * A callable given as an entity will be called, passing it $params, when
     * the entry is called from `get()`.
     *
     * A class name given as an entity will make an entry that will instantiate
     * that class, passing its constructor $params when called from `get()`.
     *
     * Any other value given as an entity will be returned when the entry is
     * called from `get()`. $params will be ignored.
     *
     * @param  string $id       Identifier of the entity
     * @param  mixed  $entity   Any value of any type
     * @param  array  $params   An array of parameters
     * @param  bool   $register If the entity is shared after first `get()` call
     * @return bool             `true` on success, `false` otherwise
     */
    public function define(
        string $id,
        $entity,
        array $params = [],
        bool $register = true
    ): bool {
        if (is_callable($entity)) {
            $method = $entity;
        } elseif (is_string($entity) && class_exists($entity)) {
            $method = $this->makeFactory($entity);
        } else {
            $method = $this->makeProvider($entity);
        }
        $return = !isset($this->definitions[$id]);
        if ($return) {
            $this->definitions[$id] = [
                'method'   => $method,
                'params'   => $params,
                'register' => $register,
            ];
        }
        return $return;
    }
    
    /**
     * Finds an entry of the container by its identifier and returns it
     *
     * @abstract
     * @param    string            $id Identifier of the entry to look for
     * @throws   NotFoundException     No entry was found for this identifier
     * @throws   ContainerException    Error while retrieving the entry
     * @return   mixed                 Entry
     */
    public abstract function get($id);
    
    /**
     * Return `true` if the container can return an entry for the given
     * identifier, `false` otherwise
     * 
     * `has($id)` returning true does not mean that `get($id)` will not throw an
     * exception. It does however mean that `get($id)` will not throw a 
     * `NotFoundException`.
     *
     * @param  string                   $id Identifier of the entry to look for
     * @throws InvalidArgumentException     $id is not a string
     * @return bool
     */
    public function has($id)
    {
        if (!is_string($id)) {
            $msg = 'Parameter must be a string';
            throw new InvalidArgumentException($msg);
        }
        return (isset($this->registry[$id]) || isset($this->definitions[$id]));
    }
    
    /**
     * Register an object with the container
     *
     * @param  string                   $id    Identifier of the entry to add
     * @param  mixed                    $entry Entry to add
     * @throws InvalidArgumentException        $entry is not an object
     * @return bool
     */
    public function register(string $id, $entry): bool
    {
        $return = !isset($this->registry[$id]);
        if ($return) {
            $this->registry[$id] = $entry;
        }
        return $return;
    }
    
    /**
     * Make a callable which returns an instance of the given class, optionally
     * accepting an arbitrary number of parameters.
     *
     * @param  string                   $className The name of the class the
     *                                                 factory will produce
     * @throws InvalidArgumentException            $className is not a valid
     *                                             class name
     * @return callable
     */
    protected function makeFactory(string $className): callable
    {
        if (!class_exists($className)) {
            $msg = $className . ' is not a valid class name';
            throw new InvalidArgumentException($msg);
        }
        return function(...$params) use ($className) {
            return new $className(...$params);
        };
    }
    
    /**
     * Makes a callable which returns the given entity, accepting and ignoring
     * an arbitrary number of parameters.
     *
     * @param  mixed    $entity Entity to return
     * @return callable
     */
    protected function makeProvider($entity): callable
    {
        return function(...$params) use ($entity) {
            return $entity;
        };
    }
}
