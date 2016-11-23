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

namespace Atan\Container;

trait ContainerTrait
{
    /**
     * Properties
     *
     * @var array $definitions Array of entity definitions
     * @var array $registry    Array of shared entities
    protected $definitions, $registry;
    
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
     * Register an entity as shared
     *
     * @param  string $id     Entity identifier
     * @param  mixed  $entity Entity to share
     * @return bool           `true` on success, `false` otherwise
    public function register(string $id, $entity): bool
    {
        $return = !isset($this->registry[$id]);
        if ($return) {
            $this->registry[$id] = $entity;
        }
        return $return;
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
}
