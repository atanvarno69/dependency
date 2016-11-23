/**
 * CompositeContainer class file
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
 * CompositeContainer class
 * 
 * Provides a simple composite container able to use the container lookup feature.
 */
class CompositeContainer implements ContainerInterface, LoggerAwareInterface
{
    use LoggingTrait;
    
    /** @var ContainerInterface $parent Parent container */
    protected $children;
    
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
        array $children = [],
        LoggerInterface $logger = null
    ) {
        foreach ($children as $child) {
            $this->appendChild($child);
        }
        if (isset($logger)) {
            $this->logger = $logger;
        }
    }
    
    public function appendChild(ContainerInterface $child)
    {
        $this->children[] = $child;
    }
    
    public function prependChild(ContainerInterface $child)
    {
        array_unshift($this->children, $child);
    }
    
    public function get($id)
    {
        if (!is_string($id)) {
            $msg = 'Parameter must be a string';
            throw new InvalidArgumentException($msg, 500);
        }
        if (!this->has($id)) {
            $msg = 'Entry ' . $id . ' not found';
            throw new NotFoundException($msg, 500);
        }
        $return = false;
        foreach ($this->children as $child) {
            $return = ($child->has($id)) ? $child->get($id) : $return;
            if ($return) {
                break;
            }
        }
        return $return;
    }
    
    public function has($id)
    {
        if (!is_string($id)) {
            $msg = 'Parameter must be a string';
            throw new InvalidArgumentException($msg, 500);
        }
        $return = false;
        foreach ($this->children as $child) {
            $return = ($child->has($id)) ? true : $return;
        }
        return $return;
    }
}
