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
class NullHandler extends AbstractHandler
{

    /**
     * 
     * @param array $record
     * @return false
     */
    public function log($record)
    {
        return false;
    }

}