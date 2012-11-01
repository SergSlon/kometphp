<?php

/**
 * Part of the KometPHP Framework
 * 
 * @package Komet/HMVC
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */

namespace Komet\HMVC;

/**
 * View renderer class
 * 
 * @package Komet/HMVC
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */
abstract class AbstractView extends \Komet\Object
{

    /**
     * File path or array of file paths that will generate the full view
     * @var string|array
     */
    protected $files = array();

    /**
     * Default module to load the views from (used on relative file paths)
     * @var string
     */
    protected $module;

    /**
     *
     * @var string
     */
    protected $content = null;

    /**
     * Content filters
     * @var array
     */
    protected $filters = array();

    /**
     * 
     * @param string|array $files File path or array of file paths that will generate the full view
     * @param array $vars View variables
     * @param string $module Default module to load the views from (used on relative file paths)
     */
    public function __construct($files, $vars = array(), $module = null)
    {
        $this->files = $files;
        $this->vars = $vars;
        $this->module = empty($module) ? \Komet\K::app()->getCurrentModuleName() : $module;
    }

    /**
     * 
     * @param string|array $files File path or array of file paths that will generate the full view
     * @param array $vars View variables
     * @param string $module Default module to load the views from (used on relative file paths)
     * @return View
     */
    public static function create($files, $vars = array(), $module = null)
    {
        return new static($files, $vars, $module);
    }

    public function content($content = null)
    {
        if ($content !== null) {
            $this->content = $content;
        }
        return $this->content;
    }

    public function addFilter($name, \Closure $fn, $priority = 0)
    {
        $this->filters[$priority][] = $fn;
    }

    public function hasFilter($name, $priority = null)
    {
        if ($priority !== null) {
            return isset($this->filters[$priority][$name]);
        } else {
            foreach ($this->filters as $priority => $filters) {
                if (isset($filters[$name])) {
                    return true;
                }
            }
        }
        return false;
    }

    public function removeFilter($name, $priority = null)
    {
        if ($priority !== null) {
            if (isset($this->filters[$priority][$name])) {
                unset($this->filters[$priority][$name]);
                return true;
            }
        } else {
            foreach ($this->filters as $priority => $filters) {
                if (isset($filters[$name])) {
                    unset($filters[$name]);
                    return true;
                }
            }
        }
        \Komet\K::app()->logger->warning("View filter '$name' does not exist");
        return false;
    }

    public function applyFilters($content)
    {
        ksort($this->filters);

        foreach ($this->filters as $priority => $filters) {
            foreach ($filters as $fn) {
                $content = $fn($content, $this);
            }
        }
        return $content;
    }

    /**
     * 
     * @param boolean $applyFilters Auto-apply filters to the generated content?
     * this function will return the response object instead of a string.
     * 
     * @return string generated content
     */
    public function render($applyFilters = true)
    {
        \Komet\K::app()->trigger("view.before_render"); //global view trigger
        chdir(\Komet\K::app()->getModule($this->module)->themePath());

        $this->content = "";

        if (is_array($this->files)) {
            foreach ($this->files as $file) {
                if (!file_exists($file)) {
                    $file = \Komet\K::app()->getModule($this->module)->themePath() . $file;
                }
                $this->content.=$this->parse($file, $this->module);
            }
        } else {

            if (!file_exists($this->files)) {
                $this->files = \Komet\K::app()->getModule($this->module)->themePath() . $this->files;
            }
            $this->content = $this->parse($this->files, $this->module);
        }

        \Komet\K::app()->trigger("view.render"); //global view trigger

        if ($applyFilters) {
            $this->content = $this->applyFilters($this->content);
        }

        // If empty, ensure it's an empty string
        if (empty($this->content)) {
            $this->content = "";
        }

        return $this->content;
    }

    /**
     * 
     * @param string $file Template file
     * @return string
     */
    abstract public function parse($file);

    public function __toString()
    {
        if ($this->content === null) {
            $this->render();
        }
        return $this->content;
    }

}