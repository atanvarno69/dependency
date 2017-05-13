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
use Atanvarno\Dependency\Definition\Entry;
use Atanvarno\Dependency\Exception\{
    ConfigurationException, ContainerException, NotFoundException
};

/**
 * Atanvarno\Dependency\Container
 *
 * A basic container implementing PSR-11 `ContainerInterface`.
 *
 * The container may contain and return any PHP type.
 *
 * @api
 */
class Container implements ArrayAccess, ContainerInterface
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
     */
    private $cache, $cacheKey, $children, $definitions, $delegate, $registry;
    
    /**
     * Builds a `Container` instance.
     *
     * @param Definition[]              $definitions Entry definitions.
     * @param CacheInterface|Entry|null $cache       PSR-16 cache.
     * @param string                    $cacheKey    Cache key for cached data.
     *
     * @throws ConfigurationException Invalid definitions array.
     * @throws ConfigurationException Invalid cache key.
     */
    public function __construct(
        array $definitions = [],
        $cache = null,
        string $cacheKey = 'container'
    ) {
        foreach ($definitions as $definition) {
            if (!$definition instanceof Definition) {
                $msg = 'Definitions array must contain only Definition objects';
                throw new ConfigurationException($msg);
            }
        }
        if (strlen($cacheKey) < 1) {
            $msg = 'Cache key must be a non-zero length string';
            throw new ConfigurationException($msg);
        }
        $this->definitions = $definitions;
        $this->cache = $this->resolveCache($cache);
        $this->cacheKey = $cacheKey;
        $this->registry = $this->resolveRegistry();
        $this->children = [];
        $this->delegate = null;
        $this->set('container', $this);
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
    public function addChild(ContainerInterface $child): Container
    {
        $this->children[] = $child;
        return $this;
    }

    /**
     * Clear the container's cached values.
     *
     * If no cache has been set, this method will do nothing.
     *
     * @throws ContainerException Unable to clear cache.
     *
     * @return $this Fluent interface, allowing multiple calls to be chained.
     */
    public function clearCache(): Container
    {
        if (isset($this->cache)) {
            $value = $this->cache->delete($this->cacheKey);
            if ($value === false) {
                throw new ContainerException('Unable to clear cache');
            }
        }
        return $this;
    }

    /**
     * Deletes an entry from the container.
     *
     * @param string $id Entry to delete.
     *
     * @return $this Fluent interface, allowing multiple calls to be chained.
     */
    public function delete(string $id): Container
    {
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
     * @throws TypeError          Given $id is not a string.
     * @throws NotFoundException  No entry was found for this identifier.
     * @throws ContainerException Error while retrieving the entry.
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
     * @throws TypeError Given $id is not a string.
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
     * @throws NotFoundException  No entry was found for this offset.
     * @throws ContainerException Error while retrieving the offset.
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
     * @return $this Fluent interface, allowing multiple calls to be chained.
     */
    public function set(string $id, $value): Container
    {
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
    public function setDelegate(ContainerInterface $delegate): Container
    {
        $this->delegate = $delegate;
        return $this;
    }

    /**
     * Sets the entry identifier for the container itself.
     *
     * When instantiated, the container self identifier will be 'container'.
     * Use this method when a different identifier is required.
     *
     * @param string $id Identifier to assign to the container.
     *
     * @throws ConfigurationException Given identifier is an empty string.
     *
     * @return $this Fluent interface, allowing multiple calls to be chained.
     */
    public function setSelfId(string $id): Container
    {
        if (strlen($id) < 1) {
            $msg = 'Self ID must be a non-zero length string';
            throw new ConfigurationException($msg);
        }
        $currentKey = array_search($this, $this->registry);
        $this->delete($currentKey);
        $this->set($id, $this);
        return $this;
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
        return sprintf(
            'Argument %u passed to %s must be of the type %s, %s given',
            $arg,
            $method,
            $expected,
            gettype($actual)
        );
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
        if (!$cache instanceof Entry && !is_string($cache)) {
            $msg = 'Cache must be a PSR-16 cache, a container key or null';
            throw new ConfigurationException($msg);
        }
        try {
            $return = $this->get((string) $cache);
        } catch (ContainerExceptionInterface $caught) {
            $msg = sprintf(
                'Error resolving given cache, container key "%s": %s',
                (string) $cache,
                $caught->getMessage()
            );
            throw new ContainerException($msg, $caught->getCode(), $caught);
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
            throw new ConfigurationException($msg);
        }
        return $return;
    }
    
    private function resolveRegistry(): array
    {
        if (!isset($this->cache)) {
            return [];
        }
        try {
            $return = $this->cache->get($this->cacheKey, []);
        } catch (CacheException $caught) {
            $msg = sprintf(
                'Error resolving given cache key "%s": %s',
                $this->cacheKey,
                $caught->getMessage()
            );
            throw new ConfigurationException(
                $msg, $caught->getCode(), $caught
            );
        }
        if (!is_array($return)) {
            $type = (is_object($return))
                ? get_class($return)
                : gettype($return);
            $msg = sprintf(
                'Cache entry "%s" resolves to a %s, array expected',
                $this->cacheKey,
                $type
            );
            throw new ConfigurationException($msg);
        }
        return $return;
    }
    
    private function updateCache()
    {
        if (isset($this->cache)) {
            $this->cache->set($this->cacheKey, $this->registry);
        }
    }
}
