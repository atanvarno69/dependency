<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2021 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

declare(strict_types = 1);

namespace Atanvarno\Dependency\Exception;

use Exception;
use Psr\Container\ContainerExceptionInterface as ContainerException;

/**
 * Exception thrown when an unexpected value is encountered.
 */
class UnexpectedValueException extends Exception implements ContainerException
{}
