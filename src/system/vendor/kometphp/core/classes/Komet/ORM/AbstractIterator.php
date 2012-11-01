<?php

/**
 * Part of the KometPHP Framework
 * 
 * @package Komet/ORM
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */

namespace Komet\ORM;

/**
 * Iterator for \Komet\ORM\AbstractRecord
 * 
 * @package Komet/ORM
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */
abstract class AbstractIterator implements \Iterator, \Countable
{

    /**
     *
     * @var string The record class (model)
     */
    protected $recordClass;

    public function __construct($recordClass)
    {
        $this->recordClass = $recordClass;
    }

    /**
     * Casts a document to a new instance specified in the $recordClass
     * @param array $item
     * @return AbstractRecord|false
     */
    protected function cast($item)
    {
        if (!is_array($item))
            return false;
        $rc = $this->recordClass;
        return new $rc($item, true);
    }

    /**
     * Returns the first record
     * @return AbstractRecord|false
     */
    abstract public function getFirst();

    /**
     * Return the next object to which this cursor points, and advance the cursor
     * @return AbstractRecord|false
     */
    abstract public function getNext();

    /**
     * Alias for current()
     * @return AbstractRecord|false
     */
    abstract public function getCurrent();

    /**
     * Returns the collection of records
     * @return array[AbstractRecord]
     */
    abstract public function getAll();

    /**
     * Returns the query iterator
     * @return \MongoCursor|\PDOStatement
     */
    abstract public function getCursorIterator();
}