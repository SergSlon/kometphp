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
 * @package Komet
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */
class Module
{

    /**
     *
     * @var string 
     */
    protected $name;

    /**
     *
     * @var string 
     */
    protected $path;

    /**
     *
     * @var string 
     */
    protected $theme;

    protected function __construct($name, array $config = array())
    {
        $this->name = $name;
        $this->path = $config["path"];

        // module theme
        $theme = $this->config("theme");
        if (!empty($theme) && is_dir($this->viewsPath() . $theme)) {
            $this->theme = $theme;
        }
    }

    /**
     * 
     * @param string $name Module name (equals to folder name)
     * @param array $config Module configuration
     * @return \Komet\Model
     */
    public static function factory($name, array $config = array())
    {
        $path = ((isset($config["path"])
                && !empty($config["path"])) ?
                        (\Komet\K::app()->path("root") . trim($config["path"], DIRECTORY_SEPARATOR) .
                        DIRECTORY_SEPARATOR) : (\Komet\K::app()->path("modules") . $name . DIRECTORY_SEPARATOR));
        if (!is_dir($path)) {
            \Komet\K::app()->logger->critical("Module not found at path: " . $path);
        }

        $config["path"] = $path;

        // If has classes, register in autoloader
        if (is_dir($path . "classes")) {
            \Komet\K::app()->autoloader->add("", $path . "classes" . DIRECTORY_SEPARATOR);
        }

        // module functions file
        if (is_readable($path . "functions.php")) {
            include_once $path . "functions.php";
        }

        $defaults = array();
        if (is_readable($path . "defaults.php")) { //default config
            $defaults = include $path . "defaults.php";
        }

        $config = Arr::merge($defaults, $config);

        // module config
        \Komet\K::app()->setConfig("modules." . $name, $config);


        $moduleClass = isset($config["module_class"]) ? $config["module_class"] : "\Komet\Module";

        $module = new $moduleClass($name, $config);

        // notifiers (for app-specific initialization)
        \Komet\K::app()->trigger("module.before_create", $module);
        \Komet\K::app()->trigger($name . "_module.before_create", $module);

        return $module;
    }

    public function isMain()
    {
        return $this->name == \Komet\K::app()->mainModuleName();
    }

    public function name()
    {
        return $this->name;
    }

    public function path()
    {
        return $this->path;
    }

    public function config($name = null, $default = null)
    {
        if ($name == null)
            return \Komet\K::app()->config("modules." . $this->name, $default);
        else
            return \Komet\K::app()->config("modules." . $this->name . "." . $name, $default);
    }

    public function setConfig($name, $value)
    {
        if ($name == null)
            \Komet\K::app()->setConfig("modules." . $this->name, $value);
        else
            \Komet\K::app()->setConfig("modules." . $this->name . "." . $name, $value);
    }

    public function hasClasses()
    {
        return is_dir($this->path . "classes");
    }

    public function hasViews()
    {
        return is_dir($this->viewsPath());
    }

    public function hasThemes()
    {
        return !empty($this->theme);
    }

    public function theme($newTheme = null)
    {
        if ($newTheme !== null)
            $this->theme = $newTheme;
        return $this->theme;
    }

    public function viewsPath()
    {
        return $this->path . "views" . DIRECTORY_SEPARATOR;
    }

    public function themePath()
    {
        $path = $this->viewsPath();

        if ($this->hasThemes()) {
            return $path . $this->theme . DIRECTORY_SEPARATOR;
        } else {
            return $path;
        }
    }

}