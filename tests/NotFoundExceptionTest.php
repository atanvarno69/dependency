<?php
/**
 * NotFoundExceptionTest class file.
 *
 * @package   Atan\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atan\Dependency\Test;

/** PSR-11 use block. */
use Psr\Container\NotFoundExceptionInterface;

/** PHPUnit use block. */
use PHPUnit\Framework\TestCase;

/** Package use block. */
use Atan\Dependency\Exception\{
    ContainerException, NotFoundException
};

class NotFoundExceptionTest extends TestCase
{   
    public function testContainerExceptionImplementsInterface()
    {
        $exception = new NotFoundException();
        $this->assertInstanceOf(ContainerException::class, $exception);
        $this->assertInstanceOf(NotFoundExceptionInterface::class, $exception);
    }
}
