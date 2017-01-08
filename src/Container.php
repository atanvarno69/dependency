<?php
/**
 * Container class file
 *
 * @package   Atan\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atan\Dependency;

/** SPL use block. */
use BadFunctionCallException, InvalidArgumentException, Throwable;

/** PSR-11 use block. */
use Interop\Container\ContainerInterface;

/** Package use block */
use Atan\Dependency\Exception\{
    ContainerException,
    NotFoundException
};

class Container implements ContainerInterface
{
    /**
     * @var ContainerInterface[] $children    Child containers.
     * @var array                $definitions Array of entity definitions.
     * @var ContainerInterface   $parent      Parent container.
     * @var array                $registry    Array of shared entities.
     */
    protected $children, $definitions, $parent, $registry;
    
    /**
     * Container constructor.
     * 
     * @param array[]              $definitions Array of definition arrays.
     * @param ContainerInterface   $parent      Parent container.
     * @param ContainerInterface[] $children    Child containers.
     *
     * @throws InvalidArgumentException `$definitions` is not array of arrays.
     */
    public function __construct(
        array $definitions = [],
        ContainerInterface $parent = null,
        array $children = []
    ) {
        foreach ($definitions as $id => $def) {
            if (!is_array($def)) {
                $msg = 'Definitions must be an array of definition arrays';
                throw new InvalidArgumentException($msg, 500);
            }
            $params = $def[1] ?? [];
            $register = $def[2] ?? true;
            $this->define($id, $def[0], $params, $register);
        }
        if (isset($parent)) {
            $this->setParent($parent);
        }
        foreach ($children as $child) {
            $this->appendChild($child);
        }
        $this->register('Container', $this);
    }
    
    /**
     * Append a child container.
     *
     * @param ContainerInterface $child Child container.
     *
     * @return void
     */
    public function appendChild(ContainerInterface $child)
    {
        $this->children[] = $child;
    }
    
    /**
     * Define an entity.
     *
     * @param  string  $id       Entity identifier.
     * @param  mixed   $entity   Entity factory callable, class name or entity.
     * @param  mixed[] $params   Parameters for entity construction.
     * @param  bool    $register Whether the entity should become shared.
     *
     * @return bool `true` on success, `false` otherwise.
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
                $method = (class_exists($entity))
                        ? $this->makeFactory($entity)
                        : $this->makeProvider($entity);
            }
            $method = $method ?? $this->makeProvider($entity);
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
        return $return;
    }
    
    /**
     * Prepend a child container.
     *
     * @param ContainerInterface $child Child container.
     *
     * @return void
     */
    public function prependChild(ContainerInterface $child)
    {
        array_unshift($this->children, $child);
    }
    
    /**
     * Register an entity as shared.
     *
     * @param string $id     Entity identifier.
     * @param mixed  $entity Entity to share.
     *
     * @return bool `true` on success, `false` otherwise
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
     * Set the parent container.
     *
     * @param ContainerInterface $parent Parent container.
     *
     * @return void
     */
    public function setParent(ContainerInterface $parent)
    {
        $this->parent = $parent;
    }

    private function getFromDefinition(string $id)
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

    private function makeFactory(string $className): callable
    {
        return function (...$params) use ($className) {
            return new $className(...$params);
        };
    }

    private function makeProvider($entity): callable
    {
        return function (...$params) use ($entity) {
            return $entity;
        };
    }

    private function resolveParameter($parameter)
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
