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
use ArrayAccess, Throwable, TypeError;

/** PSR-11 use block. */
use Psr\Container\ContainerInterface;

/** Package use block. */
use Atan\Dependency\Exception\{
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
 * Defined classes are added using an instance of the `Definition` class.
 * Defined classes are registered by default, so that after the first call to
 * `get()`, when the object is lazy loaded, `get()` will always return the
 * same instance. If a new instance is required from each call to `get()`,
 * that must be explicitly indicated in the `Definition` class.
 */
class Container implements ArrayAccess, ContainerInterface
{
    /** @var mixed[] $registry */
    private $registry;

    public function define(
        string $className,
        array $parameters = [],
        bool $register = true
    ): Definition {
        return new Definition($className, $parameters, $register);
    }

    public function delete(string $id)
    {
        unset($this->registry[$id]);
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
            $this->set($id, $return);
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

    public function set(string $id, $value)
    {
        $this->registry[$id] = $value;
    }

    public function offsetExists($offset): bool
    {
        if (!is_string($offset)) {
            return false;
        }
        return $this->has($offset);
    }

    public function offsetGet($offset)
    {
        if (!is_string($offset) || !$this->has($offset)) {
            return null;
        }
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        if (is_string($offset)) {
            $this->set($offset, $value);
        }
    }

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
            call_user_func([$object, $method], ...$params);
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
            if (is_string($value) && strpos(':', $value) === 0) {
                $id = substr($value, 1);
                $return[$key] = $this->has($id) ? $this->get($id) : $value;
            }
        }
        return $return;
    }
}
