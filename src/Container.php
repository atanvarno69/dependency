<?php
/**
 * Container class file.
 *
 * @package   Atan\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atan\Dependency;

/** SPL use block. */
use ArrayAccess, Throwable;

/** PSR-11 use block. */
use Psr\Container\ContainerInterface;

/** Package use block. */
use Atan\Container\Exception\{
    ContainerException, NotFoundException
};

/**
 * A basic container implementing PSR-11 `ContainerInterface`.
 *
 * The container may contain and return any PHP type. These container entries
 * are associated with a unique user-defined `string` identifier. All entries,
 * except defined classes, are registered, that is a call to `get()` with the
 * identifier will always return the same value.
 *
 * Defined classes are added using an instance of the `Defintion` class. Defined
 * classes are registered by default, so that after the first call to `get()`,
 * when the object is lazy loaded, `get()` will always return the same instance.
 * If a new instance is required from each call to `get()`, that must be
 * explicitly indicated in the `Defintion` class.
 */
class Container implements ArrayAccess, ContainerInterface
{
    /** @var mixed[] $registry */
    private $registry;

    /**
     * Container Constructor.
     *
     * Accepts an array of entries for the `add()` method.
     *
     * @param mixed[] An array of entries indexed by their container identifier.
     */
    public function __construct(array $entries = []) {
        foreach ($entries as $id => $entry) {
            $this->add($id, $entry)
        }
    }

    /**
     * Adds an entry to the container.
     *
     * If the entry is a `Definition` instance, the defined class will be lazy
     * loaded when called (it may be registered on first instantiation, see
     * `Definition` class). Other entries will be registered.
     *
     * @param string $id    Identifier of the entry to add.
     * @param mixed  $entry The `Definition` instance or other value to add.
     *
     * @return void
     */
    public function add(string $id, $entry)
    {
        $this->registry[$id] = $entry;
    }
    
    public function delete(string $id)
    {
        if (array_key_exists($id, $this->registry) {
            unset($this->registry[$id]);
        }
    }

    /** @inheritdoc */
    public function get(string $id)
    {
        if (!$this->has($id)) {
            $msg = 'Cannot find ' . $id;
            throw new NotFoundException($msg);
        }
        $defintion = $this->registry[$id];
        if (!$defintion instanceof Definition) {
            return $defintion;
        }
        $parameters = [];
        $i = 0;
        foreach ($defintion->getParameters() as $parameter) {
            $i++;
            try {
                $parameters[] = $this->resolveParameter($parameter);
            } catch (Throwable as $caught) {
                $msg = "Error getting $id, parameter $i: "
                    . $caught->getMessage();
                throw new ContainerException($msg, $caught->getCode(), $caught);
            }
        }
        $return = new ($defintion->getClassName())(...$parameters);
        if ($this->defintions[$id]['register']) {
            $this->register($id, $return);
        }
        return $return;
    }

    /** @inheritdoc */
    public function has(string $id): bool
    {
        return array_key_exists($id, $this->registry);
    }
    
    /** @inheritdoc */
    public function offsetExists($offset)
    {
        if (!is_string($offset) {
            return false;
        }
        return $this->has($offset);
    }
    
    /** @inheritdoc */
    public function offsetGet($offset)
    {
        if (!is_string($offset) {
            return null;
        }
        return $this->get($offset);
    }
    
    /** @inheritdoc */
    public function offsetSet($offset, $value)
    {
        if (!is_string($offset) {
            return;
        }
        return $this->add($offset, $value);
    }
    
    /** @inheritdoc */
    public function offsetUnset($offset)
    {
        if (!is_string($offset) {
            return;
        }
        return $this->delete($offset);
    }

    private function resolveParameter($parameter)
    {
        $return = $parameter;
        if (is_string($parameter)) {
            if (strpos($parameter, ':') === 0) {
                $id = substr($parameter, 1);
                $return = ($this->has($id)) ? $this->get($id) : $parameter;
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
