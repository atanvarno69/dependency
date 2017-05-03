<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Dependency\Test;

/** SPL use block. */
use ArrayAccess;

/** PSR-11 use block. */
use Psr\Container\ContainerInterface;

/** PHPUnit use block. */
use PHPUnit\Framework\TestCase;

/** Package use block. */
use Atanvarno\Dependency\Container;

class ContainerTest extends TestCase
{
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
    
    // todo: test constructor
    
    public function testAddChild()
    {
        $child = $this->createMock(ContainerInterface::class);
        $result = $this->container->addChild($child);
        $this->assertSame($this->container, $result);
        $this->assertAttributeEquals([$child], 'children', $result);
    }
    
    // todo: delete has cache interactions.
    
    // todo: test self get vs composite get.
    
    // todo: test self has vs composite has.
    
    // todo: test ArrayAccess methods.
    
    // todo: test set has cache interactions.
    
    public function testSetDelegate()
    {
        $delegate = $this->createMock(ContainerInterface::class);
        $result = $this->container->setDelegate($child);
        $this->assertSame($this->container, $result);
        $this->assertAttributeEquals($delegate, 'delegate', $result);
    }
}
