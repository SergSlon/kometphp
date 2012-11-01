<?php

/**
 * Part of the KometPHP Framework
 * 
 * @package Komet
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */
// Setting up error reporting and display errors for the startup process
error_reporting(-1);
ini_set('display_errors', 1);

// Set default timezone to avoid timezone warnings
date_default_timezone_set('UTC');

$ds = DIRECTORY_SEPARATOR;
$rootpath = realpath(dirname(__DIR__)) . $ds;
$environment = array(
    "name" => isset($_SERVER['APPLICATION_ENV']) ? $_SERVER['APPLICATION_ENV'] : (isset($_ENV['APPLICATION_ENV']) ? $_ENV['APPLICATION_ENV'] : "dev"),
    "start_time" => microtime(true),
    "start_memory" => memory_get_usage(),
    "paths"=>array(
        "root" => $rootpath,
        "vendor" => $rootpath . "vendor{$ds}",
        "core" => $rootpath . "vendor{$ds}kometphp{$ds}core{$ds}src{$ds}"
    )
);

// Resource requirements
ini_set("memory_limit", "128M");
ini_set("max_execution_time", 60);
ini_set("post_max_size", "16M");
ini_set("upload_max_filesize", "16M");

// Locale and charset
ini_set("default_charset", "UTF-8");
mb_internal_encoding("UTF-8");
ini_set("default_mimetype", "text/html");
setlocale(LC_ALL, "en_US.UTF8");

// Session
ini_set("session.use_only_cookies", true); # do not use PHPSESSID in urls
ini_set("session.use_trans_sid", false); # do not use PHPSESSID in urls
ini_set("session.hash_function", 1); # use sha1 algorithm (160 bits)

include_once $environment["paths"]["core"] . "bootstrap.php";
?>