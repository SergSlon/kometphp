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
 * PDO wrapper and abstraction layer
 * 
 * @package Komet/DBAL
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 * 
 * @todo PDO: port previous functions (find, search, select, update, ...)
 */
class PDO extends AbstractDBAL
{

    /**
     * 
     * @var \PDO
     */
    protected $pdo = null;
    protected $lastRowCount = 0;

    /**
     * 
     * @param string $name Instance name
     * @param array $config Connection configuration
     */
    public function __construct(array $config = array())
    {
        $config = array_merge(array(
            "name" => "default",
            "driver" => "mysql",
            "host" => "localhost",
            "port" => 3306,
            "schema" => null,
            "charset" => "utf8",
            "collate" => "utf8_general_ci",
            "username" => "root",
            "password" => null,
            "persistent" => false,
            "autoconnect" => false,
            "options" => array(\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC)
                ), $config);

        // DSN string
        $config["dsn"] = "{$config["driver"]}:host={$config["host"]};port={$config["port"]};dbname={$config["schema"]}";

        // Default fetch mode
        if (!isset($config["options"][\PDO::ATTR_DEFAULT_FETCH_MODE]))
            $config["options"][\PDO::ATTR_DEFAULT_FETCH_MODE] = \PDO::FETCH_ASSOC;

        $this->config = $config;

        // Autoconnect?
        if ($config["autoconnect"]) {
            $this->connect();
        }

        // Set instance to the static scope
        $instName = $config["name"];
        static::$instances[$instName] = $this;
        if (static::$activeInstance == false) {
            static::$activeInstance = $config["name"];
        }
    }

    ####################### INTERFACE METHODS:

    public function connect()
    {
        if (!$this->isConnected()) {
            \Komet\K::app()->trigger("dbal_before_pdo_connect", $this);

            try {
                $this->pdo = new \PDO($this->config["dsn"], $this->config["username"], $this->config["password"], $this->config["options"]);
                if (preg_match("/mysql/i", $this->config["driver"]) > 0) {
                    $this->pdo->exec("SET NAMES '{$this->config["charset"]}' COLLATE '{$this->config["collate"]}'");
                }
            } catch (Exception $exc) {
                $this->log(500, $exc->getTraceAsString());
            }

            \Komet\K::app()->trigger("dbal_pdo_connect", $this);
            return $this->isConnected();
        }
        return false;
    }

    public function isConnected()
    {
        return is_object($this->pdo);
    }

    public function close()
    {
        if ($this->isConnected()) {
            unset($this->pdo);
            return true;
        }
        return false;
    }

    public function isError()
    {
        if (!$this->isConnected())
            return false;

        return preg_match("/^0+$/", $this->pdo->errorCode()) == false;
    }

    public function getErrorCode()
    {
        if (!$this->isConnected())
            return false;
        return $this->pdo->errorCode();
    }

    public function getErrorMessage()
    {
        if (!$this->isConnected())
            return false;
        implode("; ", $this->pdo->errorCode());
    }

    public function getRowCount()
    {
        if (!$this->isConnected())
            return false;

        return $this->lastRowCount;
    }

    public function opBegin($triggerName = null, array &$triggerData = null)
    {
        parent::opBegin("pdo_" . $triggerName, $triggerData);
    }

    public function opEnd($result = null, $triggerName = null, array &$triggerData = null)
    {
        parent::opEnd($result, "pdo_" . $triggerName, $triggerData);
    }

    ####################### END INTERFACE METHODS
    ####################### DRIVER METHODS:

    /**
     * 
     * @return \PDO
     */
    public function getPdo()
    {
        $this->connect();
        return $this->pdo;
    }

    ####################### END DRIVER METHODS:
}