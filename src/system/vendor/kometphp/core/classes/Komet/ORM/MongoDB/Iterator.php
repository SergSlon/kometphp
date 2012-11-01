<?php

/**
 * Part of the KometPHP Framework
 * 
 * @package Komet/ORM
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */

namespace Komet\ORM\MongoDB;

/**
 * Iterator for \Komet\ORM\Mongo\Record
 * 
 * @package Komet/ORM
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */
class Iterator extends \Komet\ORM\AbstractIterator
{

    /**
     *
     * @var \MongoCursor
     */
    protected $cursorIterator;

    public function __construct($recordClass, \MongoCursor $cursorIterator)
    {
        parent::__construct($recordClass);
        $this->cursorIterator = $cursorIterator;
    }

    /**
     * Counts found records
     * @return int
     */
    public function count()
    {
        return $this->cursorIterator->count(true);
    }

    /**
     * Fetches record at current cursor. Alias for current()
     * @return MongoRecord|false
     */
    public function current()
    {
        return $this->cast($this->cursorIterator->current());
    }

    /**
     * Returns the current result's _id
     * @return string The current result's _id as a string.
     */
    public function key()
    {
        return $this->cursorIterator->key();
    }

    /**
     * Advances the cursor to the next result
     */
    public function next()
    {
        $this->cursorIterator->next();
    }

    /**
     * Moves the cursor to the beginning of the result set
     */
    public function rewind()
    {
        $this->cursorIterator->rewind();
    }

    /**
     * Checks if the cursor is reading a valid result.
     * 
     * @return boolean
     */
    public function valid()
    {
        return $this->cursorIterator->valid();
    }

    /**
     * Fetches first record and rewinds the cursor
     * @return Record|false
     */
    public function getFirst()
    {
        $this->cursorIterator->rewind();
        return $this->cast($this->cursorIterator->current());
    }

    /**
     * Return the next record to which this cursor points, and advance the cursor
     * @return Record|false Next record or false if there's no more records
     */
    public function getNext()
    {
        if ($this->cursorIterator->hasNext()) {
            return $this->cast($this->cursorIterator->getNext());
        }
        return false;
    }

    /**
     * Fetches the record at current cursor. Alias for current()
     * @return Record|false
     */
    public function getCurrent()
    {
        return $this->current();
    }

    /**
     * Fetches all records and rewinds the cursor
     * @param boolean $asRecords
     * @return array[Record|array] Array of records or arrays (depends on $asRecords)
     */
    public function getAll($asRecords = true)
    {
        $all = array();
        $this->cursorIterator->rewind();
        foreach ($this->cursorIterator as $id => $doc) {
            if ($asRecords)
                $all[$id] = $this->cast($doc);
            else
                $all[$id] = $doc;
        }
        return $all;
    }

    /**
     * 
     * @return \MongoCursor
     */
    public function getCursorIterator()
    {
        return $this->cursorIterator;
    }

}