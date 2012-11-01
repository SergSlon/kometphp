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
 * Implements the singleton pattern
 * 
 * @package Komet
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */
abstract class Singleton
{

    private static $_instances = array();

    /**
     * Prevents direct creation of object.
     *
     * @param array $args
     * @return void
     */
    protected function __construct($args)
    {
        
    }

    /**
     * Prevents to clone the instance.
     *
     * @return void
     */
    final private function __clone()
    {
        
    }

    /**
     *
     * @return \Komet\Singleton 
     */
    public static function getInstance()
    {
        $class_name = get_called_class();
        if (!isset(self::$_instances[$class_name])) {
            self::$_instances[$class_name] = new $class_name(func_get_args());
        }
        return self::$_instances[$class_name];
    }

}