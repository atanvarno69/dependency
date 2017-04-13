<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Dependency\Test;

/** PHPUnit use block. */
use PHPUnit\Framework\TestCase;

/** Package use block. */
use Atanvarno\Dependency\{
    entry, factory, object, value, Definition\Entry
};

class FunctionsTest extends TestCase
{   
    public function testFunctionEntry()
    {
        $result = entry('ID');
        $this->assertInstanceOf(Entry::class, $result);
        $this->assertSame('ID', (string) $result);
    }
}