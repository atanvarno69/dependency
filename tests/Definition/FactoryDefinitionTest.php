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

/** PHPUnit Utility use block. */
use Atanvarno\PHPUnit\CallProtectedMethodTrait;

/** Package use block. */
use Atanvarno\Dependency\{
    Definition, Definition\Entry, Definition\FactoryDefinition
};

class FactoryDefinitionTest extends TestCase
{
    use CallProtectedMethodTrait;

    private $callable, $container;

    public function setUp()
    {
        $this->callable = function(){return 'test';};
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function testConstructor()
    {
        $result = new FactoryDefinition($this->callable, [], true);
        $this->assertInstanceOf(Definition::class, $result);
        $this->assertAttributeSame($this->callable, 'callable', $result);
        $this->assertAttributeSame([], 'parameters', $result);
        $this->assertAttributeSame(true, 'register', $result);
    }

    public function testConstructorWithFalseRegister()
    {
        $result = new FactoryDefinition($this->callable, [], false);
        $this->assertAttributeSame(false, 'register', $result);
    }

    public function testFactoryMethod()
    {
        $definition = new FactoryDefinition($this->callable, [], true);
        $container = $this->createMock(ContainerInterface::class);
        $result = $this->callProtectedMethod(
            $definition,
            'factoryMethod',
            [$container]
        );
        $this->assertSame('test', $result);
    }

    public function testFactoryMethodWithResolvableCallable()
    {
        $callable = [new Entry('entryID'), 'testFunction'];
        $definition = new FactoryDefinition($callable, [], true);
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('get')
            ->with($this->stringContains('entryID'))
            ->willReturn(new TestCallableMock);
        $result = $this->callProtectedMethod(
            $definition,
            'factoryMethod',
            [$container]
        );
        $this->assertSame('test', $result);
    }
}

class TestCallableMock
{
    public function testFunction()
    {
        return 'test';
    }
}
