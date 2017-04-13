<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Dependency\Test\Exception;

/** SPL use block. */
use Exception;

/** PHPUnit use block. */
use PHPUnit\Framework\TestCase;

/** Package use block. */
use Atanvarno\Dependency\Exception\{
    ContainerException, InvalidArgumentException
};

class NotFoundExceptionTest extends TestCase
{   
    public function testContainerExceptionImplementsInterface()
    {
        $exception = new InvalidArgumentException();
        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertInstanceOf(ContainerException::class, $exception);
    }
}
