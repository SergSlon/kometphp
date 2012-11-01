<?php

/**
 * Part of the KometPHP Framework
 * 
 * @package Komet/Logger
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */

namespace Komet\Logger\Formatter;

/**
 * 
 * @package Komet/Logger
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 * 
 * @todo LineFormatter: use tabulation instead of json
 */
class LineFormatter implements FormatterInterface
{

    /**
     * Formats a log record.
     *
     * @param  array $record A record to format
     * @return string The formatted record
     */
    public function format(array $record)
    {
        return stripslashes(json_encode($record)) . "\n";
    }

}