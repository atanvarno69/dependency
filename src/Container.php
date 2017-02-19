<?php
/**
 * @package   Atan\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atan\Dependency;

/** SPL use block. */
use ArrayAccess,
    InvalidArgumentException,
    Throwable,
    TypeError,
    UnexpectedValueException;

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
 * are associated with a unique user-defined `string` identifier. All
 * entries, except those defined with `factory()`, are registered, that is a
 * call to `get()` with the identifier will always return the same value.
 *
 * Entries can be defined using the `add()` method. Lazy loaded classes are
 * defined using `class()` (for registered classes) or `factory()` (for
 * unregistered classes).
 *
 * As `Container` implements `ArrayAccess`, it can be used with array syntax:
 * ```
 * # Array syntax              # Alias of
 * $container['ID'] = $value;  $container->add('ID', $value);
 * $item = $container['ID'];   $item = $container->get('ID');
 * isset($container['ID']);    $container->has('ID');
 * unset($container['ID']);    $container->delete('ID');
 * ```
 *
 * Note that unlike a normal array, only `string` identifiers will be accepted
 * by the array syntax (as PSR-11 only permits `string` identifiers); using
 * `int` (or other) identifier types with array syntax will silently fail.
 */
class Container implements ArrayAccess, ContainerInterface
{
    /** @var mixed[] $registry Container entries indexed by identifiers. */
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
     * An entry can be of any type. To define a lazy loaded class, use `class()`
     * or `factory()`.
     *
     * @api
     *
     * @param string $id    Identifier of the entry to set.
     * @param mixed  $value Value of the entry to set.
     *
     * @return $this Fluent interface, allowing multiple calls to be chained.
     */
    public function add(string $id, $value): Container
    {
        $this->registry[$id] = $value;
        return $this;
    }
    
    /**
     * Adds a class definition for lazy loading.
     *
     * After first instantiation, the same instance will be returned by `get()`
     * on each call. If this is not the desired behaviour, you should set the
     * third parameter to `false`.
     *
     * @api
     *
     * @param string $className  The name of the defined class. Using the
     *      `::class` keyword is recommended.
     * @param array  $parameters Values to pass to the class constructor.
     * @param bool   $register   Whether the entry should be registered.
     *
     * @throws InvalidArgumentException The given class name does not exist.
     *
     * @return Definition
     */
    public function define(
        string $className,
        array $parameters = [],
        bool $register = true
    ): Definition {
        return new ClassDefinition($register, $className, ...$parameters);
    }

    /**
     * Deletes an entry from the container.
     *
     * @api
     *
     * @param string $id Identifier of the entry to delete.
     *
     * @return $this Fluent interface, allowing multiple calls to be chained.
     */
    public function delete(string $id): Container
    {
        unset($this->registry[$id]);
        return $this;
    }
    
    /**
     * Use a container entry as a parameter for a lazy loading definition.
     *
     * @api
     *
     * @param string $id Identifier of the entry to reference.
     *
     * @return EntryProxy When an `EntryProxy` is encountered in a parameter
     *      list while resolving a definition it is replaced with the container
     *      entry with the given identifier.
     */
    public function entry(string $id): EntryProxy
    {
        return new EntryProxy($id);
    }

    /**
     * Adds a factory callable for lazy loading.
     *
     * After first instantiation, the same instance will be returned by `get()`
     * on each call. If this is not the desired behaviour, you should set the
     * third parameter to `false`.
     *
     * @api
     *
     * @param callable $callable   The factory callable to use.
     * @param array    $parameters Values to pass to the given callable.
     * @param bool     $register   Whether the entry should be registered.
     *
     * @throws InvalidArgumentException The given class name does not exist.
     *
     * @return Definition
     */
    public function factory(
        callable $callable,
        array $parameters = [],
        bool $register = true
    ): Definition {
        return new FactoryDefinition($register, $callable, ...$parameters);
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
        if (!$entry instanceof ClassDefinition) {
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
        $params = $this->resolveParams($definition->getParameters());
        $cargo = $definition->getCargo();
        $object = ($definition instanceof ClassDefinition)
            ? new $cargo(...$params)
            : call_user_func($definition->getCargo(), ...$params);
        if (!is_object($object)) {
            $msg = 'No object returned from callable';
            throw new UnexpectedValueException($msg);
        }
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
