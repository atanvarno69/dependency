<?php
/**
 * NotFoundException class file.
 *
 * @package   Atan\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */
 
namespace Atan\Dependency\Exception;

/** PSR-11 use block. */
use Psr\Container\NotFoundExceptionInterface;

/** NotFoundException class */
class NotFoundException extends ContainerException implements
    NotFoundExceptionInterface
{
    
}
