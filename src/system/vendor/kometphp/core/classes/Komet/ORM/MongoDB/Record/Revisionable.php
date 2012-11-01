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
 * @property-read string $date_revised Fecha de la última revisión guardada
 * 
 */
abstract class Revisionable extends Timestampable
{

    protected static $revCollectionSuffix = "_rev";

    public function __beforeUpdate()
    {
        //get previous stored data
        $rev = static::findBy("_id", $this->_id)->getFirst()->getVars();

        //create DBRef
        $rev["ref"] = \MongoDBRef::create(static::$collectionName, $rev["_id"]);

        // unset unwanted fields for the revision
        unset($rev["_id"]);
//        if (isset($rev["date_created"]))
//            unset($rev["date_created"]);
//
//        if (isset($rev["date_modified"]))
//            unset($rev["date_modified"]);

        if (isset($rev["last_rev"]))
            unset($rev["last_rev"]);

        $rev["date_revised"] = new \MongoDate();

        // insert revision
        $revData = $this->dbal()->insert(static::$collectionName . static::$revCollectionSuffix, $rev);

        if ($revData) {
            $this->vars["last_rev"] = \MongoDBRef::create(static::$collectionName . static::$revCollectionSuffix, $revData["_id"]);
        } else {
            \Komet\K::logger()->error("Cannot create a new revision for collection " . static::$collectionName, $revData, "ORM");
        }
        parent::__beforeUpdate();
    }

    // cannot set revisions manually
    public function __setRevisions()
    {
        return false;
    }

    /**
     * @todo implement restoreRevision
     */
    public function restoreRevision($rev = 0)
    {
        ;
    }

    public function __setDateRevised()
    {
        return false;
    }

    public function __getDateRevised()
    {
        if (isset($this->last_rev))
            return date('Y-M-d H:i:s', $this->vars["last_rev"]["date_modified"]->sec);
        else
            return false;
    }

}