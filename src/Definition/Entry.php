<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Dependency\Definition;

/**
 * Atanvarno\Dependency\Definition\Entry
 *
 * @internal Represents a container entry in definitions. Returned by the
 * helper function `entry()`.
 */
class Entry
{
    /** @var string $id Identifier of the proxy entry. */
    private $id;
    
    /**
     * Builds an `Entry` instance.
     *
     * @param string $id Identifier of the proxy entry.
     */
    public function __construct(string $id)
    {
        $this->id = $id;
    }
    
    /**
     * @return string Identifier of the proxy entry.
     */
    public function __toString(): string
    {
        return $this->id;
    }
}
