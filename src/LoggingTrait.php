<?php
/**
 * LoggingTrait trait file
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

namespace Atan\Dependency;

/** PSR-3 Logger Interface use block */
use Psr\Log\LoggerInterface;

/**
 * LoggingTrait trait
 * 
 * Provides PSR-3 `LoggerAwareInterface` functionality.
 */
trait LoggingTrait
{
    /** @var LoggerInterface $logger PSR-3 logger */
    protected $logger;
    
    /**
     * LoggerAware implementation
     *
     * @param  LoggerInterface $logger
     * @return void
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * Log to a PSR-3 logger, if available
     *
     * @param  string $level   Use constants provided by `LogLevel`
     * @param  string $message Message to log
     * @param  array  $context Context array to log
     * @return void
     */
    protected function log(string $level, string $message, array $context = [])
    {
        if (isset($this->logger)) {
            $msg = get_class($this) . ': ' . $message;
            $this->logger->log($level, $msg, $context);
        }
    }
}
