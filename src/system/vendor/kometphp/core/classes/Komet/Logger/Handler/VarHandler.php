<?php

/**
 * Part of the KometPHP Framework
 * 
 * @package Komet/Logger
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */

namespace Komet\Logger\Handler;

/**
 * 
 * @package Komet/Logger
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */
class VarHandler extends \Komet\Logger\Handler\NullHandler
{

    protected $logs = array();

    public function log($record)
    {
        $this->logs[] = $record;
        return false;
    }

    public function getLogs()
    {
        return $this->logs;
    }

}