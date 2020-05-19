<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Dependency;

/** SPL use block. */
use ArrayAccess, TypeError;

/** PSR-11 use block. */
use Psr\Container\{ContainerInterface, ContainerExceptionInterface};

/** PSR-16 use block. */
use Psr\SimpleCache\{CacheInterface, CacheException};

/** Package use block. */
use Atanvarno\Dependency\Definition\{Entry, ValueDefinition};
use Atanvarno\Dependency\Exception\{
    ConfigurationException,
    InvalidArgumentException,
    NotFoundException,
    RuntimeException,
    UnexpectedValueException
};

/**
 * Atanvarno\Dependency\Container
 *
 * A basic container implementing PSR-11 `ContainerInterface`.
 *
 * The container may contain and return any PHP type. These container entries
 * are associated with a unique user-defined `string` identifier.
 *
 * By default, a `Container` instance will associate itself with the identifier
 * `container`. Use the method `setSelfId()` to change this value.
 *
 * `Container` implements PSR-11 `ContainerInterface` and thus uses the method
 * `get()` is used to retrieve an entry and the method `has()` is used to check
 * if an entry exists.
 *
 * Entries are added using the `set()` method. This accepts any value. To
 * define an entry that will be lazy loaded (only instantiated when `get()` is
 * first called), pass `set()` a `Definition` instance. The helper functions
 * `factory()`, `object()` and `value()` can be used to provide a `Definition`
 * instance for `set()`.
 *
 * Entries are removed using the `delete()` method.
 *
 * `Container` implements the Delegate Lookup Feature. To use a `Container`
 * instance as a composite container, use the `addChild()` method. To use a
 * `Container` instance as a child container, add it to the composite container
 * and use the `setDelegate()` method to register the composite container for
 * dependency resolution.
 *
 * As `Container` implements `ArrayAccess`, it can be used with array syntax:
 * ```php
 * # Array syntax              # Alias of
 * $container['ID'] = $value;  $container->add('ID', $value);
 * $item = $container['ID'];   $item = $container->get('ID');
 * isset($container['ID']);    $container->has('ID');
 * unset($container['ID']);    $container->delete('ID');
 * ```
 *
 * Unlike a normal array, non-`string` offsets will be accepted by the array
 * syntax. However, as PSR-11 only permits `string` identifiers, `int` (or
 * other) offset types used with array syntax will be silently cast to `string`.
 *
 * `Container` can cache its contained entries. To use caching, provide a
 * PSR-16 `CacheInterface` instance to the constructor, optionally with a key
 * to use for its cache entry.
 *
 * @api
 */
class Container implements ArrayAccess, DelegateContainer
{
    /**
     * @internal Class properties.
     *
     * @var CacheInterface|null     $cache       PSR-16 cache.
     * @var string                  $cacheKey    Cache key.
     * @var ContainerInterface[]    $children    Subordinate containers.
     * @var Definition[]            $definitions Definitions array.
     * @var ContainerInterface|null $delegate    Delegate container.
     * @var mixed[]                 $registry    Registered values.
     * @var string                  $selfId      Self identifier.
     */
    private
        $cache,
        $cacheKey,
        $children,
        $definitions,
        $delegate,
        $registry,
        $selfId;
    
    /**
     * Builds a `Container` instance.
     *
     * Optionally accepts an array of `Definition` instances indexed by entry
     * identifiers. These will be added to the container. This array can be
     * returned from a configuration file.
     *
     * Optionally accepts a PSR-16 `CacheInterface` instance; or an `Entry`
     * instance that refers to a PSR-16 cache instance. Thus, a cache can be
     * gotten from the provided definitions array. If this is the case, the
     * container will be updated with the values from the cache.
     *
     * Optionally accepts a cache key to store the container's data.
     *
     * @param Definition[]              $definitions Entry definitions indexed
     *      by identifiers.
     * @param CacheInterface|Entry|null $cache       PSR-16 cache.
     * @param string                    $cacheKey    Cache key for cached data.
     *
     * @throws ConfigurationException   Definitions array does not contain only
     *      `Definition` instances.
     * @throws InvalidArgumentException Given cache is not a valid type.
     * @throws InvalidArgumentException Given cache key is an empty string.
     * @throws RuntimeException         Error building cache instance.
     * @throws RuntimeException         Error getting data from cache.
     * @throws UnexpectedValueException Built cache instance is not a PSR-16
     *      cache.
     * @throws UnexpectedValueException Invalid data returned from cache.
     *
     */
    public function __construct(
        array $definitions = [],
        $cache = null,
        string $cacheKey = 'container'
    ) {
        $this->zeroLengthStringCheck($cacheKey, 'cache key');
        $this->processDefinitions($definitions);
        $this->cache = $this->resolveCache($cache);
        $this->cacheKey = $cacheKey;
        $this->processRegistry();
        $this->children = [];
        $this->delegate = null;
        $this->setSelfId('container');
    }

