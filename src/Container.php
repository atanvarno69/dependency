<?php
/**
 * Container class
 * 
 * Copyright © 2016 atanvarno.com
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
 * @package    Atan\Dependency
 * @author     atanvarno.com <https://github.com/atanvarno69>
 * @copyright  2016 atanvarno.com
 * @license    http://opensource.org/licenses/GPL-3.0 GNU GPL v3
 */
 
namespace Atan\Dependency;

/**
 * Package use block
 */
use Exception\{
    ContainerException,
    NotFoundException
};

/**
 * PSR-3 use block
 */
use Psr\Log\{
    LoggerAwareInterface,
    LoggerInterface
}

/**
 * Container interop use block
 *
 * @todo Update to PSR namespace when PSR-11 available
 */
use Interop\Container\ContainerInterface;

/**
 * Error & exception use block
 */
use InvalidArgumentException, Throwable, TypeError;

/**
 * Container class
 *
 * Basic dependency injector.
 */
class Container implements ContainerInterface, LoggerAwareInterface
{
    /**
     * The child containers
     *
     * @var ContainerInterface[]
     */
    protected $children;
    
    /**
     * Array of object definitions
     *
     * @var array[] $definitions
     */
    protected $definitions;
    
    /**
     * The logger
     *
     * @var LoggerInterface $logger
     */
    protected $logger;
    
    /**
     * The parent container
     *
     * @var ContainerInterface $parent
     */
    protected $parent;
    
    /**
     * Array of registered instances
     *
     * @var object[] $registry
     */
    protected $registry;
    
    /**
     * Constructor
     *
     * Optionally set a parent container, an array of child containers and a
     * PSR-3 logger.
     *
     * @param  ContainerInterface   $parent   A parent container.
     * @param  ContainerInterface[] $children Child container(s).
     * @param  LoggerInterface      $logger   A PSR-3 logger.
     * @return void
     */
    public function __construct(
        $parent = null,
        $children = [],
        LoggerInterface $logger = null
    ) {
        if (isset($parent)) {
            $this->setParent($parent);
        }
        if (!empty($children)) {
            $this->setChildren($children);
        }
        if (isset($logger)) {
            $this->setLogger($logger);
        }
    }
    
    /**
     * Add a child container
     *
     * @param  ContainerInterface $child
     * @return void
     */
    public function addChild(ContainerInterface $child)
    {
        $this->children[] = $child;
    }
    
