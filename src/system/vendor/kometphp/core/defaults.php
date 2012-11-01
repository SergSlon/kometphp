<?php

/**
 * Default App configuration
 * 
 * Part of the KometPHP Framework
 * 
 * @package Komet
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */
return array(
    "error_reporting" => -1,
    "display_errors" => false,
    "session_autostart" => true,
    "index_file" => $_SERVER["SCRIPT_NAME"] ? basename($_SERVER["SCRIPT_NAME"]) : "",
    "profiler_enabled"=>false,
    "main_module"=>"main", //be sure your main module has controllers
    // the modules will be created in the specified order, excepting the main one that will always be the first
    "modules" => array(
        "profiler"=>array(
            "path"=>"system/vendor/kometphp/core/modules/profiler/"
        )
    ),
    "hmvc" => array( //default HMVC engine configuration
        "router" => function(Komet\Request $request) {
            return new \Komet\HMVC\Router($request);
        },
        "controller_prefix" => "\Controller_", # default controller prefix, including class namespace
        "default_controller" => "Index", # without prefix nor namespace
        "default_resource_format" => "html", # default resource format (determined by url extension)
        "default_content_type" => "text/plain",
    ),
    "logger" => array(
        "handlers" => array(
            0 => function(\Komet\App $app) {
                return new Komet\Logger\Handler\StreamHandler($app->path("logs") . "app.log", array(100, 200, 300, 400, 500), true);
            }
        )
    )
);