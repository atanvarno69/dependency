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
    CallMethod, Entry, InstanceAction
};
use Atanvarno\Dependency\Exception\ConfigurationException;

class CallMethodTest extends TestCase
{
    private $container, $object;

    public function setUp()
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->object = new TestObjectMock();
    }

    public function testConstructor()
    {
        $result = new CallMethod('methodName', ['test']);
        $this->assertInstanceOf(InstanceAction::class, $result);
        $this->assertAttributeSame('methodName', 'name', $result);
        $this->assertAttributeSame(['test'], 'parameters', $result);
    }

    public function testInvoke()
    {
        $callMethod = new CallMethod('set', ['test string']);
        $result = $callMethod($this->object, $this->container);
        $this->assertSame($this->object, $result);
        $this->assertSame('test string', $result->get('test'));
    }

    public function testInvokeResolvesParameter()
    {
        $this->container
            ->expects($this->once())
            ->method('get')
            ->with('test')
            ->willReturn('test string');
        $callMethod = new CallMethod('set', [new Entry('test')]);
        $result = $callMethod($this->object, $this->container);
        $this->assertSame($this->object, $result);
        $this->assertSame('test string', $result->get('test'));
    }

    public function testInvokeRejectsNonRealMethodName()
    {
        $callMethod = new CallMethod('invalidMethodName', ['test', 'test']);
        $this->expectException(ConfigurationException::class);
        $callMethod($this->object, $this->container);
    }
}

class TestObjectMock
{
    public $public;

    private $value;

    public function set($value)
    {
        $this->value = $value;
        return $this;
    }

    public function get()
    {
        return $this->value;
    }
}
