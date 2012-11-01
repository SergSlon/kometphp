<?php

/**
 * Part of the KometPHP Framework
 * 
 * @package Komet/ORM
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */

namespace Komet\ORM\PDO;

/**
 * Iterator for \Komet\ORM\PDO\Record
 * 
 * @package Komet/ORM
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 * 
 * @todo Implement \Komet\ORM\PDO\Iterator
 */
class Iterator extends \Komet\ORM\AbstractIterator
{

    /**
     *
     * @var \PDOStatement
     */
    protected $cursorIterator;

    public function __construct($recordClass, \PDOStatement $cursorIterator)
    {
        parent::__construct($recordClass);
        $this->cursorIterator = $cursorIterator;
    }
}