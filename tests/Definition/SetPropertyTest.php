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
    Entry, InstanceAction, SetProperty
};
use Atanvarno\Dependency\Exception\ConfigurationException;

class SetPropertyTest extends TestCase
{
    private $container;

    public function setUp()
    {
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function testConstructor()
    {
        $result = new SetProperty('propertyName', 'test');
        $this->assertInstanceOf(InstanceAction::class, $result);
        $this->assertAttributeSame('propertyName', 'name', $result);
        $this->assertAttributeSame('test', 'value', $result);
    }

    public function testInvoke()
    {
        $object = new TestClass();
        $setProperty = new SetProperty('value', 'test');
        $result = $setProperty($object, $this->container);
        $this->assertSame($object, $result);
        $this->assertSame('test', $result->value);
    }

    public function testInvokeResolvesValue()
    {
        $this->container
            ->expects($this->once())
            ->method('get')
            ->with($this->stringContains('test'))
            ->willReturn('test string');
        $object = new TestClass();
        $setProperty = new SetProperty('value', new Entry('test'));
        $result = $setProperty($object, $this->container);
        $this->assertSame($object, $result);
        $this->assertSame('test string', $result->value);
    }

    public function testInvokeRejectsNonRealMethodName()
    {
        $object = new TestClass();
        $setProperty = new SetProperty('falseProperty', 'test');
        $this->expectException(ConfigurationException::class);
        $setProperty($object, $this->container);
    }
}

class TestClass
{
    public $value;
}
