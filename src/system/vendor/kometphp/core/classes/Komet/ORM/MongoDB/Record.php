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
 * Active Record Model for \Komet\DBAL\MongoDB connections
 * 
 * @package Komet/ORM
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 * 
 * @method void __beforeUpdate() Magic function that will be triggered before the record is updated
 * @method void __onUpdate(mixed $result) Magic function that will be triggered after the record is updated
 * @method void __beforeCreate() Magic function that will be triggered before the record is created
 * @method void __onCreate(mixed $result) Magic function that will be triggered after the record is created
 * @method void __beforeSave() Magic function that will be triggered before the record is created or updated
 * @method void __onSave(mixed $result) Magic function that will be triggered after the record is created or updated
 * @method boolean __validateSave() Magic function that will be triggered before the record is created or updated, and which return
 *  value determines if the record can be created/updated or not
 * @method void __beforeDelete() Magic function that will be triggered before the record is created
 * @method void __onDelete(mixed $result) Magic function that will be triggered after the record is created
 * @method boolean __validateDelete() Magic function that will be triggered before the record is deleted, and which return
 *  value determines if the record can be deleted or not
 * 
 * @property-read \MongoId $_id Document identifier
 * 
 */
abstract class Record extends \Komet\ORM\AbstractRecord
{

    /**
     * collection name
     * @var string 
     */
    protected static $collectionName = null;

    public function __construct($data = null, $rawAssign = false)
    {
        // an _id is passed?
        if (is_string($data) or ($data instanceof \MongoId)) {
            $data = static::findBy("_id", $data, null, null, 1);
            if (!$this->exists($data)) {
                \Komet\K::logger()->error("This " . static::getCollectionName() . " record does not exist", array($data, $this), "ORM");
                $data = array();
            }
        }
        parent::__construct($data, $rawAssign);
    }

    public static function getCollectionName()
    {
        return static::$collectionName;
    }

    public function __getId()
    {
        if (isset($this->vars["_id"])) {
            return $this->vars["_id"];
        }
        return false;
    }

    /**
     * Upserts the record
     * @return true|false|-1|\MongoId  (-1 = nothing to update, no changes)
     */
    public function save()
    {
        $result = false;
        if ($this->trigger("validate_save")) {
            if ($this->isModified()) {
                $this->trigger("before_save");
                if ($this->exists()) {
                    $this->trigger("before_update");
                    $vars = $this->vars;
                    unset($vars["_id"]); // prevent duplicates
                    $result = $this->dbal()->where(array("_id" => $this->vars["_id"]))->update(static::getCollectionName(), $vars);
                    $this->trigger("on_update", $result);
                    $result = is_array($result) && isset($result["updatedExisting"]) && ($result["updatedExisting"] == true);
                } elseif (!empty($this->vars)) {
                    $this->trigger("before_create");
                    $data = $this->dbal()->insert(static::getCollectionName(), $this->vars);
                    if ($data != false) {
                        $this->vars = $data;
                        $result = $data["_id"];
                    }
                    $this->trigger("on_create", $result);
                }
                if ($result) {
                    $this->checksum = md5(serialize($this->vars));
                }
                $this->trigger("on_save", $result);
            }else
                $result = -1;
        }
        return $result;
    }

    public function delete()
    {
        $result = false;
        if ($this->trigger("validate_delete")) {
            if ($this->exists()) {
                $this->trigger("before_delete");
                if ($this->dbal()->where(array("_id" => $this->vars["_id"]))->delete(static::getCollectionName())) {
                    $result = true;
                }
                if ($this->dbal()->isError()) {
                    $result = $this->dbal()->getDB()->lastError();
                }
                $this->trigger("on_delete", $result);
            }
            $this->vars = array();
        }
        return $result;
    }

    public function exists($vars = null)
    {
        if ($vars === null)
            $vars = $this->vars;
        return is_array($vars) && isset($vars["_id"]) && (!empty($vars["_id"]));
    }

    public function getRef()
    {
        if ($this->exists())
            return \MongoDBRef::create(static::getCollectionName(), $this->_id);
        else
            return false;
    }

    /**
     * 
     * @param string $instanceName
     * @return \Komet\DBAL\MongoDB
     */
    public static function dbal($instanceName = null)
    {
        return \Komet\DBAL\MongoDB::getInstance($instanceName);
    }

    /**
     *
     * @param string $where
     * @param string $orderBy
     * @param int $offset
     * @param int $limit
     * @param string $select
     * @param string $collection
     * @return Iterator
     */
    public static function find($where = null, $orderBy = null, $offset = null, $limit = null, $select = null, $collection = null)
    {
        if (!empty($where)) {
            static::dbal()->where($where);
        }
        if (!empty($orderBy)) {
            static::dbal()->orderBy($orderBy);
        }
        if (!empty($offset)) {
            static::dbal()->offset($offset);
        }
        if (!empty($limit)) {
            static::dbal()->limit($limit);
        }
        if (!empty($select)) {
            static::dbal()->select($select);
        }

        return static::getIterator(static::dbal()->get(empty($collection) ? static::getCollectionName() : $collection));
    }

    /**
     * 
     * @param string $field
     * @param mixed $value
     * @param string $orderBy
     * @param int $offset
     * @param int $limit
     * @param string $select
     * @param string $collection
     * @return Iterator
     */
    public static function findBy($field, $value, $orderBy = null, $offset = null, $limit = null, $select = null, $collection = null)
    {
        if (($field == "_id") and !($value instanceof \MongoId))
            $value = new \MongoId($value);
        return static::find(array($field => $value), $orderBy, $offset, $limit, $select, $collection);
    }

    /**
     * 
     * @param string $fields
     * @param string $value
     * @param string $orderBy
     * @param int $offset
     * @param int $limit
     * @param string $select
     * @param string $exact_match
     * @param string $ternary_operator
     * @param string $collection
     * @return Iterator
     */
    public static function search($fields, $value, $orderBy = null, $offset = null, $limit = null, $select = null, $exact_match = true, $ternary_operator = "OR", $collection = null)
    {
        $regex = $exact_match ? (new \MongoRegex("/^$value$/")) : (new \MongoRegex("/$value/i"));
        foreach ($fields as $field) {
            if ($ternary_operator == "OR") {
                static::dbal()->orWhere(array($field => $regex));
            } else {
                static::dbal()->where(array($field => $regex));
            }
        }
        if (!empty($orderBy)) {
            static::dbal()->orderBy($orderBy);
        }
        if (!empty($offset)) {
            static::dbal()->offset($offset);
        }
        if (!empty($limit)) {
            static::dbal()->limit($limit);
        }
        if (!empty($select)) {
            static::dbal()->select($select);
        }
        return static::getIterator(static::dbal()->get(empty($collection) ? static::getCollectionName() : $collection));
    }

    /**
     * @return Iterator
     */
    public static function getIterator(\MongoCursor $cursorIterator)
    {
        return new Iterator(get_called_class(), $cursorIterator);
    }

}