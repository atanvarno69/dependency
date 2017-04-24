<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Dependency\Test;

/** SPL use block. */
use ReflectionClass;

trait SetProtectedPropertyTrait
{
    private function setProtectedProperty(
        $object,
        string $property,
        $value = null
    ) {
        $class = new ReflectionClass($object);
        $property = $class->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($object, $value);;
    }
}
