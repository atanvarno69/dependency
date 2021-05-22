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
use Atanvarno\PHPUnit\CallProtectedMethodTrait;
use Atanvarno\Dependency\{
    Definition, Definition\ValueDefinition
};

class ValueDefinitionTest extends TestCase
{
    use CallProtectedMethodTrait;

    private $container;

    public function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function testConstructor()
    {
        $result = new ValueDefinition('test', true);
        $this->assertInstanceOf(Definition::class, $result);
        $this->assertAttributeSame('test', 'value', $result);
        $this->assertAttributeSame(true, 'register', $result);
    }

    public function testConstructorWithFalseRegister()
    {
        $result = new ValueDefinition('test', false);
        $this->assertAttributeSame(false, 'register', $result);
    }

    public function testFactoryMethod()
    {
        $definition = new ValueDefinition('test', true);
        $container = $this->createMock(ContainerInterface::class);
        $result = $this->callProtectedMethod(
            $definition,
            'factoryMethod',
            [$container]
        );
        $this->assertSame('test', $result);
    }
}
