<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Dependency\Test;

/** SPL use block. */
use ArrayAccess, TypeError;

/** PSR-11 use block. */
use Atanvarno\Dependency\Definition;
use Atanvarno\Dependency\Exception\ConfigurationException;
use Atanvarno\Dependency\Exception\ContainerException;
use Psr\Container\ContainerInterface;

/** PHPUnit use block. */
use PHPUnit\Framework\TestCase;

/** Package use block. */
use Atanvarno\Dependency\Container;
use Psr\SimpleCache\CacheInterface;

class ContainerTest extends TestCase
{
    /** @var Container $container */
    private $container;
    
    public function setup()
    {
        $this->container = new Container();
    }
    
    public function testImplementsInterfaces()
    {
        $this->assertInstanceOf(ArrayAccess::class, $this->container);
        $this->assertInstanceOf(ContainerInterface::class, $this->container);
    }
    
    public function testConstructorDefaults()
    {
        $this->assertAttributeEquals(null, 'cache', $this->container);
        $this->assertAttributeEquals('container', 'cacheKey', $this->container);
        $this->assertAttributeEmpty('children', $this->container);
        $this->assertAttributeEmpty('definitions', $this->container);
        $this->assertAttributeEquals(null, 'delegate', $this->container);
        $this->assertAttributeEquals(
            ['container' => $this->container], 'registry', $this->container
        );
    }

    public function testConstructorWithDefinition()
    {
        $definition = $this->createMock(Definition::class);
        $container = new Container(['test' => $definition]);
        $this->assertAttributeEquals(
            ['test' => $definition], 'definitions', $container
        );
    }

