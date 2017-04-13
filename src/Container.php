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
use Psr\SimpleCache\CacheInterface;

/** Package use block. */
use Atanvarno\Dependency\Exception\{
    ContainerException, InvalidArgumentException, NotFoundException
};

/**
 * Atanvarno\Dependency\Container
 *
 * A basic container implementing PSR-11 `ContainerInterface`.
 *
 * The container may contain and return any PHP type.
 */
class Container implements ArrayAccess, ContainerInterface
{
    /**
     * Class properties.
     *
     * @var CacheInterface       $cache       PSR-16 cache.
     * @var string               $cacheKey    Cache key.
     * @var ContainerInterface[] $children    Subordinate containers.
     * @var Definition[]         $definitions Definitions array.
     * @var ContainerInterface   $delegate    Delegate container.
     * @var array                $registry    Registered values.
     */
    private $cache, $cacheKey, $children, $definitions, $delegate, $registry;
    
    /**
     * Builds a `Container` instance.
     *
     * @param Definition[]              $definitions Entry definitions.
     * @param CacheInterface|Entry|null $cache       PSR-16 cache.
     * @param string                    $cacheKey    Cache key for cached data.
     */
    public function __construct(
        array $definitions = [],
        $cache = null,
        string $cacheKey = 'container'
    ) {
        foreach ($definitions as $definition) {
            if (!$definition instanceof Definition) {
                $msg = 'Definitions array must contain only Definition objects'
                throw new InvalidArgumentException($msg);
            }
        }
        if (strlen($cacheKey) < 1) {
            $msg = 'Cache key must be a non-zero length string';
            throw new InvalidArgumentException($msg);
        }
        $this->cache = $this->resolveCache($cache);
        $this->cacheKey = $cacheKey;
        $this->registry = $this->resolveRegistry();
        $this->definitions = $definitions;
        $this->children = [];
        $this->parent = null;
    }
    
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
    
    public function get($id)
    {
        if (!is_string($id)) {
            throw new TypeError(
                $this->getBcTypeErrorMessage(1, __METHOD__, 'string', $id)
            );
        }
        return (!empty($this->children))
            ? $this->compositeGet($id);
            : $this->selfGet($id);
    }
    
    public function has($id)
    {
        if (!is_string($id)) {
            throw new TypeError(
                $this->getBcTypeErrorMessage(1, __METHOD__, 'string', $id)
            );
        }
        return (!empty($this->children))
            ? $this->compositeHas($id);
            : $this->selfHas($id);
    }
    
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
    
    public function addChild(ContainerInterface $child)
    {
        $this->children[] = $child;
        return $this;
    }
    
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }
    
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }
    
    public function offsetSet($offset, $value)
    {
        $this->set($offsetm $value);
    }
    
    public function offsetUnset($offset)
    {
        $this->delete($offset);
    }
    
    public function setDelegate(ContainerInterface $delegate)
    {
        $this->delegate = $delegate;
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
        if ($this->selfHas()) {
            return $true;
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
            throw new InvalidArgumentException(
                $msg, $caught->getCode(), $caught
            );
        }
        if (!$return instanceof CacheInterface) {
            $type = (is_object($return))
                ? get_class($return)
                : get_type($return);
            $msg = sprintf(
                'Entry "%s" resolves to a %s, PSR-16 CacheInterface expected',
                (string) $cache,
                $type
            );
            throw new InvalidArgumentException($msg);
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
            throw new InvalidArgumentException(
                $msg, $caught->getCode(), $caught
            );
        }
        if (!is_array($return) {
            $type = (is_object($return))
                ? get_class($return)
                : get_type($return);
            $msg = sprintf(
                'Cache entry "%s" resolves to a %s, array expected',
                $this->cacheKey,
                $type
            );
            throw new InvalidArgumentException($msg);
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
