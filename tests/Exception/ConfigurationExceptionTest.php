<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2021 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

declare(strict_types = 1);

namespace Atanvarno\Dependency\Test\Exception;

use Psr\Container\ContainerExceptionInterface;
use PHPUnit\Framework\TestCase;
use Atanvarno\Dependency\Exception\ConfigurationException;

class ConfigurationExceptionTest extends TestCase
{
    private $exception;

    public function setUp(): void
    {
        $this->exception = new ConfigurationException();
    }

    public function testIsThrowable()
    {
        $this->expectException(ContainerExceptionInterface::class);
        throw $this->exception;
    }
}
