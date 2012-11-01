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
 * @package Komet/Logger
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */
class Logger implements Loggeable
{

    /**
     * @var array of Komet\Logger\Handler\HandlerInterface
     */
    protected $handlers = array();

    /**
     * 
     * @param \Closure|Handler\AbstractHandler $handler
     * @param int|string $index
     */
    public function addHandler($handler, $index = null)
    {
        if ($index !== null) {
            $this->handlers[$index] = $handler;
        } else {
            $this->handlers[] = $handler;
        }
    }

    /**
     * 
     * @param int|string $index
     * @return \Closure|Handler\AbstractHandler
     */
    public function getHandler($index)
    {
        return $this->handlers[$index];
    }

    /**
     * 
     * @param int|string $index
     * @throws \LogicException
     */
    public function removeHandler($index = null)
    {
        if (!$this->handlers) {
            throw new \LogicException('You tried to pop from an empty handler stack.');
        }
        if ($index !== null) {
            unset($this->handlers[$index]);
        } else {
            array_pop($this->handlers);
        }
    }

    protected function getCaller()
    {
        $trace = debug_backtrace();
        //array_shift($bt); //skip current method
        //$rg = array_shift($bt);
        $i = 0;
        while (isset($trace[$i]['class']) && false !== strpos($trace[$i]['class'], 'Logger\\')) {
            $i++;
        }
        return array(
            'class' => isset($trace[$i]['class']) ? $trace[$i]['class'] : null,
            'function' => isset($trace[$i]['function']) ? $trace[$i]['function'] : null,
            'line' => isset($trace[$i - 1]['line']) ? $trace[$i - 1]['line'] : null,
            'file' => isset($trace[$i - 1]['file']) ? $trace[$i - 1]['file'] : null,
        );
    }

    public function debug($message, $data = null, $channel = null)
    {
        $this->write(self::DEBUG, $message, $data, $channel);
    }

    public function info($message, $data = null, $channel = null)
    {
        $this->write(self::INFO, $message, $data, $channel);
    }

    public function warning($message, $data = null, $channel = null)
    {
        $this->write(self::WARNING, $message, $data, $channel);
    }

    public function error($message, $data = null, $channel = null)
    {
        $this->write(self::ERROR, $message, $data, $channel);
    }

    public function critical($message, $data = null, $channel = null)
    {
        $this->write(self::CRITICAL, $message, $data, $channel);
    }

    public function write($level, $message, $data = null, $channel = null)
    {
        $record = array(
            "channel" => $channel,
            "level" => $level,
            "message" => $message,
            "data" => $data,
            "microtime" => microtime(),
            "caller" => $this->getCaller()
        );
        foreach ($this->handlers as $i => $h) {
            /* @var $h Handler\AbstractHandler */
            if (($h instanceof Handler\AbstractHandler) && ($h->canHandle($channel, $level))) {
                $h->log($record);
                if (!$h->canBubble()) {
                    break;
                }
            } elseif (is_callable($h)) {
                if ($h($record) === false) {
                    //If the callable returns false, means stop bubbling
                    break;
                }
            }
        }
    }

    public static function getLevelName($level)
    {
        if (($level >= 100) && ($level <= 199))
            return 'debug';
        if (($level >= 200) && ($level <= 299))
            return 'info';
        if (($level >= 300) && ($level <= 399))
            return 'warning';
        if (($level >= 400) && ($level <= 499))
            return 'error';
        if (($level >= 500) && ($level <= 599))
            return 'critical';

        return 'undefined';
    }

}