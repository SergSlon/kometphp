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
 * Queries the client request
 * 
 * @package Komet
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */
class Request extends Input
{

    /**
     *
     * @var string
     */
    public $uri;

    /**
     *
     * @var HMVC\AbstractRouter
     */
    public $router;

    /**
     *
     * @var array 
     */
    protected $urls = array();

    /**
     *
     * @var array 
     */
    protected $tags = array();

    public function __construct($uri = null, array $superglobals = array())
    {
        parent::__construct($superglobals);
        $this->uri = empty($uri) ? null : '/' . trim(strval($uri), '/');
        $router_fn = \Komet\K::app()->config("hmvc.router");
        $this->router = $router_fn($this);

        if (empty($this->uri)) {
            $this->uri = $this->detectedUri;
            $this->extension = $this->detectedExtension;
        } elseif (empty($this->extension)) {
            $uriInfo = pathinfo($this->uri);
            if (!empty($uriInfo['extension'])) {
                $this->extension = $uriInfo['extension'];
                $this->uri = $uriInfo['dirname'] . '/' . $uriInfo['filename'];
            }
        }

        $index_file = \Komet\K::app()->config("index_file");
        $this->setUrl("root", $this->detectedHostUrl());
        $this->setUrl("base", $this->detectedBaseUrl());
        $this->setUrl("mvc", $this->detectedBaseUrl() . (empty($index_file) ? "" : $index_file . "/"));
        $this->setUrl("current", $this->url("mvc") . trim($this->uri, "/"));
        $this->setUrl("static", $this->url("base") . 'static/');

        \Komet\K::app()->trigger("request.create", $this);
    }

    /**
     *
     * @return string 
     */
    public function resource()
    {
        return $this->uri . (empty($this->extension) ? "" : "." . $this->extension);
    }

    /**
     *
     * @param string $name Some possible values: root (domain url), base, mvc, controller, action, current, static
     * @return string 
     */
    public function url($name = "base")
    {
        return $this->urls[$name];
    }

    /**
     *
     * @param string $name
     * @param string $value
     * @return string 
     */
    public function setUrl($name, $value)
    {
        return $this->urls[$name] = $value;
    }

    /**
     *
     * @param string $name
     * @return array
     */
    public function tags()
    {
        return $this->tags;
    }

    /**
     *
     * @param string $name
     */
    public function addTag($name)
    {
        if (is_array($name)) {
            foreach ($name as $n) {
                $this->tags[$n] = $n;
            }
        } else
            $this->tags[$name] = $name;
    }

    /**
     *
     * @param string $name
     * @return boolean
     */
    public function hasTag($name)
    {
        return isset($this->tags[$name]);
    }

    /**
     *
     * @param string $name
     */
    public function removeTag($name)
    {
        if (isset($this->tags[$name])) {
            unset($this->tags[$name]);
        }
    }

}