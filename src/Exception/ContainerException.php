<?php
/**
 * ContainerException class file.
 *
 * @package   Atan\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */
 
namespace Atan\Dependency\Exception;

/** SPL use block */
use Exception;

/** PSR-11 use block. */
use Psr\Container\ContainerExceptionInterface;

/** ContainerException class */
class ContainerException extends Exception implements
    ContainerExceptionInterface
{
    
}
