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
 */
class StreamHandler extends AbstractHandler
{

    protected $stream;
    protected $uri;

    public function __construct($stream, $channel = null, $level = array(100, 200, 300, 400, 500), $bubble = true)
    {
        parent::__construct($channel, $level, $bubble);
        if (is_resource($stream)) {
            $this->stream = $stream;
        } else {
            $this->uri = $stream;
        }
    }

    public function close()
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
        $this->stream = null;
    }

    /**
     * 
     * @param array $record
     * @return boolean
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    public function log($record)
    {
        if (null === $this->stream) {
            if (!$this->uri) {
                throw new \LogicException('Missing stream uri, the stream can not be opened. This may be caused by a premature call to close().');
            }
            $errorMessage = null;
            set_error_handler(function ($code, $msg) use (&$errorMessage) {
                        $errorMessage = preg_replace('{^fopen\(.*?\): }', '', $msg);
                    });
            $this->stream = fopen($this->uri, 'a');
            restore_error_handler();
            if (!is_resource($this->stream)) {
                $this->stream = null;
                throw new \UnexpectedValueException(sprintf('The stream or file "%s" could not be opened: ' . $errorMessage, $this->uri));
            }
        }
        return fwrite($this->stream, $this->format($record)) > 0;
    }

}