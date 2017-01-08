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
use Exception, InvalidArgumentException;

/** PHPUnit use block */
use PHPUnit_Framework_TestCase as TestCase;

/** PSR-11 use block. */
use Interop\Container\{
    ContainerInterface,
    Exception\ContainerException as ContainerExceptionInterface,
    Exception\NotFoundException as NotFoundExceptionInterface
};

/** Package use block. */
use Atan\Dependency\Container;

class ContainerTest extends TestCase
{
    protected $container;
    
    public function setUp()
    {
        $this->container = new Container();
    }
    
    public function testConstructorDefaults()
    {
        $this->assertInstanceOf(ContainerInterface::class, $this->container);
    }
    
    public function testConstructorAcceptsWellFormedDefinitionsArray()
    {
        $definitions = [
            'Test1' => [
                'tests string',
                [],
                true,
            ],
        ];
        $container = new Container($definitions);
        $expected = [
            'Test1' => [
                'method' => function (...$params) {
                    return 'tests string';
                },
                'params' => [],
                'register' => true,
            ],
        ];
        $this->assertAttributeEquals($expected, 'definitions', $container);
    }
    
    public function testConstructorAcceptsMinimalDefinitionsArray()
    {
        $definitions = [
            'Test1' => [
                'tests string',
            ],
        ];
        $container = new Container($definitions);
        $expected = [
            'Test1' => [
                'method' => function (...$params) {
                    return 'tests string';
                },
                'params' => [],
                'register' => true,
            ],
        ];
        $this->assertAttributeEquals($expected, 'definitions', $container);
    }
    
    public function testConstructorAcceptsParent()
    {
        $parent = new Container();
        $container = new Container([], $parent);
        $this->assertAttributeInstanceOf(
            ContainerInterface::class,
            'parent',
            $container
        );
    }
    
    public function testConstructorAcceptsChildren()
    {
        $child1 = new Container();
        $child2 = new Container();
        $container = new Container([], null, [$child1, $child2]);
        $this->assertAttributeEquals([$child1, $child2], 'children', $container);
    }
    
    public function testConstructorSetsEntryForSelf()
    {
        $container = new Container();
        $result = $container->get('Container');
        $this->assertSame($container, $result);
    }
    
    public function testConstructorThrowsInvalidArgumentExceptionWhenDefinitionsNotArrayOfArrays()
    {
        $definitions = [
            'string',
            0,
            true,
        ];
        $this->expectException(InvalidArgumentException::class);
        new Container($definitions);
    }
    
    public function testDefineWithCallable()
    {
        $callable = function(...$params) {
            return 'value';
        };
        $this->container->define('Test', $callable);
        $this->assertTrue($this->container->has('Test'));
        $this->assertEquals('value', $this->container->get('Test'));
    }
    
    public function testDefineWithClassName()
    {
        $this->container->define('Test', Container::class);
        $this->assertTrue($this->container->has('Test'));
        $this->assertInstanceOf(Container::class, $this->container->get('Test'));
    }
    
    /**
     * @dataProvider provideDefineWithOtherEntity
     */
    public function testDefineWithOtherEntity($entity)
    {
        $this->container->define('Test', $entity);
        $this->assertTrue($this->container->has('Test'));
        $this->assertEquals($entity, $this->container->get('Test'));
    }
    
    public function testGetGetsFromChildrenIfEntryNotFound()
    {
        $child = new Container();
        $child->define('Test', 'value');
        $this->container->appendChild($child);
        $this->assertEquals('value', $this->container->get('Test'));
    }
    
    public function testGetWhenParamsRequired()
    {
        $callable = function($input) {
            return 'A' . $input;
        };
        $this->container->define('Test', $callable, ['B']);
        $this->assertEquals('AB', $this->container->get('Test'));
    }
    
    public function testGetWhenArrayParamsRequired()
    {
        $callable = function(array $input, string $terminator) {
            return implode('-', $input) . $terminator;
        };
        $this->container->define('Test', $callable, [['A', 'B', 'C'], '-D']);
        $this->assertEquals('A-B-C-D', $this->container->get('Test'));
    }
    
    public function testGetWhenDependencyParamRequired()
    {
        $callable = function($input) {
            return 'A' . $input;
        };
        $this->container->define('Test', $callable, [':Dependency']);
        $this->container->define('Dependency', 'B');
        $this->assertEquals('AB', $this->container->get('Test'));
    }
    
    public function testGetWhenDependencyParamFromParent()
    {
        $parent = new Container();
        $parent->define('Dependency', 'B');
        $callable = function($input) {
            return 'A' . $input;
        };
        $this->container->define('Test', $callable, [':Dependency']);
        $this->container->define('Dependency', 'C');
        $this->container->setParent($parent);
        $this->assertEquals('AB', $this->container->get('Test'));
        
    }
    
    public function testGetThrowsInvalidArgumentExceptionWhenNonStringIdGiven()
    {
        $this->container->define('1', 'value');
        $this->expectException(InvalidArgumentException::class);
        $this->container->get(1);
    }
    
    public function testGetThrowsContainerExceptionIfUserCallableThrowsThrowable()
    {
        $callable = function(...$params) {
            throw new Exception();
        };
        $this->container->define('Test', $callable);
        $this->expectException(ContainerExceptionInterface::class);
        $this->container->get('Test');
    }
    
    public function testGetThrowsNotFoundExceptionIfNotFound()
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $this->container->get('Test');
    }
    
    public function testHasThrowsInvalidArgumentExceptionWhenNonStringIdGiven()
    {
        $this->container->define('1', 'value');
        $this->expectException(InvalidArgumentException::class);
        $this->container->has(1);
    }
    
    public function testPrependChildGivesHigherPriority()
    {
        $child1 = new Container(['Test' => [1]]);
        $this->container->appendChild($child1);
        $child2 = new Container();
        $child2->define('Test', 2);
        $this->container->prependChild($child2);
        $this->assertEquals(2, $this->container->get('Test'));
    }
    
    public function provideDefineWithOtherEntity()
    {
        $object = new class(){};
        return [
            [true],
            [false],
            [0],
            [1],
            [1.1],
            [1.1e2],
            [1.1e-1],
            ['string'],
            [[true, 0]],
            [$object],
        ];
    }
}
