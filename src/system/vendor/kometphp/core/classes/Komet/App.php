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
 * 
 * 
 * @package Komet
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 * 
 * @property \Composer\Autoload\ClassLoader $autoloader Composer autoloader
 * @property \Komet\Logger\Logger $logger App default logger
 * @property Asset $asset Asset Manager
 * @property boolean $isCLI
 * @property boolean $hasReadlineSupport
 */
class App
{
    use Listenable;

    /**
     * Application instances
     * @var array[App]
     */
    protected static $instances = array();

    /**
     * Environment stage name
     * i.e. dev, prod, test, ...
     * @var string
     */
    protected $environment = "dev";

    /**
     * App configuration
     * @var array 
     */
    protected $config = array();

    /**
     * @var array Write-once data container for app variables
     */
    protected $registry = array();

    /**
     * Project absolute paths
     * @var array
     */
    protected $paths = array();

    /**
     * Loaded modules for this app instance
     * @var array
     */
    protected $modules = array();

    /**
     * Requests made / being done in this app instance
     * @var array
     */
    protected $requests = array();

    /**
     * Current active request
     * @var string
     */
    protected $activeRequest = -1;

    /**
     * 
     * @param array $environment
     * @param \Composer\Autoload\ClassLoader $autoloader
     */
    public function __construct(array $environment, \Composer\Autoload\ClassLoader $autoloader, $instanceName = "default")
    {
        $this->autoloader = $autoloader;
        
        //Set properties
        $this->paths = $environment["paths"];

        // Load configuration
        $this->config = $this->loadConfig("config", include $environment["paths"]["core"] . "defaults.php");

        // Set config's error reporting
        error_reporting($this->config["error_reporting"]);
        ini_set('display_errors', $this->config["display_errors"]);

        // Start a new session if there is no one active
        if (($this->config["session_autostart"] == true) && (session_id() == "")) {
            session_start();
        }

        // Register log handlers
        $this->logger = new Logger\Logger();
        foreach ($this->config("logger.handlers") as $i => $handlerCreator) {
            $this->logger->addHandler($handlerCreator($this), $i);
        }

        // Environment detection
        $this->isCLI = ((bool) defined('STDIN')) or (php_sapi_name() == "cli");
        $this->hasReadlineSupport = extension_loaded('readline');

        // Other stuff
        $this->asset = new Asset();
        
        $this->environment = (object) $environment;

        //Register shutdown function that will be called on the end of the script execution
        register_shutdown_function(array($this, "shutdown"));
        
        self::$instances[$instanceName] = $this;
    }

    public function startup()
    {
        // global init file 
        if (is_readable($this->path("root") . "init.php")) {
            // This file is useful i.e. for binding listeners
            include $this->path("root") . "init.php";
        }

        //Load modules
        
        $modules = $this->config("modules");
        
        // main module always the first
        if(!isset($modules[$this->mainModuleName()])){
            $modules[$this->mainModuleName()] = array();
        }
        $this->setModule($this->mainModuleName(), Module::factory($this->mainModuleName(), $modules[$this->mainModuleName()]));
        
        foreach ($modules as $mod => $settings) {
            if($mod == $this->mainModuleName()) continue;
            $this->setModule($mod, Module::factory($mod, $settings));
        }

        $this->trigger("app.startup");
    }

    public function shutdown()
    {
        // try log fatal errors
        $error = error_get_last();
        if ($error !== NULL) {
            $this->trigger("app.shutdown_error", $error);
            $this->logger->error("[SHUTDOWN] file:" . $error['file'] . " | ln:" . $error['line'] . " | msg:" . $error['message'] . PHP_EOL, $error);
        }

        $this->trigger("app.shutdown");
    }

    public function mainModuleName(){
        return $this->config["main_module"];
    }

    /**
     * Returns the app instance
     * 
     * @return App 
     */
    public static function getInstance($name = "default")
    {
        return self::$instances[$name];
    }

    public function environment()
    {
        return $this->environment;
    }

