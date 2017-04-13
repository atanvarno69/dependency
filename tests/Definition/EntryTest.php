<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Dependency\Test\Definition;

/** PHPUnit use block. */
use PHPUnit\Framework\TestCase;

/** Package use block. */
use Atanvarno\Dependency\Definition\Entry;

class EntryTest extends TestCase
{
    public function testEntry()
    {
        $entry = new Entry('ID');
        $this->assertInstanceOf(Entry::class, $entry);
        $this->assertSame('ID', (string) $entry);
    }
}