    /**
     * Adds a child container.
     *
     * This will make the container act as a composite container.
     *
     * @param ContainerInterface $child Child container to add.
     *
     * @return $this Fluent interface, allowing multiple calls to be chained.
     */
    public function addChild(DelegateContainer $child): DelegateContainer
    {
        $this->children[] = $child;
        $child->setDelegate($this);
        return $this;
    }

    /**
     * Clear the container's cached values.
     *
     * If no cache has been set, this method will do nothing.
     *
     * @throws RuntimeException Unable to clear cache.
     *
     * @return $this Fluent interface, allowing multiple calls to be chained.
     */
    public function clearCache(): Container
    {
        if (isset($this->cache)) {
            $value = $this->cache->delete($this->cacheKey);
            if ($value === false) {
                throw new RuntimeException('Unable to clear cache');
            }
        }
        return $this;
    }

    /**
     * Deletes an entry from the container.
     *
     * @param string $id Entry to delete.
     *
     * @throws InvalidArgumentException Given identifier is an empty string.
     *
     * @return $this Fluent interface, allowing multiple calls to be chained.
     */
    public function delete(string $id): Container
    {
        $this->zeroLengthStringCheck($id, 'identifier');
        if (isset($this->registry[$id])) {
            unset($this->registry[$id]);
            $this->updateCache();
        }
        if (isset($this->definitions[$id])) {
            unset($this->definitions[$id]);
        }
        return $this;
    }

    /**
     * Retrieves an entry from the container.
     *
     * @param string $id Entry to retrieve.
     *
     * @throws TypeError                Given $id is not a string.
     * @throws InvalidArgumentException Given identifier is an empty string.
     * @throws NotFoundException        No entry was found for this identifier.
     * @throws RuntimeException         Error while retrieving the entry.
     *
     * @return mixed The entry.
     */
    public function get($id)
    {
        if (!is_string($id)) {
            throw new TypeError(
                $this->getBcTypeErrorMessage(1, __METHOD__, 'string', $id)
            );
        }
        $this->zeroLengthStringCheck($id, 'identifier');
        return (!empty($this->children))
            ? $this->compositeGet($id)
            : $this->selfGet($id);
    }

    /**
     * Checks if an entry exists.
     *
     * If the container is acting as a composite container (it has children),
     * this method will check for a matching entry in itself first, then in its
     * children.
     *
     * @param string $id Entry to check for.
     *
     * @throws TypeError                Given identifier is not a string.
     * @throws InvalidArgumentException Given identifier is an empty string.
     *
     * @return bool `true` if the entry exists, `false` otherwise.
     */
    public function has($id)
    {
        if (!is_string($id)) {
            throw new TypeError(
                $this->getBcTypeErrorMessage(1, __METHOD__, 'string', $id)
            );
        }
        $this->zeroLengthStringCheck($id, 'identifier');
        return (!empty($this->children))
            ? $this->compositeHas($id)
            : $this->selfHas($id);
    }

    /**
     * Checks if an offset (entry) exists.
     *
     * `ArrayAccess` method executed when using `isset()` and `empty()` on a
     * `Container` object using array syntax.
     *
     * Calls `has()` internally.
     *
     * @param mixed $offset Offset (entry) to check for. The value will be
     *      cast to `string`.
     *
     * @throws InvalidArgumentException Given offset resolves to an empty
     *      string.
     *
     * @return bool `true` if the offset (entry) exists, `false` otherwise.
     */
    public function offsetExists($offset): bool
    {
        return $this->has((string) $offset);
    }

