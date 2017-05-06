<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Dependency\Test\Definition;

/** PSR-11 use block. */
use Psr\Container\ContainerInterface;

/** PHPUnit use block. */
use PHPUnit\Framework\TestCase;

/** PHPUnit Utility use block. */
use Atanvarno\PHPUnit\CallProtectedMethodTrait;

/** Package use block. */
use Atanvarno\Dependency\Definition\{
    Entry, ResolveParametersTrait
};

class ResolveParametersTraitTest extends TestCase
{
    use CallProtectedMethodTrait;

    private $container, $trait;

    public function setUp()
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->trait = $this->getMockForTrait(ResolveParametersTrait::class);
    }

    public function testResolveParameterPassthrough()
    {
        $parameter = 'test';
        $result = $this->callProtectedMethod(
            $this->trait,
            'resolveParameter',
            [$parameter, $this->container]
        );
        $this->assertSame($parameter, $result);
    }

    public function testResolveParameterEntry()
    {
        $this->container
            ->expects($this->once())
            ->method('get')
            ->with($this->stringContains('test'))
            ->willReturn(100);
        $parameter = new Entry('test');
        $result = $this->callProtectedMethod(
            $this->trait,
            'resolveParameter',
            [$parameter, $this->container]
        );
        $this->assertSame(100, $result);
    }

    public function testResolveParameterRecursiveWithArray()
    {
        $this->container
            ->expects($this->once())
            ->method('get')
            ->with($this->stringContains('test'))
            ->willReturn(100);
        $parameter = [
            'A' => 'testA',
            'B' => [
                'Y' => new Entry('test'),
                'Z' => 500,
            ],
            'C' => 200,
        ];
        $result = $this->callProtectedMethod(
            $this->trait,
            'resolveParameter',
            [$parameter, $this->container]
        );
        $expected = [
            'A' => 'testA',
            'B' => [
                'Y' => 100,
                'Z' => 500,
            ],
            'C' => 200,
        ];
        $this->assertSame($expected, $result);
    }
}
