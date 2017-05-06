<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Dependency;

/** SPL use block. */
use ArrayAccess, Throwable, TypeError;

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
     * @param ContainerInterface $child
     *
     * @return $this Fluent interface, allowing multiple calls to be chained.
     */
    public function addChild(ContainerInterface $child)
    {
        $this->children[] = $child;
        return $this;
    }

    /**
     * Deletes an entry from the container.
     *
     * @param string $id Entry to delete.
     *
     * @return $this Fluent interface, allowing multiple calls to be chained.
     */
    public function delete(string $id)
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
     * @throws TypeError Given $id is not a string.
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

    /** @inheritdoc */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /** @inheritdoc */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /** @inheritdoc */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /** @inheritdoc */
    public function offsetUnset($offset)
    {
        $this->delete($offset);
    }

    /**
     * Adds an entry to the container.
     *
     * @param string $id    Entry ID to add.
     * @param mixed  $value Entry value.
     *
     * @return $this Fluent interface, allowing multiple calls to be chained.
     */
    public function set(string $id, $value)
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
     * Sets a delegate container.
     *
     * Dependency resolution will be delegated to the given container.
     *
     * @param ContainerInterface $delegate Delegate container.
     *
     * @return $this Fluent interface, allowing multiple calls to be chained.
     */
    public function setDelegate(ContainerInterface $delegate)
    {
        $this->delegate = $delegate;
        return $this;
    }

    /**
     * Sets the entry ID for the container itself.
     *
     * When instantiated, the container self ID will be 'container'. Use this
     * method when a different ID is required.
     *
     * @param string $id New self ID.
     *
     * @throws ConfigurationException Given ID is an empty string.
     *
     * @return $this Fluent interface, allowing multiple calls to be chained.
     */
    public function setSelfId(string $id)
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
        try {
            $return = $definition->build($this->delegate ?? $this);
        } catch (Throwable $caught) {
            $msg = sprintf('Could not build "%s" from definition', $id);
            throw new ContainerException($msg, $caught->getCode(), $caught);
        }
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
