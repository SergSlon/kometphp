<?php

/**
 * Part of the KometPHP Framework
 * 
 * @package Komet
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */

namespace Komet;

/**
 * Read-once cross-request "Flash" messaging
 * 
 * @package Komet
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */
class Flash
{

    const SESSVAR = "FLASH_MESSAGES";

    protected $channel = "main";

    public function __construct($channel = "main")
    {
        $this->channel = $channel;
        
        if (!isset($_SESSION[self::SESSVAR])) {
            $_SESSION[self::SESSVAR] = array();
        }
        if (!isset($_SESSION[self::SESSVAR][$this->channel])) {
            $_SESSION[self::SESSVAR][$this->channel] = array();
        }
    }

    public function write($data, $type = "info")
    {
        $_SESSION[self::SESSVAR][$this->channel][] = $message = array(
            "type" => $type,
            "data" => $data,
        );
    }

    public function getMessages($flush = true)
    {
        $messages = $_SESSION[self::SESSVAR][$this->channel];

        if ($flush) {
            $this->clear();
        }
        return $messages;
    }

    public function hasMessages()
    {
        return count($_SESSION[self::SESSVAR][$this->channel]) > 0;
    }

    public function clear()
    {
        $_SESSION[self::SESSVAR][$this->channel] = array();
    }

}