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
use TypeError;

/**
 * Container class
 *
 * Basic dependency injector.
 */
class Container implements ContainerInterface
{
    /**
     * Array of object definitions
     *
     * @var array $definitions
     */
    protected $definitions;
    
    /**
     * Array of registered instances
     *
     * @var array $registry
     */
    protected $registry;
    
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
        if (!$this->has()) {
            $msg = $id . ' not found';
            throw new NotFoundException($msg);
        }
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
}
