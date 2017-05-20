<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Dependency\Test\Exception;

/** PSR-11 use block. */
use Psr\Container\NotFoundExceptionInterface;

/** PHPUnit use block. */
use PHPUnit\Framework\TestCase;

/** Package use block. */
use Atanvarno\Dependency\Exception\NotFoundException;

class NotFoundExceptionTest extends TestCase
{
    private $exception;

    public function setUp()
    {
        $this->exception = new NotFoundException();
    }

    public function testIsThrowable()
    {
        $this->expectException(NotFoundExceptionInterface::class);
        throw $this->exception;
    }
}
