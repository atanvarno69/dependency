<?php
/**
 * @package   Atan\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atan\Dependency\Test;

/** SPL use block. */
use TypeError;

/** PHPUnit use block */
use PHPUnit\Framework\TestCase;

/** PSR-11 use block. */
use Psr\Container\{
    ContainerInterface, ContainerExceptionInterface, NotFoundExceptionInterface
};

/** Package use block. */
use Atan\Dependency\{
    Container, ClassDefinition, EntryProxy, FactoryDefinition
};

class ContainerTest extends TestCase
{
    /**
     * @var callable $callable
     * @var Container $container
     */
    private $callable, $container;

    public function setUp()
    {
        $this->callable = function (...$params) {
            return new Container(...$params);
        };
        $this->container = new Container();
    }

    public function testConstructorDefaults()
    {
        $expected = ['container' => $this->container];
        $this->assertInstanceOf(ContainerInterface::class, $this->container);
        $this->assertAttributeEquals($expected, 'registry', $this->container);
    }

    public function testConstructorWithContainerName()
    {
        $container = new Container('name');
        $expected = ['name' => $container];
        $this->assertAttributeEquals($expected, 'registry', $container);
    }

    public function testAdd()
    {
        $result = $this->container->add('one', 1)->add('two', 2);
        $expected = ['container' => $this->container, 'one' => 1, 'two' => 2];
        $this->assertAttributeEquals($expected, 'registry', $this->container);
        $this->assertSame($this->container, $result);
    }

    public function testDefineDefaults()
    {
        $result = $this->container->define(Container::class);
        $this->assertInstanceOf(ClassDefinition::class, $result);
        $this->assertSame(Container::class, $result->getCargo());
        $this->assertSame([], $result->getMethods());
        $this->assertSame([], $result->getParameters());
        $this->assertTrue($result->getRegister());
    }

    public function testDefineWithParameters()
    {
        $result = $this->container->define(Container::class, ['name']);
        $this->assertSame(['name'], $result->getParameters());
    }

    public function testDefineWithFalseRegister()
    {
        $result = $this->container->define(Container::class, [], false);
        $this->assertFalse($result->getRegister());
    }

    public function testDelete()
    {
        $result = $this->container->add('one', 1)->add('two', 2)->delete('one');
        $expected = [
            'two' => 2,
            'container' => $this->container
        ];
        $this->assertAttributeEquals($expected, 'registry', $this->container);
        $this->assertSame($this->container, $result);
    }

    public function testEntry()
    {
        $result = $this->container->entry('container');
        $this->assertInstanceOf(EntryProxy::class, $result);
        $this->assertSame('container', (string) $result);
    }

    public function testFactoryDefaults()
    {
        $result = $this->container->factory($this->callable);
        $this->assertInstanceOf(FactoryDefinition::class, $result);
        $this->assertSame($this->callable, $result->getCargo());
        $this->assertSame([], $result->getMethods());
        $this->assertSame([], $result->getParameters());
        $this->assertTrue($result->getRegister());
    }

    public function testFactoryWithParameters()
    {
        $result = $this->container->factory($this->callable, ['name']);
        $this->assertSame(['name'], $result->getParameters());
    }

    public function testFactoryWithFalseRegister()
    {
        $result = $this->container->factory($this->callable, [], false);
        $this->assertFalse($result->getRegister());
    }

    // todo: tests for get method

    public function testHasThrowsTypeErrorForNonStringParam()
    {
        $this->expectException(TypeError::class);
        $this->container->has(1);
    }

    public function testHasReportsTrueForValidEntry()
    {
        $this->container->add('entry', 'value');
        $this->assertTrue($this->container->has('entry'));
    }

    public function testHasReportsFalseForInvalidEntry()
    {
        $this->assertFalse($this->container->has('entry'));
    }

    public function testOffsetExistsReportsFalseForNonString()
    {
        $this->container[1] = 'value';
        $this->assertFalse(isset($this->container[1]));
    }

    public function testOffsetExistsReportsTrueForValidEntry()
    {
        $this->assertTrue(isset($this->container['container']));
    }

    public function testOffsetExistsReportsFalseForVInvalidEntry()
    {
        $this->assertFalse(isset($this->container['not set']));
    }

    public function testOffsetGetReturnsNullForNonString()
    {
        $this->container[1] = 'value';
        $this->assertNull($this->container[1]);
    }

    public function testOffsetGetReturnsValidEntry()
    {
        $this->container['entry'] = 'value';
        $this->assertSame('value', $this->container['entry']);
    }

    public function testOffsetGetReturnsNullForInvalidEntry()
    {
        $this->assertNull($this->container['entry']);
    }

    public function testOffsetSetDoesNothingForNonStringId()
    {
        $this->container[1] = 'value';
        $this->assertAttributeEquals(
            ['container' => $this->container],
            'registry',
            $this->container
        );
    }

    public function testOffsetSetSetsValueWithStringId()
    {
        $this->container['entry'] = 'value';
        $this->assertAttributeEquals(
            ['container' => $this->container, 'entry' => 'value'],
            'registry',
            $this->container
        );
    }

    public function testOffsetUnsetDoesNothingForNonStringId()
    {
        $this->container['entry'] = 'value';
        unset($this->container[1]);
        $this->assertAttributeEquals(
            ['container' => $this->container, 'entry' => 'value'],
            'registry',
            $this->container
        );
    }

    public function testOffsetUnsetDoesNothingForNotSetId()
    {
        $this->container['entry'] = 'value';
        unset($this->container['not set']);
        $this->assertAttributeEquals(
            ['container' => $this->container, 'entry' => 'value'],
            'registry',
            $this->container
        );
    }

    public function testOffsetUnsetDeletesValueForStringId()
    {
        $this->container['entry'] = 'value';
        unset($this->container['entry']);
        $this->assertAttributeEquals(
            ['container' => $this->container],
            'registry',
            $this->container
        );
    }
}
