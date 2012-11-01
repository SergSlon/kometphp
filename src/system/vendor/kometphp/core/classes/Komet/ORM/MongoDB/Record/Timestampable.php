<?php

/**
 * Part of the KometPHP Framework
 * 
 * @package Komet/ORM
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */

namespace Komet\ORM\MongoDB\Record;

/**
 * 
 * @package Komet/ORM
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 * 
 * @property-read string $date_created Fecha de creación del registro
 * @property-read string $date_modified Fecha de la última modificación
 */
abstract class Timestampable extends \Komet\ORM\MongoDB\Record
{

    public function __beforeCreate()
    {
        $this->vars["date_created"] = new \MongoDate();
    }

    public function __beforeUpdate()
    {
        $this->vars["date_modified"] = new \MongoDate();
    }

    public function __getDateModified()
    {
        if (isset($this->date_modified))
            return date('Y-M-d H:i:s', $this->get("date_modified")->sec);
        else
            return false;
    }

    public function __getDateCreated()
    {
        if (isset($this->date_created))
            return date('Y-M-d H:i:s', $this->get("date_created")->sec);
        else
            return false;
    }

    public function __setDateModified()
    {
        return false;
    }

    public function __setDateCreated()
    {
        return false;
    }

}