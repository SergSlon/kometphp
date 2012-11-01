<?php

/**
 * Part of the KometPHP Framework
 * 
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */

namespace Demo;

/**
 * 
 * Demo module class
 * 
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */
class Demo extends \Komet\Module
{
    protected function __construct($name, array $config = array())
    {
        parent::__construct($name, $config);
        
        // Initialize your module here
    }
}