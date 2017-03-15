<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Dependency\Test;

/** PHPUnit use block */
use PHPUnit\Framework\TestCase;

/** PSR-16 use block. */
use Psr\SimpleCache\CacheInterface;

/** Package use block. */
use Atanvarno\Dependency\Container;

/** Dependency use block. */
use Atanvarno\Cache\{
    Apcu\APCuDriver, Cache
};

class CachedContainerTest extends TestCase
{
    /** @var CacheInterface $cache */
    private $cache;

    /** @var Container $container */
    private $container;

    public function setUp()
    {
        $this->cache = new Cache(new APCuDriver());
        $this->container = new Container('container', $this->cache);
    }

    public function tearDown()
    {
        $this->cache->clear();
    }

    public function testConstructorWithCache()
    {
        $this->assertAttributeEquals($this->cache, 'cache', $this->container);
        $this->assertAttributeEquals(
            'container.', 'cacheKey', $this->container
        );
    }

    public function testAdd()
    {
        $this->container->add('test', 'value');
        $this->assertTrue($this->cache->has('container.test'));
    }

    public function testDelete()
    {
        $this->cache->set('container.test', 'value');
        $this->assertTrue($this->cache->has('container.test'));
        $this->container->delete('test');
        $this->assertFalse($this->cache->has('container.test'));
    }

    public function testGet()
    {
        $this->container->add('test', 'value');
        $result = $this->container->get('test');
        $this->assertSame('value', $result);
    }

    public function testHas()
    {
        $this->assertFalse($this->container->has('test'));
        $this->container->add('test', 'value');
        $this->assertTrue($this->container->has('test'));
    }
}