    /**
     * Retrieves an offset (entry) from the container.
     *
     * `ArrayAccess` method executed when using array syntax on a `Container`
     * object.
     *
     * Calls `get()` internally.
     *
     * @param mixed $offset Offset (entry) to retrieve. The value will be cast
     *      to `string`.
     *
     * @throws InvalidArgumentException Given offset resolves to an empty
     *      string.
     * @throws NotFoundException        No entry was found for this offset.
     * @throws RuntimeException         Error while retrieving the offset.
     *
     * @return mixed The entry.
     */
    public function offsetGet($offset)
    {
        return $this->get((string) $offset);
    }

    /**
     * Assigns a value to the specified offset (identifier).
     *
     * `ArrayAccess` method executed when using array syntax on a `Container`
     * object.
     *
     * Calls `set()` internally.
     *
     * @param mixed  $offset Offset (identifier) to add. The value will be cast
     *      to `string`.
     * @param mixed  $value  Entry value.
     *
     * @throws InvalidArgumentException Given offset resolves to an empty
     *      string.
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->set((string) $offset, $value);
    }

    /**
     * Unsets (deletes) an offset (entry) from the container.
     *
     * `ArrayAccess` method executed when using `unset()` on a `Container`
     * object using array syntax.
     *
     * Calls `delete()` internally.
     *
     * @param mixed $offset Offset (entry) to unset (delete). The value will be
     *      cast to `string`.
     *
     * @throws InvalidArgumentException Given offset resolves to an empty
     *      string.
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->delete((string) $offset);
    }

    /**
     * Assigns a value to the specified identifier.
     *
     * @param string $id    Identifier to assign.
     * @param mixed  $value Entry value.
     *
     * @throws InvalidArgumentException Given identifier is an empty string.
     *
     * @return $this Fluent interface, allowing multiple calls to be chained.
     */
    public function set(string $id, $value): Container
    {
        $this->zeroLengthStringCheck($id, 'identifier');
        $this->delete($id);
        if ($value instanceof Definition) {
            $this->definitions[$id] = $value;
            return $this;
        }
        $this->registry[$id] = $value;
        $this->updateCache();
        return $this;
    }

    /**
     * Sets a container to delegate dependency resolution to.
     *
     * @param ContainerInterface $delegate Delegate container.
     *
     * @return $this Fluent interface, allowing multiple calls to be chained.
     */
    public function setDelegate(DelegateContainer $delegate): DelegateContainer
    {
        $this->delegate = $delegate;
        if (!$delegate->isChild($this)) {
            $delegate->addChild($this);
        }
        return $this;
    }

    /**
     * Sets the entry identifier for the container itself.
     *
     * When instantiated, the container self identifier will be 'container'.
     * Use this method when a different identifier is required.
     *
     * @param string $id Identifier to assign to the container itself.
     *
     * @throws InvalidArgumentException Given identifier is an empty string.
     *
     * @return $this Fluent interface, allowing multiple calls to be chained.
     */
    public function setSelfId(string $id): Container
    {
        $this->zeroLengthStringCheck($id, 'identifier');
        if (isset($this->selfId)) {
            $this->delete($this->selfId);
        }
        $this->selfId = $id;
        $this->registry[$this->selfId] = $this;
        return $this;
    }

    /**
     * @internal
     */
    public function isChild(Container $container): bool {
        foreach($this->children as $child) {
            if ($child === $container) {
                return true;
            }
        }
        return false;
    }

    private function compositeGet(string $id)
    {
        if ($this->selfHas($id)) {
            return $this->selfGet($id);
        }
        $has = false;
        foreach ($this->children as $key => $child) {
            if ($child->has($id)) {
                $has = $key;
                break;
            }
        }
        if ($has === false) {
            $msg = sprintf('Container entry "%s" not found', $id);
            throw new NotFoundException($msg);
        }
        /** @var ContainerInterface $child */
        $child = $this->children[$has];
        return $child->get($id);
    }
    
