<?php

/**
 * Part of the KometPHP Framework
 * 
 * @package Komet/DBAL
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */

namespace Komet\DBAL;

/**
 * 
 * @package Komet/DBAL
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */
abstract class AbstractDBAL
{

    /**
     * Operation counter
     * @var int
     */
    protected $opCount = 0;

    /**
     * Start microtime (will change for each operation)
     * @var float
     */
    protected $opStartTime = 0;

    /**
     *
     * @var array 
     */
    protected $config = array();

    /**
     *
     * @var array
     */
    protected static $instances = array();

    /**
     *
     * @var string
     */
    protected static $activeInstance = false;

    abstract public function connect();

    abstract public function isConnected();

    abstract public function close();

    abstract public function isError();

    abstract public function getErrorCode();

    abstract public function getErrorMessage();

    abstract public function getRowCount();

    /**
     *
     * @param string $name
     * @return mixed|null 
     */
    public function getConfig($name = null)
    {
        if (empty($name))
            return $this->config;

        if (isset($this->config[$name])) {
            return $this->config[$name];
        }else
            return null;
    }

    public function getOpCount()
    {
        return $this->opCount;
    }

    public function getOpElapsedTime()
    {
        $et = \Komet\Date::elapsedTime($this->opStartTime, microtime(true), 4, true);
        $this->opStartTime = 0;
        return $et;
    }

    public function opBegin($triggerName = null, array &$triggerData = null)
    {
        $this->opCount++;
        $this->opStartTime = microtime(true);
        $this->connect();

        if (!empty($triggerName)) {
            $triggerData["driver"] = $this->config["driver"];
            \Komet\K::app()->trigger("dbal_before_" . $triggerName, $triggerData);
        }
    }

    public function opEnd($result = null, $triggerName = null, array &$triggerData = null)
    {
        $rowCount = $this->getRowCount();
        $logMsg = $triggerName;

        if ($this->isError()) {
            $level = \Komet\Logger\Logger::ERROR;
            $logMsg .= " ERROR: " . $this->getErrorMessage();
        } else {
            $level = \Komet\Logger\Logger::DEBUG;
        }

        $triggerData["driver"] = $this->config["driver"];

        if (!empty($triggerName)) {
            if ($this->isError()) {
                $triggerData["level"] = $level;
                $triggerData["error"] = $this->getErrorMessage();
            } else {
                $triggerData["level"] = $level;
                $triggerData["error"] = null;
            }
            $triggerData["rowCount"] = $rowCount;
            $triggerData["result"] = $result;
            \Komet\K::app()->trigger("dbal_" . $triggerName, $triggerData);
        }

        $this->log($level, $logMsg, array(
            "className" => get_class($this),
            "driver" => $this->config["driver"],
            "opCount" => $this->opCount,
            "rowCount" => $rowCount,
            "query" => isset($triggerData["query"]) ? $triggerData["query"] : null,
            "elapsedTime" => $this->getOpElapsedTime(),
        ));
    }

    public function log($level = 100, $message = null, $data = null)
    {
        $triggerData = array(
            "instance" => &$this,
            "logMessage" => &$message,
            "logData" => &$data
        );

        if (($level >= 300) && ($level < 400)) {
            \Komet\K::app()->trigger("dbal_warning", $triggerData);
        } elseif (($level >= 400) && ($level < 500)) {
            \Komet\K::app()->trigger("dbal_error", $triggerData);
        } elseif ($level >= 500) {
            \Komet\K::app()->trigger("dbal_critical", $triggerData);
        }

        \Komet\K::app()->logger->write($level, $message, $data, "DBAL");
    }

    public static function getInstance($name = null)
    {
        if (($name == null) && (static::$activeInstance != false)) {
            return static::$instances[static::$activeInstance];
        } elseif (isset(static::$instances[$name])) {
            return static::$instances[$name];
        }
        return false;
    }

    public static function setInstance($name, AbstractDBAL $inst = null)
    {
        if (func_num_args() == 1) {
            static::$activeInstance = $name;
        } else {
            static::$instances[$name] = $inst;
        }
    }

}