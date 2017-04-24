<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Dependency\Test\Definition;

/** PSR-11 use block. */
use Psr\Container\ContainerInterface;

/** PHPUnit use block. */
use PHPUnit\Framework\TestCase;

/** Package use block. */
use Atanvarno\Dependency\Test\CallProtectedMethodTrait;
use Atanvarno\Dependency\Definition;
use Atanvarno\Dependency\Definition\ValueDefinition;

class ValueDefinitionTest extends TestCase
{
    use CallProtectedMethodTrait;

    private $container;

    public function setUp()
    {
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function testConstructor()
    {
        $result = new ValueDefinition('test', true);
        $this->assertInstanceOf(Definition::class, $result);
        $this->assertAttributeSame('test', 'value', $result);
        $this->assertAttributeSame(true, 'register', $result);
    }

    public function testConstructorWithFalseRegister()
    {
        $result = new ValueDefinition('test', false);
        $this->assertAttributeSame(false, 'register', $result);
    }

    public function testFactoryMethod()
    {
        $definition = new ValueDefinition('test', true);
        $container = $this->createMock(ContainerInterface::class);
        $result = $this->callProtectedMethod(
            $definition,
            'factoryMethod',
            [$container]
        );
        $this->assertSame('test', $result);
    }
}
