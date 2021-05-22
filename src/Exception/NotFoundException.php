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
use Psr\Container\NotFoundExceptionInterface as NotFound;

/** 
 * Exception thrown when resolving a non-existent entry.
 */
class NotFoundException extends Exception implements NotFound
{}
