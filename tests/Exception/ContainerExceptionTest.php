<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Dependency\Test\Exception;

/** PSR-11 use block. */
use Psr\Container\ContainerExceptionInterface;

/** PHPUnit use block. */
use PHPUnit\Framework\TestCase;

/** Package use block. */
use Atanvarno\Dependency\Exception\RuntimeException;

class ContainerExceptionTest extends TestCase
{
    private $exception;

    public function setUp()
    {
        $this->exception = new RuntimeException();
    }

    public function testIsThrowable()
    {
        $this->expectException(ContainerExceptionInterface::class);
        throw $this->exception;
    }
}
