<?php

/**
 * Part of the KometPHP Framework
 * 
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */

namespace Komet\Profiler;

/**
 * 
 * 
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */
class Profiler extends \Komet\Module
{

    const SESSION_VAR = "__kometphp_profiler_data";

    /**
     *
     * @var \Komet\Logger\Handler\VarHandler
     */
    protected $loggerHandler;
    protected static $instance = null;

    protected function __construct($name, array $config = array())
    {
        parent::__construct($name, $config);

        // assets copy
        $assets_path = \Komet\K::path("static") . "assets" . DIRECTORY_SEPARATOR . $this->config("assets_folder") . DIRECTORY_SEPARATOR;
        if (!is_dir($assets_path)) {
            \Komet\Disk::copy($this->path() . "assets" . DIRECTORY_SEPARATOR, $assets_path);
        }

        // logger handler
        $this->loggerHandler = new \Komet\Logger\Handler\VarHandler(null, array(100, 200, 300, 400, 500), true);

        \Komet\K::logger()->addHandler($this->loggerHandler);

        if (static::$instance == null) {
            static::$instance = $this;
        }

        // save data before shutdown

        \Komet\K::app()->bind("app.before_shutdown", function() {
                    \Komet\Profiler\Profiler::getInstance()->saveData();
                });
    }

    /**
     * @todo Pass end_time and end_mem
     */
    public function saveData()
    {
        if (\Komet\K::router()->callableUri != "profiler") {

            $data = $this->getData(false);

            //Store logs
            $data["logs"] = array_merge($data["logs"], $this->loggerHandler->getLogs());
            $data["events"] = \Komet\K::app()->getEventLog();
            $data["info"] = array(
                "env" => \Komet\K::app()->environment(),
                "elapsed_time" => \Komet\Date::elapsedTime(\Komet\K::app()->environment()->start_time, null, 3, false),
                "used_memory" => round(((memory_get_usage() - \Komet\K::config("start_mem")) / 1024) / 1024, 4),
                "called_uri" => \Komet\K::router()->callableUri,
                "segmented_params" => (count(\Komet\K::router()->params) > 0) ? '[' . implode(",", \Komet\K::router()->params) . ']' : '[ ]',
                "called_action" => \Komet\K::router()->controller . '::' . \Komet\K::router()->action,
                "controller_module" => \Komet\K::router()->controllerModule,
                'loaded_modules' => '[' . implode(", ", array_keys(\Komet\K::app()->getModules())) . ']',
                'get_vars' => $_GET,
                'post_vars' => $_POST,
                'cookie_vars' => $_COOKIE
            );

            $_SESSION[static::SESSION_VAR] = $data;
        }
    }

    public function getData($flush = false)
    {
        if (isset($_SESSION[static::SESSION_VAR])) {
            $data = $_SESSION[static::SESSION_VAR];
            if ($flush)
                unset($_SESSION[static::SESSION_VAR]);
            return $data;
        }else {
            return array("logs" => array(), "events" => array(), "info" => array(
                    "env" => '?', "elapsed_time" => '?', "used_memory" => '?', "called_uri" => "profiler",
                    "segmented_params" => '[ ]', "called_action" => "Komet\Profiler\Controller\Profiler::__index",
                    "controller_module" => "profiler", "loaded_modules" => '?', "get_vars"=>array(), "post_vars"=>array(), "cookie_vars"=>array()
                    ));
        }
    }

    public function getIframe()
    {
        return \Komet\HMVC\View::create($this->viewsPath() . "iframe.php")->render();
    }

    /**
     * 
     * @return Profiler
     */
    public static function getInstance()
    {
        return self::$instance;
    }

}