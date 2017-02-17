<?php
/**
 * @package   Atan\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atan\Dependency;

/** SPL use block. */
use ArrayAccess, Throwable, TypeError;

/** PSR-11 use block. */
use Psr\Container\ContainerInterface;

/** Package use block. */
use Atan\Dependency\Exception\{
    ContainerException, NotFoundException
};

/**
 * Atan\Dependency\Container
 *
 * A basic container implementing PSR-11 `ContainerInterface`.
 *
 * The container may contain and return any PHP type. These container entries
 * are associated with a unique user-defined `string` identifier. All entries,
 * except those defined with the `factory()` method, are registered, that is a
 * call to `get()` with the identifier will always return the same value.
 *
 * To define a class to be lazy loaded, use one of the `class()` or `factory()`
 * methods. For other entries, use the `add()` method.
 *
 * Entries can be accessed using the methods defined in `ContainerInterface` or
 * using array syntax.
 */
class Container implements ArrayAccess, ContainerInterface
{
    /** @var mixed[] $registry */
    private $registry;

    /**
     * Container constructor.
     *
     * Optionally accepts an array of entries.
     *
     * Optionally accepts an identifier for itself, defaults to 'container'.
     *
     * @api
     *
     * @param string  $id      Identifier for the container's own entry.
     */
    public function __construct(string $id = 'container')
    {
        $this->add($id, $this);
    }

    /**
     * Adds an entry to the container.
     *
     * An entry can be of any type. To define a lazy loaded class, give an
     * instance of the `Definition` class.
     *
     * @api
     *
     * @param string $id    Identifier of the entry to set.
     * @param mixed  $value Value of the entry to set.
     *
     * @return void
     */
    public function add(string $id, $value)
    {
        $this->registry[$id] = $value;
    }
    
    /**
     * Adds a registered class definition for lazy loading.
     *
     * @api
     *
     * @param string $className     The name of the defined class.
     * @param mixed  ...$parameters Constructor parameters.
     *
     * @throws InvalidArgumentException Class name does not exist.
     *
     * @return Definition
     */
    public function class(string $className, ...$parameters): Definition
    {
        if (!class_exists($className)) {
            $msg = "A class named '$className' does not exist";
            throw new InvalidArgumentException($msg);
        }
        return new Definition(true, $className, $parameters);
    }

    /**
     * Deletes an entry from the container.
     *
     * @api
     *
     * @param string $id Identifier of the entry to delete.
     *
     * @return void
     */
    public function delete(string $id)
    {
        unset($this->registry[$id]);
    }
    
    /**
     * Use a container entry as a parameter.
     *
     * @api
     *
     * @param string $id Identifier of the entry to reference.
     *
     * @return EntryProxy
     */
    public function entry(string $id): EntryProxy
    {
        return new EntryProxy($id);
    }

    /**
     * Adds an unregistered class definition for lazy loading.
     *
     * @api
     *
     * @param string $className     The name of the defined class.
     * @param mixed  ...$parameters Constructor parameters.
     *
     * @throws InvalidArgumentException Class name does not exist.
     *
     * @return Definition
     */
    public function factory(string $className, ...$parameters): Definition
    {
        if (!class_exists($className)) {
            $msg = "A class named '$className' does not exist";
            throw new InvalidArgumentException($msg);
        }
        return new Definition(false, $className, $parameters);
    }

    /** @inheritdoc */
    public function get($id)
    {
        if (!is_string($id)) {
            throw new TypeError('Parameter must be a string');
        }
        if (!$this->has($id)) {
            throw new NotFoundException("$id not found");
        }
        $entry = $this->registry[$id];
        if (!$entry instanceof Definition) {
            return $entry;
        }
        try {
            $return = $this->build($entry);
        } catch (Throwable $caught) {
            throw new ContainerException(
                "Error getting $id",
                $caught->getCode(),
                $caught
            );
        }
        if ($entry->getRegister()) {
            $this->add($id, $return);
        }
        return $return;
    }

    /** @inheritdoc */
    public function has($id): bool
    {
        if (!is_string($id)) {
            throw new TypeError('Parameter must be a string');
        }
        return array_key_exists($id, $this->registry);
    }

    /**
     * @internal Implements `ArrayAccess`.
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        if (!is_string($offset)) {
            return false;
        }
        return $this->has($offset);
    }

    /**
     * @internal Implements `ArrayAccess`.
     *
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if (!is_string($offset) || !$this->has($offset)) {
            return null;
        }
        return $this->get($offset);
    }

    /**
     * @internal Implements `ArrayAccess`.
     *
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (is_string($offset)) {
            $this->add($offset, $value);
        }
    }

    /**
     * @internal Implements `ArrayAccess`.
     *
     * @param mixed $offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        if (is_string($offset)) {
            $this->delete($offset);
        }
    }

    private function build(Definition $definition)
    {
        $name = $definition->getClassName();
        $params = $this->resolveParams($definition->getParameters());
        $object = new $name(...$params);
        $methods = $definition->getMethods();
        foreach ($methods as $method => $params) {
            $params = $this->resolveParams($params);
            $object->$method(...$params);
        }
        return $object;
    }

    private function resolveParams(array $parameters): array
    {
        $return = [];
        foreach ($parameters as $key => $value) {
            $return[$key] = $value;
            if (is_array($value)) {
                $return[$key] = $this->resolveParams($value);
            }
            if ($value instanceof EntryProxy) {
                $return[$key] = $this->get($value);
            }
        }
        return $return;
    }
}
