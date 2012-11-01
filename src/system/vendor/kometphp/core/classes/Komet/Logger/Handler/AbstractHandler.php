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
 * 
 * @todo AbstractHandler: Mailer / SwiftMailer handler
 */
abstract class AbstractHandler
{

    /**
     * Channel the handler is subscribed to
     * @var string
     */
    protected $channel = null;

    /**
     *
     * @var int
     */
    protected $level = 100;

    /**
     *
     * @var boolean
     */
    protected $bubble = true;

    /**
     *
     * @var \Closure|\Komet\Logger\Formatter\FormatterInterface
     */
    protected $formatter;

    public function __construct($channel = null, $level = array(100, 200, 300, 400, 500), $bubble = true)
    {
        $this->channel = $channel;
        $this->level = $level;
        $this->bubble = $bubble;
        $this->formatter = new \Komet\Logger\Formatter\LineFormatter();
    }

    public function canHandle($channel, $level)
    {
        $subscribed = ($channel == $this->channel) || (empty($this->channel)) || ($this->channel == "*");
        if (is_array($this->level)) {
            return $subscribed && in_array($level, $this->level);
        } else {
            return $subscribed && ($this->level == $level);
        }
    }

    public function canBubble()
    {
        return $this->bubble;
    }

    /**
     * Closes the handler.
     *
     * This will be called automatically when the object is destroyed
     */
    public function close()
    {
        
    }

    public function __destruct()
    {
        try {
            $this->close();
        } catch (\Exception $e) {
            // do nothing
        }
    }

    /**
     * 
     * @param \Closure|\Komet\Logger\Formatter\FormatterInterface $formatter
     */
    public function setFormatter($formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * 
     * @return \Closure|\Komet\Logger\Formatter\FormatterInterface
     */
    public function getFormatter()
    {
        return $this->formatter;
    }

    /**
     * 
     * @param array $record
     * @return boolean
     */
    abstract public function log($record);

    /**
     * 
     * @param array $record
     * @return array returns $record with a "formatted" field
     */
    public function format($record)
    {
        if ($this->formatter instanceof \Komet\Logger\Formatter\FormatterInterface) {
            $formatted = $this->formatter->format($record);
        } elseif (is_callable($this->formatter)) {
            $fn = $this->formatter;
            $formatted = $fn($record);
        } else {
            $formatted = $record;
        }

        return $formatted;
    }

}