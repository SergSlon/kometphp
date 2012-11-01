<?php

/**
 * Part of the KometPHP Framework
 * 
 * @package Komet
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */
isset($environment) or exit('KometPHP bootstrap file must be loaded from within index.php!');
isset($rootpath) or exit('KometPHP cannot continue without specifying a root path');
$ds = DIRECTORY_SEPARATOR;

$syspath = isset($environment["paths"]["system"]) ? $environment["paths"]["system"] : $rootpath . "system{$ds}";
$environment["paths"] = array_merge($environment["paths"], array(
    "public" => $rootpath . "public{$ds}",
    "static" => $rootpath . "public{$ds}static{$ds}",
    "assets" => $rootpath . "public{$ds}static{$ds}assets{$ds}",
    "uploads" => $rootpath . "public{$ds}static{$ds}uploads{$ds}",
    "system" => $syspath,
    "config" => $syspath . "config{$ds}",
    "modules" => $syspath . "modules{$ds}",
    "data" => $syspath . "data{$ds}",
    "logs" => $syspath . "data{$ds}logs{$ds}",
    "tmp" => $syspath . "data{$ds}tmp{$ds}",
    "cache" => $syspath . "data{$ds}cache{$ds}",
    "vendor" => $syspath . "vendor{$ds}",
    "core" => $syspath . "vendor{$ds}kometphp{$ds}core{$ds}",
));

foreach ($environment["paths"] as $name => $path) {
    if (!is_dir($path)) {
        @mkdir($path, 0755, true);
    }
}

$environment = array_merge($environment, array(
    "name" => isset($_SERVER['APPLICATION_ENV']) ? $_SERVER['APPLICATION_ENV'] : (isset($_ENV['APPLICATION_ENV']) ? $_ENV['APPLICATION_ENV'] : "dev"),
    "start_time" => microtime(true),
    "start_memory" => memory_get_usage(),
    "app_class" => "\Komet\App",
    "fatal_message" => "<div style=\"font-size:16px; font-family:Helvetica,Arial,sans-serif; padding:20px 40px; color:#c90a0a;\"><h1>Something went wrong :(</h1> <p>
        Right now we are experiencing some problems, please come back in a few minutes. <br>
        Sorry for the inconvenience.</p></div>"
));

// Tell PHP to log errors in the app/logs directory
ini_set("error_log", $environment["paths"]["logs"] . DIRECTORY_SEPARATOR .
        "php_error.log");

// Environment test
if (is_readable($rootpath . "install.php")) {
    include $rootpath . "install.php";
    exit();
}

//Import composer autoloader
$autoloader = include $environment["paths"]["vendor"] . "autoload.php";

//Load constants and core functions
require_once $environment["paths"]["core"] . "constants.php";
require_once $environment["paths"]["core"] . "functions.php";

// Try to create and startup the app instance
try {
    // Initialize / Create the app instance
    /* @var $app \Komet\App */
    $app = new $environment["app_class"]($environment, $autoloader, "default");
    $app->startup();
} catch (Exception $exc) {
    error_log($exc->getTraceAsString());
    try {
        if (!$app->trigger("app.fatal")) {
            die($environment["fatal_message"]);
        }
    } catch (Exception $exc) {
        die($environment["fatal_message"]);
    }
}

// Try to run the current request
try {
    $app->run();
} catch (Exception $exc) {
    error_log($exc->getMessage());
    try {
        $app->logger->critical("Application cannot continue", $exc);
        if (!$app->trigger("app.fatal")) {
            die($environment["fatal_message"]);
        }
    } catch (Exception $exc) {
        die($environment["fatal_message"]);
    }
}

$app->trigger("app.before_shutdown");

?>