    private function compositeHas(string $id): bool
    {
        if ($this->selfHas($id)) {
            return true;
        }
        foreach ($this->children as $child) {
            if ($child->has($id)) {
                return true;
            }
        }
        return false;
    }
    
    private function getBcTypeErrorMessage(
        int $arg,
        string $method,
        string $expected,
        $actual
    ): string {
        $msg = 'Argument %u passed to %s must be of the type %s, %s given';
        return sprintf($msg, $arg, $method, $expected, gettype($actual));
    }

    private function processDefinitions(array $definitions)
    {
        foreach ($definitions as $id => $definition) {
            if (!$definition instanceof Definition) {
                $msg = 'Definitions array must contain only Definition objects';
                throw new ConfigurationException($msg);
            }
            if ($definition instanceof ValueDefinition) {
                $this->registry[$id] = $definition->build($this);
            } else {
                $this->definitions[$id] = $definition;
            }
        }
    }

    private function processRegistry()
    {
        if (!isset($this->cache)) {
            return;
        }
        try {
            $cachedRegistry = $this->cache->get($this->cacheKey, []);
        } catch (CacheException $caught) {
            $msg = sprintf(
                'Error resolving given cache key "%s": %s',
                $this->cacheKey,
                $caught->getMessage()
            );
            throw new RuntimeException(
                $msg, $caught->getCode(), $caught
            );
        }
        if (!is_array($cachedRegistry)) {
            $type = (is_object($cachedRegistry))
                ? get_class($cachedRegistry)
                : gettype($cachedRegistry);
            $msg = sprintf(
                'Cache entry "%s" resolves to a %s, array expected',
                $this->cacheKey,
                $type
            );
            throw new UnexpectedValueException($msg);
        }
        foreach ($cachedRegistry as $id => $value) {
            $this->registry[$id] = $value;
        }
        if ($this->registry != $cachedRegistry) {
            $this->updateCache();
        }
    }
    
    private function selfGet(string $id)
    {
        if (!$this->selfHas($id)) {
            $msg = sprintf('Container entry "%s" not found', $id);
            throw new NotFoundException($msg);
        }
        if (isset($this->registry[$id])) {
            return $this->registry[$id];
        }
        /** @var Definition $definition */
        $definition = $this->definitions[$id];
        $return = $definition->build($this->delegate ?? $this);
        if ($definition->isRegistered()) {
            $this->registry[$id] = $return;
            $this->updateCache();
        }
        return $return;
    }
    
    private function selfHas(string $id): bool
    {
        if (isset($this->registry[$id])) {
            return true;
        }
        if (isset($this->definitions[$id])) {
            return true;
        }
        return false;
    }
    
    private function resolveCache($cache)
    {
        if ($cache instanceof CacheInterface || is_null($cache)) {
            return $cache;
        }
        if (!$cache instanceof Entry) {
            $msg = 'Cache must be a PSR-16 cache, a container entry or null';
            throw new InvalidArgumentException($msg);
        }
        try {
            $return = $this->get((string) $cache);
        } catch (ContainerExceptionInterface $caught) {
            $msg = sprintf(
                'Error resolving given cache, container key "%s": %s',
                (string) $cache,
                $caught->getMessage()
            );
            throw new RuntimeException($msg, $caught->getCode(), $caught);
        }
        if (!$return instanceof CacheInterface) {
            $type = (is_object($return))
                ? get_class($return)
                : gettype($return);
            $msg = sprintf(
                'Entry "%s" resolves to a %s, PSR-16 CacheInterface expected',
                (string) $cache,
                $type
            );
            throw new UnexpectedValueException($msg);
        }
        return $return;
    }
    
    private function updateCache()
    {
        if (isset($this->cache)) {
            $registry = $this->registry;
            if (isset($this->selfId)) {
                unset($registry[$this->selfId]);
            }
            $this->cache->set($this->cacheKey, $registry);
        }
    }

    private function zeroLengthStringCheck(string $string, string $name)
    {
        if (strlen($string) < 1) {
            $msg = sprintf('Given %s is an empty string', $name);
            throw new InvalidArgumentException($msg);
        }
    }
}
