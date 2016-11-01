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
 * Container interop use block
 *
 * @todo Update to PSR namespace when PSR-11 available
 */
use Interop\Container\ContainerInterface;

/**
 * Error & exception use block
 */
use InvalidArgumentException, TypeError;

/**
 * Container class
 *
 * Basic dependency injector.
 */
class Container implements ContainerInterface
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
    
    public function __construct($parent = null, $children = [])
    {
        if (isset($parent)) {
            $this->setParent($parent);
        }
        if (!empty($children)) {
            $this->setChildren($children);
        }
    }
    
    public function addChild(ContainerInterface $child)
    {
        $this->children[] = $child;
    }
    
    public function define(string $id, $classOrCallable, array $params = [], bool $register = false): bool
    {
        if (is_string($classOrCallable)) {
            $method = $this->makeFactory($classOrCallable);
        } elseif (is_callable($classOrCallable)) {
            $method = $classOrCallable;
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
     * @return mixed                  Entry.
     */
    public function get($id)
    {
        if (!is_string($id)) {
            $msg = 'Parameter must be a string';
            throw new TypeError($msg);
        }
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
        if (!isset($return)) {
            $msg = $id . ' not found';
            throw new NotFoundException($msg);
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
    
    public function setChildren(array $children)
    {
        $this->children = [];
        foreach ($children as $child) {
            $this->addChild($child);
        }
    }
    
    public function setParent(ContainerInterface $parent)
    {
        $this->parent = $parent;
    }
    
    protected function instantiate(string $id)
    {
        $factory = $this->definitions[$id]['method'];
        $params = [];
        foreach ($this->definitions[$id]['params'] as $param) {
            $params[] = $this->resolveParam($param);
        }
        $return = $factory(...$params);
        if ($this->definitions[$id]['register']) {
            $this->register($id, $return);
        }
        return $return;
    }
    
    protected function makeFactory(string $className)
    {
        return function (...$params) use ($className) {
            return new $className(...$params);
        };
    }
    
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
                if (isset($this->parent)) {
                    $return = $this->parent->get($id);
                } else {
                    $return = $this->get($id);
                }
            }
        }
        return $return;
    }
}