    /**
     * Creates a new request, calls it and returns the response
     * @param string $uri
     * @param array $superglobals Array of superglobals like POST, GET, SERVER, ...
     * @return Response 
     */
    public function call($uri = null, $superglobals = array())
    {
        $this->trigger("app.before_call", $uri);
        $this->activeRequest = count($this->requests);
        $request = new Request($uri, $superglobals);
        $this->requests[] = $request;

        $response = $request->router->execute();
        $this->trigger("app.call", $response);
        return $response;
    }

    public function run($uri = null, $superglobals = array())
    {
        $this->trigger("app.before_run", $uri);
        $response = $this->call($uri, $superglobals);
        $response->body(str_replace("\${EXEC_TIME}", Date::elapsedTime($this->environment->start_time, null, 3, true), $response->body()));
        $ob = $response->sendBody();
        $this->trigger("app.run", $ob);
    }

    public function __get($name)
    {
        if (!isset($this->registry[$name])) {
            $this->logger->error(sprintf('Value "%s" is not defined', $name), array("method", __METHOD__));
        }else
            return $this->registry[$name];
    }

    public function __set($name, $value)
    {
        if (!isset($this->registry[$name])) {
            $this->registry[$name] = $value;
        }
    }

    /**
     *
     * @param string $name Some possible values: root, bin, main, modules, public, vendor, assets, ...
     * @return string 
     */
    public function path($name = "root")
    {
        return $this->paths[$name];
    }

    /**
     *
     * @param string $name
     * @param string $value
     * @return string 
     */
    public function setPath($name, $value)
    {
        return $this->paths[$name] = $value;
    }

    /**
     * Gets a config value by dot notation
     * 
     * @param string $name
     * @param mixed $default
     * @return mixed 
     */
    public function config($name = null, $default = null)
    {
        if (empty($name))
            return $this->config;
        return Arr::dotget($this->config, $name, $default);
    }

    /**
     * Sets a config value by dot notation
     * 
     * @param string $name
     * @param mixed $value
     */
    public function setConfig($name, $value)
    {
        if (!empty($name)) {
            Arr::dotset($this->config, $name, $value);
        } else {
            $this->config = $value;
        }
    }

    /**
     * Loads a config file depending on the current environment name
     * @param string $name
     * @param array $defaults
     * @return array 
     */
    public function loadConfig($name = "config", $defaults = array())
    {
        $path = $this->path("config");
        return Arr::merge($defaults, is_readable($path . "{$name}_{$this->environment}.php") ?
                                (include $path . "{$name}_{$this->environment}.php") :
                                (is_readable($path . "{$name}.php") ?
                                        (include $path . "{$name}.php") :
                                        array())
        );
    }

    /**
     *
     * @param string $name
     * @return Module
     */
    public function getModule($name = null)
    {
        return $this->modules[$name ? : $this->getCurrentModuleName()];
    }

    /**
     * 
     * @return array
     */
    public function getModules()
    {
        return $this->modules;
    }
    
    public function getCurrentModuleName(){
        $mod = $this->mainModuleName();
        $req = isset($this->requests[$this->activeRequest]) ? $this->requests[$this->activeRequest] : false;
        if($req){
            $mod = $this->getRequest()->router->moduleName();
        }
        return $mod;
    }

    /**
     *
     * @param string $name
     * @param Module $module
     */
    public function setModule($name, Module $module)
    {
        $this->modules[$name] = $module;
    }

    /**
     *
     * @param int $level
     * @return Request|false 
     */
    public function getRequest($level = null)
    {
        $level = ($level !== null) ? $level : $this->activeRequest;
        if ($level < 0) {
            $this->call(); // original request
            $level = 0;
        }
        if ($level >= 0) {
            return $this->requests[$this->activeRequest];
        }
        return false;
    }

    /**
     * 
     * @return array
     */
    public function getRequests()
    {
        return $this->requests;
    }

    /**
     *
     * @param string $name
     * @param Module $module
     */
    public function setRequest($level, Request $request)
    {
        $this->requests[$level] = $request;
    }

    /**
     * @return int Active request level 
     */
    public function getActiveRequestLevel()
    {
        return $this->activeRequest;
    }

    /**
     * Sets the current active request
     * @param int $level 
     */
    public function setActiveRequest($level)
    {
        $this->activeRequest = $level;
    }

}