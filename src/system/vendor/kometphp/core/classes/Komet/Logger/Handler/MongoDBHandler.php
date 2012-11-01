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
class MongoDBHandler extends AbstractHandler
{

    /**
     *
     * @var \MongoCollection
     */
    protected $mongoCollection;

    public function __construct(\Mongo $mongo, $database, $collection, $channel = null, $level = array(100, 200, 300, 400, 500), $bubble = true)
    {
        parent::__construct($channel, $level, $bubble);
        $this->formatter = function($record) {
                    return $record;
                };
        $this->mongoCollection = $mongo->selectCollection($database, $collection);
    }

    public function log($record)
    {
        return $this->mongoCollection->save($record) != false;
    }

}