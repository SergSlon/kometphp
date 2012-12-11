<?php
// Config file for 'test' environment
$config = include "config_development.php";
$config["profiler_enabled"]=false;

return $config;
