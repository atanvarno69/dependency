<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */
 
namespace Atanvarno\Dependency\Exception;

/** SPL use block */
use Exception;

/** PSR-11 use block. */
use Psr\Container\ContainerExceptionInterface;

/**
 * Atanvarno\Dependency\Exception\RuntimeException
 *
 * Exception thrown when the container encounters an issue at runtime.
 */
class RuntimeException extends Exception implements
    ContainerExceptionInterface
{
}
