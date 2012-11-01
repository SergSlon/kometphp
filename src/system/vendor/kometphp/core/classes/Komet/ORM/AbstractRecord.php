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
 * Active Record model
 * 
 * @package Komet/ORM
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */
abstract class AbstractRecord extends \Komet\Object
{

    /**
     * MD5 checksum for detecting changes
     * @var string
     */
    protected $checksum;

    /**
     * 
     * @param array|\stdClass|AbstractRecord $data Will be assigned using setVars
     * @param boolean $rawAssign Raw assignment of vars (without get/set override)
     */
    public function __construct($data = null, $rawAssign = false)
    {
        if (empty($data)) {
            $data = array();
        } elseif ($data instanceof \stdClass) {
            $data = (array) $data;
        } elseif ($data instanceof static) {
            $data = $data->getRawVars();
        }
        if (is_array($data)) {
            if ($rawAssign)
                $this->setRawVars($data);
            else
                $this->setVars($data);
        }
        if($this->exists()){
            $this->checksum = md5(serialize($this->vars));
        }
    }

    public function isModified()
    {
        return md5(serialize($this->vars)) != $this->checksum;
    }

    public function trigger($eventName, &$data = null)
    {
        \Komet\K::app()->trigger("orm." . $eventName, $this);
        $eventFn = "__" . \Komet\Format::camelize($eventName);
        if (method_exists($this, $eventFn)) {
            return $this->$eventFn($this, $data);
        }
        return true;
    }

    /**
     * @return bool|string|MongoId|int
     */
    abstract public function save();

    /**
     * @return bool
     */
    abstract public function delete();

    /**
     * @return bool
     */
    abstract public function exists();

    public function __isset($name)
    {
        return $this->__override(__FUNCTION__, $name);
    }

    public function __get($name)
    {
        return $this->__override(__FUNCTION__, $name);
    }

    public function __set($name, $value)
    {
        return $this->__override(__FUNCTION__, $name, $value);
    }

    public function __unset($name)
    {
        return $this->__override(__FUNCTION__, $name);
    }

    public function __call($name, $args)
    {
        if (method_exists($this, $name)) {
            return call_user_func_array(array($this, $name), $args);
        }
    }

    public function getVars()
    {
        $vars = array();
        foreach ($this->vars as $k => $v) {
            $vars[$k] = $this->__get($k);
        }
        return $vars;
    }

    public function setVars($vars)
    {
        if ($vars instanceof \stdClass) {
            $vars = (array) $vars;
        }
        foreach ($vars as $k => $v) {
            $this->__set($k, $v);
        }
    }

    public function getRawVars()
    {
        return parent::getVars();
    }

    public function setRawVars($vars)
    {
        parent::setVars($vars);
    }

    protected function __override()
    {
        $args = func_get_args();
        $fn = array_shift($args);
        $name = array_shift($args);
        $method_name = $fn . ucfirst(\Komet\Format::camelize($name));
        if (method_exists($this, $method_name)) {
            return call_user_func_array(array($this, $method_name), $args);
        } else {
            array_unshift($args, $name);
            return call_user_func_array("parent::$fn", $args);
        }
    }

}