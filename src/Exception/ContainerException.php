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
 * Atanvarno\Dependency\Exception\ContainerException
 *
 * @internal Exception implementing `ContainerExceptionInterface`.
 */
class ContainerException extends Exception implements
    ContainerExceptionInterface
{
    
}
