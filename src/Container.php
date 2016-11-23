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
 * ParentAwareCompositeContainer class
 */
class Container implements ContainerInterface, LoggerAwareInterface
{
    /**
     * Properties
     *
     * @var ContainerInterface[] $children Child containers
     * @var array $definitions Array of entity definitions
     * @var LoggerInterface $logger PSR-3 logger
     * @var ContainerInterface $parent Parent container
     * @var array $registry    Array of shared entities
     */
    protected $children, $definitions, $logger, $parent, $registry;
    
    /**
     * Constructor
     */
    public function __construct(
        array $definitions = [],
        ContainerInterface $parent = null,
        array $children = [],
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
        if (isset($parent)) {
            $this->setParent($parent);
        }
        foreach ($children as $child) {
            $this->appendChild($child);
        }
        if (isset($logger)) {
            $this->setLogger($logger);
        }
        $this->register('Container', $this);
    }
    
    /**
     * Append a child container
     *
     * @param  ContainerInterface $child Child container
     * @return void
     */
    public function appendChild(ContainerInterface $child)
    {
        $this->children[] = $child;
    }
    
    /**
     * Define an entity
     *
     * @param  string  $id       Entity identifier
     * @param  mixed   $entity   Entity factory callable, class name or entity
     * @param  mixed[] $params   Parameters for entity construction
     * @param  bool    $register Whether the entity should become shared
     * @return bool              `true` on success, `false` otherwise
     */
    public function define(
        string $id,
        $entity,
        array $params = [],
        bool $register = true
    ): bool {
        $return = !isset($this->definitions[$id]);
        if ($return) {
            if (is_callable($entity)) {
                $method = $entity;
            } elseif (is_string($entity)) {
                if (class_exists($entity)) {
                    $method = $this->makeFactory($entity);
                } else {
                    $method = $this->makeProvider($entity);
                }
            } else {
                $method = $this->makeProvider($entity);
            }
            $this->definitions[$id] = [
                'method'   => $method,
                'params'   => $params,
                'register' => $register,
            ];
        }
        return $return;
    }
    
    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundException  No entry was found for this identifier.
     * @throws ContainerException Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id)
    {
        if (!is_string($id)) {
            $msg = 'Identifier must be a string';
            throw new InvalidArgumentException($msg, 500);
        }
        try {
            if ($this->has($id)) {
                $return = $this->registry[$id] ?? $this->getFromDefinition($id);
            } elseif (!empty($this->children)) {
                foreach ($this->children as $child) {
                    if ($child->has($id)) {
                        $return = $child->get($id);
                        break;
                    }
                }
            }       
        } catch (Throwable $t) {
            $msg = 'Could not get ' . $id . ': ' . $t->getMessage();
            throw new ContainerException($msg, $t->getCode(), $t);
        }
        if (!isset($return)) {
            $msg = $id . ' not found';
            throw new NotFoundException($msg);
        }
        return $return;
    }
    
    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     * 
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundException`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return boolean
     */
    public function has($id)
    {
        if (!is_string($id)) {
            $msg = 'Identifier must be a string';
            throw new InvalidArgumentException($msg, 500);
        }
        $return = isset($this->registry[$id]) || isset($this->definitions[$id]);
        if (!$return && !empty($this->children)) {
            foreach ($this->children as $child) {
                $return = $child->has($id);
                if ($return) {
                    break;
                }
            }
        }
        return $return;
    }
    
    /**
     * Prepend a child container
     *
     * @param  ContainerInterface $child Child container
     * @return void
     */
    public function prependChild(ContainerInterface $child)
    {
        array_unshift($this->children, $child);
    }
    
    /**
     * Register an entity as shared
     *
     * @param  string $id     Entity identifier
     * @param  mixed  $entity Entity to share
     * @return bool           `true` on success, `false` otherwise
     */
    public function register(string $id, $entity): bool
    {
        $return = !isset($this->registry[$id]);
        if ($return) {
            $this->registry[$id] = $entity;
        }
        return $return;
    }
    
    /**
     * LoggerAware implementation
     *
     * @param  LoggerInterface $logger
     * @return void
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * Set the parent container
     *
     * @param  ContainerInterface $parent Parent container
     * @return void
     */
    public function setParent(ContainerInterface $parent)
    {
        $this->parent = $parent;
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
     * Log to a PSR-3 logger, if available
     *
     * @param  string $level   Use constants provided by `LogLevel`
     * @param  string $message Message to log
     * @param  array  $context Context array to log
     * @return void
     */
    protected function log(string $level, string $message, array $context = [])
    {
        if (isset($this->logger)) {
            $msg = get_class($this) . ': ' . $message;
            $this->logger->log($level, $msg, $context);
        }
    }
    
    /**
     * Make a factory for a class definition
     *
     * @param  string   $className Class name
     * @return callable            Factory callable
     */
    protected function makeFactory(string $className): callable
    {
        return function (...$params) use ($className) {
            return new $className(...$params);
        };
    }
    
    /**
     * Make a provider for a non-class definition
     *
     * @param  mixed    $entity Entity to provide
     * @return callable         Provider callable
     */
    protected function makeProvider($entity): callable
    {
        return function (...$params) use ($entity) {
            return $entity;
        };
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
                $return = (isset($this->parent))
                        ? $this->parent->get($id)
                        : $this->get($id);
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
