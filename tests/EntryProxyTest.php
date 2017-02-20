<?php
/**
 * @package   Atan\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atan\Dependency\Test;

/** PHPUnit use block. */
use PHPUnit\Framework\TestCase;

/** Package use block. */
use Atan\Dependency\EntryProxy;

class EntryProxyTest extends TestCase
{
    public function testConstructor()
    {
        $entryProxy = new EntryProxy('value');
        $this->assertAttributeEquals('value', 'id', $entryProxy);
    }

    public function testToString()
    {
        $entryProxy = new EntryProxy('value');
        $this->assertSame('value', (string) $entryProxy);
    }
}
