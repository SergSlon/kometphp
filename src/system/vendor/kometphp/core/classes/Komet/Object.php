<?php

/**
 * Part of the KometPHP Framework
 * 
 * @package Komet
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */

namespace Komet;

/**
 * @package Komet
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */
class Object implements \ArrayAccess
{

    /**
     *
     * @var array
     */
    protected $vars = array();

    public function getVars()
    {
        return $this->vars;
    }

    public function setVars($vars)
    {
        if ($vars instanceof \stdClass) {
            $vars = (array) $vars;
        }
        foreach ($vars as $k => $v) {
            $this->set($k, $v);
        }
    }

    public function get($name)
    {
        return isset($this->vars[$name]) ? $this->vars[$name] : null;
    }

    public function set($name, $value)
    {
        $this->vars[$name] = $value;
    }

    public function __isset($name)
    {
        return isset($this->vars[$name]);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    public function __unset($name)
    {
        if (isset($this->vars[$name])) {
            unset($this->vars[$name]);
            return true;
        }else
            return false;
    }

    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    public function offsetSet($offset, $value)
    {
        return $this->__set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        return $this->__unset($offset);
    }

    public function __toString()
    {
        return print_r($this->vars, true);
    }

    /**
     * Takes a classname and returns the actual classname for an alias or just the classname
     * if it's a normal class.
     *
     * @param   string  classname to check
     * @return  string  real classname
     */
    public static function getRealClass($class)
    {
        static $classes = array();

        if (!array_key_exists($class, $classes)) {
            $reflect = new ReflectionClass($class);
            $classes[$class] = $reflect->getName();
        }

        return $classes[$class];
    }

    /**
     * Gets all the public vars for an object.  Use this if you need to get all the
     * public vars of $this inside an object.
     *
     * @return	array
     */
    public static function getObjectPublicVars($obj)
    {
        return get_object_vars($obj);
    }

    /**
     * Retrieves all constants (or the specified one) from a class using Reflection
     * 
     * @param string $class_name
     * @param string $constant_name specific constant value
     * @return mixed 
     */
    public static function getClassConstants($class_name, $constant_name = null)
    {
        $reflect = new ReflectionClass($class_name);
        $constants = $reflect->getConstants();

        if (!empty($constant_name))
            return $constants[$constant_name];
        else
            return $constants;
    }

    /**
     * Returns the constant value of the called class
     * @param string $name constant name
     * @return mixed 
     */
    public static function getConstant($name)
    {
        return defined("static::$name") ? constant("static::$name") : null;
    }

    /**
     * 
     * @param mixed $var
     * @return string primitive type or class name
     */
    public static function getType($var)
    {
        return is_object($var) ? get_class($var) : gettype($var);
    }

    /**
     * Returns a new instance of the called class
     * @return \Komet\Object 
     */
    public static function newInstance()
    {
        return new static(func_get_args());
    }

}