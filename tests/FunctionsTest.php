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
    entry, factory, object, value, Definition\Entry as EntryClass
};

class FunctionsTest extends TestCase
{
    public function setUp()
    {
        include dirname(__DIR__) . '/src/functions.php';
    }
    
    public function testFunctionEntry()
    {
        $result = entry('ID');
        $this->assertInstanceOf(EntryClass::class, $result);
        $this->assertSame('ID', (string) $result);
    }
}