    /**
     * Define an entry
     *
     * @param  string                    $id             Identifier of the 
     *                                                   entry.
     * @param  ContainerInterface|string $nameOrCallable A valid class name or
     *                                                   a callable which
     *                                                   returns the entity
     *                                                   when passed ...$params.
     * @param  array                     $params         An array of parameters
     *                                                   for entry instantiation.
     * @param  bool                      $register       Whether the instance,
     *                                                   once created, should 
     *                                                   be registered.
     * @throws InvalidArgumentException                  $nameOrCallable is not
     *                                                   a string or callable.
     * @return bool
     */
    public function define(
        string $id,
        $nameOrCallable,
        array $params = [],
        bool $register = false
    ): bool {
        if (is_string($nameOrCallable)) {
            $method = $this->makeFactory($nameOrCallable);
        } elseif (is_callable($nameOrCallable)) {
            $method = $nameOrCallable;
        } else {
            $msg = 'Paramter must be a string or callable';
            throw new InvalidArgumentException($msg);
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
     * Return a container entry from its identifier
     *
     * @param  string             $id Identifier of the entry to look for.
     * @throws TypeError              $id is not a string.
     * @throws NotFoundException      No entry was found for this identifier.
     * @throws ContainerException     Error while retrieving the entry.
     * @return mixed
     */
    public function get($id)
    {
        if (!is_string($id)) {
            $msg = 'Parameter must be a string';
            throw new TypeError($msg);
        }
        try {
            if ($this->has($id)) {
                $return = $this->registry[$id] ?? $this->instantiate($id);
            } elseif (!empty($this->children)) {
                foreach ($children as $child) {
                    if ($child->has($id)) {
                        $return = $child->get($id);
                        break;
                    }
                }
            } 
        } catch (Throwable $t) {
            $msg = class_name($t)
                 . ' caught resolving entry '
                 . $id
                 . 'with message: '
                 . $t->getMessage();
            $exception =  new ContainerException($msg, $t->getCode(), $t);
            if (isset($this->logger)) {
                $this->logger->critical(
                    'Atan\Dependnecy unable to resolve ' . $id,
                    ['exception' => $exception]
                );
            }
            throw $exception;
        }
        if (!isset($return)) {
            $msg = $id . ' not found';
            $exception = new NotFoundException($msg);
            if (isset($this->logger)) {
                $this->logger->error(
                    'Atan\Dependnecy could not find ' . $id,
                    ['exception' => $exception]
                );
            }
            throw $exception;
        }
        return $return;
    }
    
    /**
     * Return true if the container can return an entry for the given
     * identifier, false otherwise.
     * 
     * has($id) returning true does not mean that get($id) will not throw an
     * exception. It does however mean that get($id) will not throw a 
     * NotFoundException.
     *
     * @param  string $id Identifier of the entry to look for.
     * @throws TypeError  $id is not a string.
     * @return bool
     */
    public function has($id)
    {
        if (!is_string($id)) {
            $msg = 'Parameter must be a string';
            throw new TypeError($msg);
        }
        return isset($this->registry[$id]) || isset($this->definitions[$id]);
    }
    
    /**
     * Register an object with the container
     *
     * @param  string                   $id    Identifier of the entry to add.
     * @param  object                   $entry Entry to add.
     * @throws InvalidArgumentException        $entry is not an object.
     * @return bool
     */
    public function register(string $id, $entry): bool
    {
        if (!is_object($entry)) {
            $msg = 'Paramter must be an object';
            throw new InvalidArgumentException($msg);
        }
        $return = !isset($this->registry[$id]);
        if ($return) {
            $this->registry[$id] = $entry;
        }
        return $return;
    }
    
    /**
     * Set an array of child containers
     *
     * @param  ContainerInterface[] $children
     * @return void
     */
    public function setChildren(array $children)
    {
        $this->children = [];
        foreach ($children as $child) {
            $this->addChild($child);
        }
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
     * Set a parent container
     *
     * @param  ContainerInterface $parent
     * @return void
     */
    public function setParent(ContainerInterface $parent)
    {
        $this->parent = $parent;
    }
    
    /**
     * Instantiate antry from its definition
     *
     * @param  string $id Identifier of the entry to instantiate.
     * @return object
     */
    protected function instantiate(string $id)
    {
        $factory = $this->definitions[$id]['method'];
        $params = [];
        foreach ($this->definitions[$id]['params'] as $param) {
            $params[] = $this->resolveParam($param);
        }
        $return = call_user_func($factory, ...$params);
        if ($this->definitions[$id]['register']) {
            $this->register($id, $return);
        }
        return $return;
    }
    
    /**
     * Make a callable which returns an instance of the given class, optionally
     * accepting an arbitary number of paramters.
     *
     * @param  string                   $className The name of the class the
     *                                             factory will produce.
     * @throws InvalidArgumentException            $className is not a valid
     *                                             class name.
     * @return callable
     */
    protected function makeFactory(string $className): callable
    {
        if (!class_exists($className)) {
            $msg = $className . ' is not a valid class name';
            throw new InvalidArgumentException($msg);
        }
        return function (...$params) use ($className) {
            return new $className(...$params);
        };
    }
    
    /**
     * Resolve the given paramter as a dependency, if possible
     *
     * Strings beginning with ':' are taken as an identifier to resolve. Arrays
     * are recursively passed through this method to resolve any identifier 
     * strings they contain.
     *
     * Other types are returned without attempting to resolve them.
     *
     * @param  mixed              $param
     * @throws ContainerException        Error resolving dependency.
     * @return mixed
     */
    protected function resolveParam($param)
    {
        $return = $param;
        if (is_array($param)) {
            $return = [];
            foreach ($param as $key => $item) {
                $return[$key] = $this->resolveParam($item);
            }
        } elseif (is_string($param)) {
            if (strpos($param, ':') === 0) {
                $id = substr($param, 1);
                try {
                    if (isset($this->parent)) {
                        $return = $this->parent->get($id);
                    } else {
                        $return = $this->get($id);
                    }
                } catch (Throwable $t) {
                    $msg = class_name($t)
                         . ' caught resolving dependnecy '
                         . $id
                         . ' with message: '
                         . $t->getMessage();
                    throw new ContainerException($msg, $t->getCode(), $t);
                }
            }
        }
        return $return;
    }
}
