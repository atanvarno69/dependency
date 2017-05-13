<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Dependency\Test;

/** SPL use block. */
use ArrayAccess, Exception, TypeError;

/** PSR-11 use block. */
use Psr\Container\{ContainerInterface, NotFoundExceptionInterface};

/** PSR-16 use block. */
use Psr\SimpleCache\{CacheException, CacheInterface};

/** PHPUnit use block. */
use PHPUnit\Framework\TestCase;

/** Package use block. */
use Atanvarno\Dependency\{Container, Definition};
use Atanvarno\Dependency\Exception\{ConfigurationException, ContainerException};

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

    public function testConstructorWithCacheFromDefinition()
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willReturn([]);
        $callable = function($cache) {return $cache;};
        $container = new Container(
            [
                'cache' => new Definition\FactoryDefinition(
                    $callable, [$cache], true
                ),
            ],
            'cache'
        );
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

    public function testConstructorRejectsInvalidCacheIdentifierType()
    {
        $this->expectException(ContainerException::class);
        new Container([], 1);
    }

    public function testConstructorRejectsInvalidCacheStringEntryIdentifier()
    {
        $this->expectException(ContainerException::class);
        new Container([], 'invalid');
    }

    public function testConstructorRejectsInvalidCacheResolutionFromDefinition()
    {
        $this->expectException(ContainerException::class);
        new Container(
            [
                'invalid' => new Definition\ObjectDefinition(
                    Container::class, [], true
                ),
            ],
            'invalid'
        );
    }

    public function testConstructorBubblesCacheGetError()
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willThrowException(
            new class() extends Exception implements CacheException {}
        );
        $callable = function($cache) {return $cache;};
        $this->expectException(ConfigurationException::class);
        new Container(
            [
                'cache' => new Definition\FactoryDefinition(
                    $callable, [$cache], true
                ),
            ],
            'cache'
        );
    }

    public function testConstructorRejectsInvalidPrimitiveReturnFromCache()
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willReturn('invalid');
        $callable = function($cache) {return $cache;};
        $this->expectException(ConfigurationException::class);
        new Container(
            [
                'cache' => new Definition\FactoryDefinition(
                    $callable, [$cache], true
                ),
            ],
            'cache'
        );
    }

    public function testConstructorRejectsInvalidObjectReturnFromCache()
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willReturn($this->container);
        $callable = function($cache) {return $cache;};
        $this->expectException(ConfigurationException::class);
        new Container(
            [
                'cache' => new Definition\FactoryDefinition(
                    $callable, [$cache], true
                ),
            ],
            'cache'
        );
    }

    public function testConstructorRejectsInvalidCacheKey()
    {
        $this->expectException(ContainerException::class);
        new Container([], null, '');
    }

    public function testAddChild()
    {
        $child = $this->createMock(ContainerInterface::class);
        $result = $this->container->addChild($child);
        $this->assertSame($this->container, $result);
        $this->assertAttributeEquals([$child], 'children', $result);
    }

    public function testClearCache()
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willReturn([]);
        $cache->expects($this->once())
            ->method('delete')
            ->with('container')
            ->willReturn(true);
        $container = new Container([], $cache);
        $container->clearCache();
    }

    public function testClearCacheDoesNothingWithoutACacheSet()
    {
        $result = $this->container->clearCache();
        $this->assertSame($this->container, $result);
    }

    public function testClearCacheThrowsExceptionIfCacheCannotBeCleared()
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willReturn([]);
        $cache->expects($this->once())
            ->method('delete')
            ->with('container')
            ->willReturn(false);
        $container = new Container([], $cache);
        $this->expectException(ContainerException::class);
        $container->clearCache();
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

    // todo: delete() - cache interactions.

    public function testGetFromDefinition()
    {
        $container = new Container(
            [
                'test ID' => new Definition\ObjectDefinition(
                    Container::class, [], true
                ),
            ]
        );
        $result = $container->get('test ID');
        $this->assertInstanceOf(Container::class, $result);
        $this->assertNotSame($container, $result);
    }

    public function testGetFromRegistry()
    {
        $result = $this->container->get('container');
        $this->assertSame($this->container, $result);
    }

    public function testCompositeGetChecksSelfFirst()
    {
        $child = $this->createMock(ContainerInterface::class);
        $child->expects($this->never())->method('has');
        $this->container->addChild($child);
        $result = $this->container->get('container');
        $this->assertSame($this->container, $result);
    }

    public function testCompositeGetGetsFromChildren()
    {
        $child = new Container();
        $this->container->setSelfId('parent');
        $this->container->addChild($child);
        $result = $this->container->get('container');
        $this->assertSame($child, $result);
    }

    public function testGetRejectsUnknownId()
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $this->container->get('invalid');
    }

    public function testCompositeGetRejectsUnknownId()
    {
        $child = new Container();
        $this->container->addChild($child);
        $this->expectException(NotFoundExceptionInterface::class);
        $this->container->get('invalid');
    }

    public function testGetRejectsNonStringParameter()
    {
        $this->expectException(TypeError::class);
        $this->container->get(1);
    }

    // todo: get() - cache interactions.

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

    public function testOffsetGetWithDefinition()
    {
        $container = new Container(
            [
                'test ID' => new Definition\ObjectDefinition(
                    Container::class, [], true
                ),
            ]
        );
        $result = $container['test ID'];
        $this->assertInstanceOf(Container::class, $result);
        $this->assertNotSame($container, $result);
    }

    public function testOffsetGetWithValue()
    {
        $result = $this->container['container'];
        $this->assertSame($this->container, $result);
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

    // todo: set() - cache interactions.

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