    public function testConstructorWithCache()
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willReturn([]);
        $container = new Container([], $cache);
        $this->assertAttributeEquals($cache, 'cache', $container);
    }

    public function testConstructorWithCacheKey()
    {
        $container = new Container([], null, 'test key');
        $this->assertAttributeEquals('test key', 'cacheKey', $container);
    }

    public function testConstructorRejectsNonDefinition()
    {
        $this->expectException(ConfigurationException::class);
        new Container(['test' => 'value']);
    }

    public function testConstructorRejectsInvalidCache()
    {
        $this->expectException(ContainerException::class);
        new Container([], 'invalid');
    }

    public function testConstructorRejectsInvalidCacheKey()
    {
        $this->expectException(ContainerException::class);
        new Container([], null, '');
    }

    // todo: various cache resolution options.
    
    public function testAddChild()
    {
        $child = $this->createMock(ContainerInterface::class);
        $result = $this->container->addChild($child);
        $this->assertSame($this->container, $result);
        $this->assertAttributeEquals([$child], 'children', $result);
    }

    public function testDeleteOnRegistryKey()
    {
        $this->container->set('registry entry', 'test value');
        $this->assertAttributeEquals(
            [
                'container' => $this->container,
                'registry entry' => 'test value'
            ],
            'registry',
            $this->container
        );
        $result = $this->container->delete('registry entry');
        $this->assertSame($this->container, $result);
        $this->assertAttributeEquals(
            ['container' => $this->container], 'registry', $this->container
        );
    }

    public function testDeleteOnDefinitionKey()
    {
        $definition = $this->createMock(Definition::class);
        $this->container->set('definition entry', $definition);
        $this->assertAttributeEquals(
            ['definition entry' => $definition], 'definitions', $this->container
        );
        $result = $this->container->delete('definition entry');
        $this->assertSame($this->container, $result);
        $this->assertAttributeEquals([], 'definitions', $this->container);
    }

    // todo: delete has cache interactions.
    
    // todo: test self get vs composite get.
    
    // todo: test self has vs composite has.
    public function testHasWithValidDefinitionValue()
    {
        $definition = $this->createMock(Definition::class);
        $this->container->set('definition entry', $definition);
        $result = $this->container->has('definition entry');
        $this->assertTrue($result);
    }

    public function testHasWithValidRegistryValue()
    {
        $result = $this->container->has('container');
        $this->assertTrue($result);
    }

    public function testHasWithInvalidValue()
    {
        $result = $this->container->has('invalid');
        $this->assertFalse($result);
    }

    public function testCompositeHasChecksSelfFirst()
    {
        $child = $this->createMock(ContainerInterface::class);
        $child->expects($this->never())->method('has');
        $this->container->addChild($child);
        $result = $this->container->has('container');
        $this->assertTrue($result);
    }

    public function testCompositeHasChecksChildren()
    {
        $child = $this->createMock(ContainerInterface::class);
        $child->expects($this->once())
            ->method('has')
            ->with('test ID')
            ->willReturn(true);
        $this->container->addChild($child);
        $result = $this->container->has('test ID');
        $this->assertTrue($result);
    }

    public function testCompositeHasReturnsFalseWhenNotFound()
    {
        $child = $this->createMock(ContainerInterface::class);
        $child->expects($this->once())
            ->method('has')
            ->with('test ID')
            ->willReturn(false);
        $this->container->addChild($child);
        $result = $this->container->has('test ID');
        $this->assertFalse($result);
    }

    public function testHasRejectsNonStringParameter()
    {
        $this->expectException(TypeError::class);
        $this->container->has(1);
    }
    
    // todo: test ArrayAccess methods.
    public function testOffsetExistsWithValidValue()
    {
        $result = isset($this->container['container']);
        $this->assertTrue($result);
    }

    public function testOffsetExistsWithInvalidValue()
    {
        $result = isset($this->container['invalid']);
        $this->assertFalse($result);
    }

    public function testOffsetSetWithDefinition()
    {
        $definition = $this->createMock(Definition::class);
        $this->container['test'] = $definition;
        $this->assertAttributeEquals(
            ['test' => $definition], 'definitions', $this->container
        );
    }

    public function testOffsetSetWithValue()
    {
        $this->container['test'] = 'value';
        $expected = [
            'container' => $this->container,
            'test' => 'value',
        ];
        $this->assertAttributeEquals($expected, 'registry', $this->container);
    }

    public function testOffsetUnsetOnRegistryKey()
    {
        $this->container['registry entry'] = 'test value';
        $this->assertAttributeEquals(
            [
                'container' => $this->container,
                'registry entry' => 'test value'
            ],
            'registry',
            $this->container
        );
        unset($this->container['registry entry']);
        $this->assertAttributeEquals(
            ['container' => $this->container], 'registry', $this->container
        );
    }

    public function testOffsetUnsetOnDefinitionKey()
    {
        $definition = $this->createMock(Definition::class);
        $this->container['definition entry'] = $definition;
        $this->assertAttributeEquals(
            ['definition entry' => $definition], 'definitions', $this->container
        );
        unset($this->container['definition entry']);
        $this->assertAttributeEquals([], 'definitions', $this->container);
    }
    
    // todo: test set has cache interactions.

    public function testSetWithDefinition()
    {
        $definition = $this->createMock(Definition::class);
        $result = $this->container->set('test', $definition);
        $this->assertSame($this->container, $result);
        $this->assertAttributeEquals(
            ['test' => $definition], 'definitions', $result
        );
    }

    public function testSetWithValue()
    {
        $result = $this->container->set('test', 'value');
        $expected = [
            'container' => $this->container,
            'test' => 'value',
        ];
        $this->assertSame($this->container, $result);
        $this->assertAttributeEquals($expected, 'registry', $result);
    }
    
    public function testSetDelegate()
    {
        $delegate = $this->createMock(ContainerInterface::class);
        $result = $this->container->setDelegate($delegate);
        $this->assertSame($this->container, $result);
        $this->assertAttributeEquals($delegate, 'delegate', $result);
    }

    public function testSetSelfId()
    {
        $result = $this->container->setSelfId('new value');
        $this->assertSame($this->container, $result);
        $this->assertAttributeEquals(
            ['new value' => $this->container], 'registry', $result
        );
    }

    public function testSetSelfIdRejectsInvalidId()
    {
        $this->expectException(ConfigurationException::class);
        $this->container->setSelfId('');
    }
}
