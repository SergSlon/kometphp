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
class PDOHandler extends AbstractHandler
{

    /**
     *
     * @var \PDO
     */
    protected $pdo;

    /**
     *
     * @var string
     */
    protected $tableName = "logs";

    /**
     *
     * @var \PDOStatement
     */
    protected $statement;

    /**
     *
     * @var boolean
     */
    protected $initialized = false;

    public function __construct(\PDO $pdo, $tableName = "logs", $channel = null, $level = array(100, 200, 300, 400, 500), $bubble = true)
    {
        $this->pdo = $pdo;
        $this->tableName = $tableName;
        parent::__construct($channel, $level, $bubble);
    }

    public function log($record)
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        $message = $this->format($record);
        return $this->statement->execute(array(
                    'channel' => $record['channel'],
                    'level' => $record['level'],
                    'message' => mysql_real_escape_string($message),
                    'microtime' => $record['microtime'],
                ));
    }

    protected function initialize()
    {
        $this->pdo->exec(
                'CREATE TABLE IF NOT EXISTS ' . $this->tableName . ' '
                . '(ID bigint(20) unsigned NOT NULL AUTO_INCREMENT, channel VARCHAR(255), level INTEGER, message LONGTEXT, microtime VARCHAR(50), PRIMARY KEY (ID));'
        );
        $this->statement = $this->pdo->prepare(
                'INSERT INTO ' . $this->tableName . ' (channel, level, message, microtime) VALUES (:channel, :level, :message, :microtime);'
        );

        $this->initialized = true;
    }

}