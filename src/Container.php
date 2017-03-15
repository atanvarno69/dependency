<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Dependency;

/** SPL use block. */
use ArrayAccess,
    InvalidArgumentException,
    Throwable,
    TypeError,
    UnexpectedValueException;

/** PSR-11 use block. */
use Psr\Container\ContainerInterface;

/** PSR-16 use block. */
use Psr\SimpleCache\CacheInterface;

/** Package use block. */
use Atanvarno\Dependency\Exception\{
    ContainerException, NotFoundException
};

/**
 * Atanvarno\Dependency\Container
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
    /**
     * @var CacheInterface|null $cache    Cache.
     * @var string              $cacheKey Cache key prefix for the container.
     * @var mixed[]             $registry Container entries indexed by
     *      identifiers.
     */
    private $cache, $cacheKey, $registry;

    /**
     * Container constructor.
     *
     * Optionally accepts an array of entries.
     *
     * Optionally accepts an identifier for itself, defaults to 'container'.
     *
     * @api
     *
     * @param string              $id    Identifier for the container's own
     *      entry.
     * @param CacheInterface|null $cache PSR-16 cache for the container to use.
     */
    public function __construct(
        string $id = 'container',
        CacheInterface $cache = null
    ) {
        $this->cache = $cache;
        $this->cacheKey = $id . '.';
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
        if ($this->cache instanceof CacheInterface) {
            $this->cache->set($this->cacheKey . $id, $value);
            return $this;
        }
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
        if ($this->cache instanceof CacheInterface) {
            $this->cache->delete($this->cacheKey . $id);
            return $this;
        }
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
            throw new TypeError(
                $this->getBcTypeErrorMessage(1, __METHOD__, 'string', $id)
            );
        }
        if (!$this->has($id)) {
            throw new NotFoundException("$id not found");
        }
        $entry = ($this->cache instanceof CacheInterface)
            ? $this->cache->get($this->cacheKey . $id)
            : $this->registry[$id];
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
            throw new TypeError(
                $this->getBcTypeErrorMessage(1, __METHOD__, 'string', $id)
            );
        }
        return ($this->cache instanceof CacheInterface)
            ? $this->cache->has($this->cacheKey . $id)
            : array_key_exists($id, $this->registry);
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
    
    private function getBcTypeErrorMessage(
        int $arg,
        string $method,
        string $expected,
        $actual
    ): string {
        return sprintf(
            'Argument %u passed to %s must be of the type %s, %s given',
            $arg,
            $method,
            $expected,
            gettype($actual)
        );
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
                $return[$key] = $this->get((string) $value);
            }
        }
        return $return;
    }
}
