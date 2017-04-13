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
use Atanvarno\Dependency\Definition\{
    AbstractDefinition,
    Entry, FactoryDefinition, ObjectDefinition, ValueDefinition
};

class AbstractDefinitionTest extends TestCase
{
    private $definition;
    
    public function setUp()
    {
        $this->definition = $this->getMockBuilder(AbstractDefinition::class)
            ->setConstructorArgs([true])
            ->getMockForAbstractClass();
    }
    
    public function testConstructorRegistrationWithTrue()
    {
        $this->assertAttributeEquals([], 'actions', $this->definition);
        $this->assertAttributeEquals(true, 'register', $this->definition);
    }
    
    public function testConstructorRegistrationWithFalse()
    {
        $definition = $this->getMockBuilder(AbstractDefinition::class)
            ->setConstructorArgs([false])
            ->getMockForAbstractClass();
        $this->assertAttributeEquals([], 'actions', $definition);
        $this->assertAttributeEquals(false, 'register', $definition);
    }
    
    public function testIsRegisteredWithTrue()
    {
        $this->assertTrue($this->definition->isRegistered());
    }
    
    public function testIsRegisteredWithFalse()
    {
        $definition = $this->getMockBuilder(AbstractDefinition::class)
            ->setConstructorArgs([false])
            ->getMockForAbstractClass();
        $this->assertFalse($definition->isRegistered());
    }
    
    public function testMethodDefault()
    {
        $result = $this->definition->method('test');
        $this->assertSame($this->definition, $result);
        $expected = [
            ['method', 'test', []],
        ];
        $this->assertAttributeEquals($expected, 'actions', $result);
    }
    
    public function testMethodWithParameters()
    {
        $result = $this->definition->method('test', ['A', 'B', 'C']);
        $this->assertSame($this->definition, $result);
        $expected = [
            ['method', 'test', ['A', 'B', 'C']],
        ];
        $this->assertAttributeEquals($expected, 'actions', $result);
    }
    
    public function testPropertyDefault()
    {
        $result = $this->definition->property('test');
        $this->assertSame($this->definition, $result);
        $expected = [
            ['property', 'test', []],
        ];
        $this->assertAttributeEquals($expected, 'actions', $result);
    }
    
    public function testPropertyWithParameters()
    {
        $result = $this->definition->property('test', ['A', 'B', 'C']);
        $this->assertSame($this->definition, $result);
        $expected = [
            ['property', 'test', ['A', 'B', 'C']],
        ];
        $this->assertAttributeEquals($expected, 'actions', $result);
    }
    
    public function testBuildSimple()
    {
        $container = $this->createMock(ContainerInterface::class);
        $this->definition
            ->expects($this->any())->method('factoryMethod')
            ->willReturn('A');
        $result = $this->definition->build($container);
        $this->assertSame('A', $result);
    }
}
