<?php

/**
 * Part of the KometPHP Framework
 * 
 * @package Komet/Logger
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */

namespace Komet\Logger;

/**
 * Interface for logger classes
 * 
 * @package Komet/Logger
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */
interface Loggeable
{
    /**
     * Detailed debug information.
     */

    const DEBUG = 100;

    /**
     * Interesting events. Examples: User logs in, SQL logs.
     */
    const INFO = 200;

    /**
     * Exceptional occurrences that are not errors. Examples: Use of deprecated
     * APIs, poor use of an API, undesirable things that are not necessarily wrong.
     */
    const WARNING = 300;

    /**
     * User errors or runtime errors that do NOT REQUIRE immediate action but should typically
     * be logged and monitored. 
     */
    const ERROR = 400;

    /**
     * Server errors or runtime fatal errors that REQUIRE immediate action.
     */
    const CRITICAL = 500;

    public function debug($message, $data = null, $channel = null);

    public function info($message, $data = null, $channel = null);

    public function warning($message, $data = null, $channel = null);

    public function error($message, $data = null, $channel = null);

    public function critical($message, $data = null, $channel = null);

    public function write($level, $message, $data = null, $channel = null);
}