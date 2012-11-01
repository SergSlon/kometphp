<?php

/**
 * Part of the KometPHP Framework
 * 
 * @package Komet/HMVC
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */

namespace Komet\HMVC;

/**
 * @package Komet/HMVC
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */
abstract class AbstractRouter
{

    /**
     *
     * @var Request
     */
    protected $request;

    public function __construct(\Komet\Request $request)
    {
        $this->request = $request;
    }

    /**
     * 
     * @return \Komet\Request
     */
    public function request()
    {
        return $this->request;
    }

    /**
     * @return Response Returns the controller response
     */
    abstract public function execute();
    
    abstract public function moduleName();
}