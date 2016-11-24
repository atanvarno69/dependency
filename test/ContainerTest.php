<?php
/**
 * ContainerTest class file
 * 
 * Copyright (C) 2016 atanvarno.com
 *
 * This file is part of Atan\Dependency.
 *
 * Atan\Dependency is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Atan\Dependency is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Atan\Dependency.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package   Atan\Dependency
 * @author    atan <https://github.com/atanvarno69>
 * @copyright 2016 atanvarno.com
 * @license   http://opensource.org/licenses/GPL-3.0 GNU GPL v3
 */

namespace Atan\Dependency\Test;

/** PSR-3 use block */
use Psr\Log\LoggerAwareInterface;

/** PHPUnit use block */
use PHPUnit_Framework_TestCase as TestCase;

use Atan\Dependency\Container;

/**
 * PSR-11 use block
 *
 * @todo Change to `Psr` namespace when PSR-11 accepted
 */
use Interop\Container\{
    ContainerInterface,
    Exception\ContainerException as ContainerExceptionInterface,
    Exception\NotFoundException as NotFoundExceptionInterface
};

use Monolog\{
    Logger,
    Handler\StreamHandler
};

use Exception, InvalidArgumentException;

class ContainerTest extends TestCase
{
    protected $container, $logger;
    
    public function setUp()
    {
        $logPath = __DIR__ . '/log';
        $this->logger = new Logger('testLogger');
        $this->logger->pushHandler(new StreamHandler($logPath, Logger::DEBUG));
        $this->container = new Container();
    }
    
    public function tearDown()
    {
        $path = __DIR__ . '/log';
        if (file_exists($path)) {
            unlink($path);
        }
    }
    
    public function testConstructorDefaults()
    {
        $this->assertInstanceOf(ContainerInterface::class, $this->container);
        $this->assertInstanceOf(LoggerAwareInterface::class, $this->container);
    }
    
    public function testConstructorAcceptsWellFormedDefinitionsArray()
    {
        $definitions = [
            'Test1' => [
                'entity' => 'test string',
                'params' => [],
                'register' => true,
            ],
        ];
        $container = new Container($definitions);
        $this->assertInstanceOf(Container::class, $container);
    }
    
    public function testConstructorAcceptsMinimalDefinitionsArray()
    {
        $definitions = [
            'Test1' => [
                'entity' => 'test string',
            ],
        ];
        $container = new Container($definitions);
        $this->assertInstanceOf(Container::class, $container);
    }
    
    public function testConstructorAcceptsParent()
    {
        $parent = new Container();
        $container = new Container([], $parent);
        $this->assertInstanceOf(Container::class, $container);
    }
    
    public function testConstructorAcceptsChildren()
    {
        $child1 = new Container();
        $child2 = new Container();
        $container = new Container([], null, [$child1, $child2]);
        $this->assertInstanceOf(Container::class, $container);
    }
    
    public function testConstructorAcceptsLogger()
    {
        $container = new Container([], null, [], $this->logger);
        $this->assertInstanceOf(Container::class, $container);
    }
    
    public function testConstructorSetsEntryForSelf()
    {
        $container = new Container();
        $this->assertTrue($container->has('Container'));
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
    
    public function testConstructorThrowsInvalidArgumentExceptionWhenDefinitionsDoesNotContainEntity()
    {
        $definitions = [
            'Test1' => [
                'wrongKey' => 'test string',
                'params' => [],
                'register' => true,
            ],
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
    
    public function testLogsDebugOnSucess()
    {
        $this->container->setLogger($this->logger);
        $this->container->define('Test', 'value');
        $this->container->get('Test');
        $this->assertFileExists(__DIR__ . '/log');
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
        $child1 = new Container(['Test' => ['entity' => 1]]);
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
