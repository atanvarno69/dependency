<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2021 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

declare(strict_types = 1);

namespace Atanvarno\Dependency\Test;

use ArrayAccess, Exception, TypeError;
use Psr\Container\{ContainerInterface, NotFoundExceptionInterface};
use Psr\SimpleCache\{CacheException, CacheInterface};
use PHPUnit\Framework\TestCase;
use Atanvarno\Dependency\{Container, Definition};
use Atanvarno\Dependency\Definition\{Entry, ValueDefinition};
use Atanvarno\Dependency\Exception\{
    ConfigurationException,
    InvalidArgumentException,
    RuntimeException,
    UnexpectedValueException
};

class ContainerTest extends TestCase
{
    /** @var Container $container */
    private $container;
    
    public function setup(): void
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
        $container = new Container([
            'test-definition' => $definition,
            'test-value' => new ValueDefinition('value', true)

        ]);
        $this->assertAttributeEquals(
            ['test-definition' => $definition], 'definitions', $container
        );
        $this->assertAttributeEquals(
            ['test-value' => 'value', 'container' => $container],
            'registry',
            $container
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
        $cache->method('get')->willReturn([
            'test-value' => 'value',
        ]);
        $callable = function($cache) {return $cache;};
        $container = new Container(
            [
                'cache' => new Definition\FactoryDefinition(
                    $callable, [$cache], true
                ),
            ],
            new Entry('cache')
        );
        $this->assertAttributeEquals($cache, 'cache', $container);
        $this->assertAttributeEquals(
            [
                'test-value' => 'value',
                'container' => $container,
                'cache' => $cache
            ],
            'registry',
            $container
        );
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
        $this->expectException(InvalidArgumentException::class);
        new Container([], 1);
    }

    public function testConstructorRejectsInvalidCacheStringEntryIdentifier()
    {
        $this->expectException(RuntimeException::class);
        new Container([], new Entry('invalid'));
    }

    public function testConstructorRejectsInvalidCacheResolutionFromDefinition()
    {
        $this->expectException(UnexpectedValueException::class);
        new Container(
            [
                'invalid' => new Definition\ObjectDefinition(
                    Container::class, [], true
                ),
            ],
            new Entry('invalid')
        );
    }

    public function testConstructorBubblesCacheGetError()
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willThrowException(
            new class() extends Exception implements CacheException {}
        );
        $callable = function($cache) {return $cache;};
        $this->expectException(RuntimeException::class);
        new Container(
            [
                'cache' => new Definition\FactoryDefinition(
                    $callable, [$cache], true
                ),
            ],
            new Entry('cache')
        );
    }

    public function testConstructorRejectsInvalidPrimitiveReturnFromCache()
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willReturn('invalid');
        $callable = function($cache) {return $cache;};
        $this->expectException(UnexpectedValueException::class);
        new Container(
            [
                'cache' => new Definition\FactoryDefinition(
                    $callable, [$cache], true
                ),
            ],
            new Entry('cache')
        );
    }

    public function testConstructorRejectsInvalidObjectReturnFromCache()
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willReturn($this->container);
        $callable = function($cache) {return $cache;};
        $this->expectException(UnexpectedValueException::class);
        new Container(
            [
                'cache' => new Definition\FactoryDefinition(
                    $callable, [$cache], true
                ),
            ],
            new Entry('cache')
        );
    }

    public function testConstructorRejectsInvalidCacheKey()
    {
        $this->expectException(InvalidArgumentException::class);
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
        $this->expectException(RuntimeException::class);
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
        $this->expectException(InvalidArgumentException::class);
        $this->container->setSelfId('');
    }

    public function testSelfIdExcludedFromCache()
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willReturn([]);
        $container = new Container([], $cache);
        $cache->expects($this->once())
            ->method('set')
            ->with('container', ['test' => 'value'])
            ->willReturn(true);
        $container->set('test', 'value');
    }
}
