<?php
/**
 * @package   Atan\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atan\Dependency;

/**
 * Atan\Dependency\EntryProxy
 *
 * @internal For use as a proxy parameter for a container entry.
 */
class EntryProxy
{
    /** @var string $id Identifier of the proxy entry. */
    private $id;

    /**
     * EntryProxy constructor.
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
