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
 * KometPHP facade class
 */
class K
{

    /**
     * 
     * @return string
     */
    public static function version()
    {
        return VERSION;
    }

    /**
     * 
     * @param string $name
     * @return App
     */
    public static function app($name = "default")
    {
        return App::getInstance($name);
    }

    /**
     * 
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function config($name = null, $default = null)
    {
        return self::app()->config($name, $default);
    }

    /**
     * 
     * @param string $name
     * @return string
     */
    public static function path($name = "root")
    {
        return self::app()->path($name);
    }

    /**
     * 
     * @param string $name
     * @return string
     */
    public static function url($name = "base")
    {
        return self::app()->getRequest()->url($name);
    }

    /**
     * 
     * @return Logger\Logger
     */
    public static function logger()
    {
        return self::app()->logger;
    }

    /**
     * If any parameter is passed, calls the getHtml function of the 'asset' app object,
     * and returns the generated HTML, otherwise returns the Asset object
     * 
     * @param string|array $file This can be one file or an array of files.
     * Each item of the array can be a string or an array containing filename and
     * attributes array.
     * 
     * @param string|int $version Resource version
     * @param string $type Type of resource (css, js or img)
     * @param array $attributes HTML extra / override attributes
     * @return string|Asset
     * @throws \RuntimeException 
     */
    public static function asset()
    {
        if (func_num_args() > 0) {
            return call_user_func_array(array(self::app()->asset, "getHtml"), func_get_args());
        } else {
            return self::app()->asset;
        }
    }

    /**
     * 
     * @return Request
     */
    public static function request()
    {
        return self::app()->getRequest();
    }

    /**
     * $_SERVER
     * 
     * @param string $index Array key
     * @param mixed $validation FILTER_* constant value or regular expression.
     *      If true is passed, the function will only check if the $index is set or not
     * @param mixed $default Default value if the variable is not set or regexp is false
     * @return mixed
     */
    public static function server($index = null, $validation = null, $default = null)
    {
        return self::request()->server($index, $validation, $default);
    }

    /**
     * $_ENV
     * 
     * @param string $index Array key
     * @param mixed $validation FILTER_* constant value or regular expression.
     *      If true is passed, the function will only check if the $index is set or not
     * @param mixed $default Default value if the variable is not set or regexp is false
     * @return mixed
     */
    public static function env($index = null, $validation = null, $default = null)
    {
        return self::request()->env($index, $validation, $default);
    }

    /**
     * $argv
     * 
     * @param string $index Array key
     * @param mixed $validation FILTER_* constant value or regular expression.
     *      If true is passed, the function will only check if the $index is set or not
     * @param mixed $default Default value if the variable is not set or regexp is false
     * @return mixed
     */
    public static function arg($index = null, $validation = null, $default = null)
    {
        return self::request()->arg($index, $validation, $default);
    }

    /**
     * php://input variable (mixed php://input and GET)
     * 
     * @param string $index Array key
     * @param mixed $validation FILTER_* constant value or regular expression.
     *      If true is passed, the function will only check if the $index is set or not
     * @param mixed $default Default value if the variable is not set or regexp is false
     * @return mixed
     */
    public static function input($index = null, $validation = null, $default = null)
    {
        return self::request()->input($index, $validation, $default);
    }

    /**
     * $_POST
     * 
     * @param string $index Array key
     * @param mixed $validation FILTER_* constant value or regular expression.
     *      If true is passed, the function will only check if the $index is set or not
     * @param mixed $default Default value if the variable is not set or regexp is false
     * @return mixed
     */
    public static function post($index = null, $validation = null, $default = null)
    {
        return self::request()->post($index, $validation, $default);
    }

    /**
     * $_GET
     * 
     * @param string $index Array key
     * @param mixed $validation FILTER_* constant value or regular expression.
     *      If true is passed, the function will only check if the $index is set or not
     * @param mixed $default Default value if the variable is not set or regexp is false
     * @return mixed
     */
    public static function get($index = null, $validation = null, $default = null)
    {
        return self::request()->get($index, $validation, $default);
    }

    /**
     * $_COOKIE
     * 
     * @param string $index Array key
     * @param mixed $validation FILTER_* constant value or regular expression.
     *      If true is passed, the function will only check if the $index is set or not
     * @param mixed $default Default value if the variable is not set or regexp is false
     * @return mixed
     */
    public static function cookie($index = null, $validation = null, $default = null)
    {
        return self::request()->cookie($index, $validation, $default);
    }

    /**
     * 
     * @return HMVC\AbstractRouter|HMVC\Router
     */
    public static function router()
    {
        return self::request()->router;
    }

    /**
     * 
     * @param string $name
     * @return Module
     */
    public static function module($name = null)
    {
        return self::app()->getModule($name);
    }

    /**
     * 
     * @return HMVC\AbstractController
     */
    public static function controller()
    {
        return self::router()->controllerInstance;
    }

    /**
     * 
     * @return HMVC\AbstractView|HMVC\View
     */
    public static function view()
    {
        // get current view
    }

    /**
     * 
     * @return Response
     */
    public static function response()
    {
        // get current response
    }

    public static function redirect($url, $status = "301 Moved Permanently", $httpVersion = "1.1")
    {
        if (!empty($status)) {
            if (strpos(PHP_SAPI, 'cgi') === 0) {
                header("Status: $status");
            } else {
                header("HTTP/{$httpVersion}: $status");
            }
        }
        header("Location: $url");
        exit();
    }

}