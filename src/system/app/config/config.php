<?php
// Default configuration file (when no environment specific config file is found)
$config = include "config_development.php";
$config["display_errors"]=false;
$config["profiler_enabled"]=false;

return $config;