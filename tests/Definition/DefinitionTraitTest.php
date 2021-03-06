<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2021 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

declare(strict_types = 1);

namespace Atanvarno\Dependency\Test\Definition;

use Psr\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Atanvarno\PHPUnit\SetProtectedPropertyTrait;
use Atanvarno\Dependency\Definition;
use Atanvarno\Dependency\Definition\{
    CallMethod, DefinitionTrait, SetProperty
};

class DefinitionTraitTest extends TestCase
{
    use SetProtectedPropertyTrait;

    private $container, $objectReturningDefinition, $stringReturningDefinition;

    public function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->objectReturningDefinition = new DefinitionMock(
            function () {return new TestObjectMock();}
        );
        $this->stringReturningDefinition = new DefinitionMock(
            function () {return 'test string';}
        );
        $this->setProtectedProperty(
            $this->objectReturningDefinition, 'actions', []
        );
        $this->setProtectedProperty(
            $this->stringReturningDefinition, 'actions', []
        );
    }

    public function testBuildReturningNonObject()
    {
        $result = $this->stringReturningDefinition->build($this->container);
        $this->assertSame('test string', $result);
    }

    public function testBuildReturningObject()
    {
        $result = $this->objectReturningDefinition->build($this->container);
        $this->assertInstanceOf(TestObjectMock::class, $result);
    }

    public function testBuildReturningObjectWithActions()
    {
        $this->objectReturningDefinition->property('public', 'public test');
        $this->objectReturningDefinition->method('set', ['private test']);
        $result = $this->objectReturningDefinition->build($this->container);
        $this->assertInstanceOf(TestObjectMock::class, $result);
        $this->assertSame('public test', $result->public);
        $this->assertSame('private test', $result->get());
    }

    public function testIsRegisteredTrue()
    {
        $this->setProtectedProperty(
            $this->stringReturningDefinition,
            'register',
            true
        );
        $this->assertTrue($this->stringReturningDefinition->isRegistered());
    }

    public function testIsRegisteredFalse()
    {
        $this->setProtectedProperty(
            $this->stringReturningDefinition,
            'register',
            false
        );
        $this->assertFalse($this->stringReturningDefinition->isRegistered());
    }

    public function testMethod()
    {
        $result = $this->stringReturningDefinition
            ->method('testName', ['A', 2]);
        $expected = [new CallMethod('testName', ['A', 2])];
        $this->assertSame($this->stringReturningDefinition, $result);
        $this->assertAttributeEquals($expected, 'actions', $result);
    }

    public function testProperty()
    {
        $result = $this->stringReturningDefinition
            ->property('testName', ['A', 2]);
        $expected = [new SetProperty('testName', ['A', 2])];
        $this->assertSame($this->stringReturningDefinition, $result);
        $this->assertAttributeEquals($expected, 'actions', $result);
    }
}

class DefinitionMock implements Definition
{
    use DefinitionTrait;

    private $callable;

    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    protected function factoryMethod(ContainerInterface $container): mixed
    {
        return call_user_func($this->callable, []);
    }
}
