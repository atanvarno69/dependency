<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */
 
namespace Atanvarno\Dependency\Exception;

/** PSR-11 use block. */
use Psr\Container\NotFoundExceptionInterface;

/** 
 * Atanvarno\Dependency\Exception\NotFoundException
 *
 * Exception thrown when resolving a non-existent entry.
 *
 * Implements `Psr\Container\NotFoundExceptionInterface`.
 */
class NotFoundException extends ContainerException implements
    NotFoundExceptionInterface
{
}
