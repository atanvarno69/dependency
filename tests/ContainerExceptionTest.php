<?php
/**
 * ContainerExceptionTest class file.
 *
 * @package   Atan\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atan\Dependency\Test;

/** SPL use block. */
use Exception;

/** PSR-11 use block. */
use Psr\Container\ContainerExceptionInterface;

/** PHPUnit use block. */
use PHPUnit\Framework\TestCase;

/** Package use block. */
use Atan\Dependency\Exception\ContainerException;

class ContainerExceptionTest extends TestCase
{   
    public function testContainerExceptionImplementsInterface()
    {
        $exception = new ContainerException();
        $this->assertInstanceOf(ContainerExceptionInterface::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
    }
}
