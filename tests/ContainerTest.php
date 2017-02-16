<?php
/**
 * ContainerTest class file.
 *
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
    Container, Definition
};

class ContainerTest extends TestCase
{
    public function testConstructorDefaults()
    {
        $container = new Container();
        $expected = ['container' => $container];
        $this->assertInstanceOf(ContainerInterface::class, $container);
        $this->assertAttributeEquals($expected, 'registry', $container);
    }

    public function testConstructorWithEntries()
    {
        $container = new Container([
            'one' => 1,
            'two' => 'two',
            'three' => [3]
        ]);
        $expected = [
            'one' => 1,
            'two' => 'two',
            'three' => [3],
            'container' => $container
        ];
        $this->assertAttributeEquals($expected, 'registry', $container);
    }

    public function testConstructorWithContainerName()
    {
        $container = new Container([], 'name');
        $expected = ['name' => $container];
        $this->assertAttributeEquals($expected, 'registry', $container);
    }

    public function testAdd()
    {
        $container = new Container();
        $container->add('test', 'value');
        $this->assertAttributeEquals(
            ['container' => $container, 'test' => 'value'],
            'registry',
            $container
        );
    }

    public function testDelete()
    {
        $container = new Container([
            'one' => 1,
            'two' => 'two',
            'three' => [3]
        ]);
        $expected = [
            'one' => 1,
            'three' => [3],
            'container' => $container
        ];
        $container->delete('two');
        $this->assertAttributeEquals($expected, 'registry', $container);
    }

    public function testGetThrowsTypeErrorForNonStringParam()
    {
        $container = new Container();
        $this->expectException(TypeError::class);
        $container->get(1);
    }

    public function testGetThrowsNotFoundExceptionForNotSetId()
    {
        $container = new Container();
        $this->expectException(NotFoundExceptionInterface::class);
        $container->get('not set');
    }

    public function testGetReturnsValueForValidId()
    {
        $container = new Container();
        $result = $container->get('container');
        $this->assertSame($container, $result);
    }

    public function testGetReturnsInstanceFromDefinition()
    {
        $container = new Container([
            'testCase' => new Definition(Container::class)
        ]);
        $result = $container->get('testCase');
        $this->assertInstanceOf(Container::class, $result);
    }

    public function testGetResolvesPostInstantiateMethods()
    {
        $definition = (new Definition(Container::class))
            ->method('add', ['id', 'value']);
        $container = new Container(['testCase' => $definition]);
        $result = $container->get('testCase');
        $expected = [
            'container' => $result,
            'id' => 'value',
        ];
        $this->assertAttributeEquals($expected, 'registry', $result);
    }

    public function testGetResolvesConstructorParamsFromContainer()
    {
        $definition = (new Definition(Container::class))
            ->parameter(['testEntry' => ':parentEntry']);
        $container = new Container([
            'testCase' => $definition,
            'parentEntry' => 5,
        ]);
        $result = $container->get('testCase');
        $expected = [
            'container' => $result,
            'testEntry' => 5
        ];
        $this->assertAttributeEquals($expected, 'registry', $result);
    }

    public function testGetThrowsContainerExceptionWhenBuildThrowsThrowable()
    {
        $definition = (new Definition(Container::class))
            ->method('get', [1]);
        $container = new Container(['id' => $definition]);
        $this->expectException(ContainerExceptionInterface::class);
        $container->get('id');
    }

    public function testHasThrowsTypeErrorForNonStringParam()
    {
        $container = new Container();
        $this->expectException(TypeError::class);
        $container->has(1);
    }

    public function testHasReportsTrueForValidEntry()
    {
        $container = new Container(['entry' => 'value']);
        $this->assertTrue($container->has('entry'));
    }

    public function testHasReportsFalseForInvalidEntry()
    {
        $container = new Container();
        $this->assertFalse($container->has('entry'));
    }

    public function testOffsetExistsReportsFalseForNonString()
    {
        $container = new Container(['1' => 'value']);
        $this->assertFalse(isset($container[1]));
    }

    public function testOffsetExistsReportsTrueForValidEntry()
    {
        $container = new Container();
        $this->assertTrue(isset($container['container']));
    }

    public function testOffsetExistsReportsFalseForVInvalidEntry()
    {
        $container = new Container();
        $this->assertFalse(isset($container['not set']));
    }

    public function testOffsetGetReturnsNullForNonString()
    {
        $container = new Container(['1' => 'value']);
        $this->assertNull($container[1]);
    }

    public function testOffsetGetReturnsValidEntry()
    {
        $container = new Container(['entry' => 'value']);
        $this->assertSame('value', $container['entry']);
    }

    public function testOffsetGetReturnsNullForInvalidEntry()
    {
        $container = new Container();
        $this->assertNull($container['entry']);
    }

    public function testOffsetSetDoesNothingForNonStringId()
    {
        $container = new Container();
        $container[1] = 'value';
        $this->assertAttributeEquals(
            ['container' => $container],
            'registry',
            $container
        );
    }

    public function testOffsetSetSetsValueWithStringId()
    {
        $container = new Container();
        $container['entry'] = 'value';
        $this->assertAttributeEquals(
            ['container' => $container, 'entry' => 'value'],
            'registry',
            $container
        );
    }

    public function testOffsetUnsetDoesNothingForNonStringId()
    {
        $container = new Container(['1' => 'value']);
        unset($container[1]);
        $this->assertAttributeEquals(
            ['container' => $container, 1 => 'value'],
            'registry',
            $container
        );
    }

    public function testOffsetUnsetDoesNothingForNotSetId()
    {
        $container = new Container(['entry' => 'value']);
        unset($container['not set']);
        $this->assertAttributeEquals(
            ['container' => $container, 'entry' => 'value'],
            'registry',
            $container
        );
    }

    public function testOffsetUnsetDeletesValueForStringId()
    {
        $container = new Container(['entry' => 'value']);
        unset($container['entry']);
        $this->assertAttributeEquals(
            ['container' => $container],
            'registry',
            $container
        );
    }
}
