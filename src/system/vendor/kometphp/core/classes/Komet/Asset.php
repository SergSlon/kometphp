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
 * 
 */
class Asset
{

    /**
     *
     * @var array
     */
    protected $handlers;

    /**
     * Imported assets
     * 
     * @var array 
     */
    protected $imported = array();

    public function __construct()
    {
        $this->handlers = array(
            "css" => array(
                "extensions" => "css",
                "folder" => "css",
                "generator" => function($url, $attributes) {
                    return Html::link(array_merge($attributes, array(
                                        "rel" => "stylesheet",
                                        "type" => "text/css",
                                        "media" => "all",
                                        "href" => $url
                                    )));
                }
            ),
            "less" => array(
                "extensions" => "less",
                "folder" => "less",
                "generator" => function($url, $attributes) {
                    return Html::link(array_merge($attributes, array(
                                        "rel" => "stylesheet",
                                        "type" => "text/less",
                                        "media" => "all",
                                        "href" => $url
                                    )));
                }
            ),
            "js" => array(
                "extensions" => "js",
                "folder" => "js",
                "generator" => function($url, $attributes) {
                    return Html::script(array_merge($attributes, array(
                                        "type" => "text/javascript",
                                        "src" => $url
                                    )));
                }
            ),
            "img" => array(
                "extensions" => "bmp|jpg|jpeg|gif|apng|png|svg|tiff",
                "folder" => "img",
                "generator" => function($url, $attributes) {
                    return Html::img(array_merge($attributes, array(
                                        "src" => $url
                                    )));
                }
            ),
            "ico" => array(
                "extensions" => "ico",
                "folder" => "img",
                "generator" => function($url, $attributes) {
                    return Html::link(array_merge($attributes, array(
                                        "rel" => "shortcut icon",
                                        "type" => "image/x-icon",
                                        "href" => $url
                                    )));
                }
            ),
        );
    }

    public function setHandler($name, array $settings)
    {
        $this->handlers[$name] = $settings;
    }

    public function getHandler($name)
    {
        if (isset($this->handlers[$name])) {
            return $this->handlers[$name];
        }
        return false;
    }

    /**
     * Generates the HTML for a given asset resource
     * 
     * @param string|array $file This can be one file or an array of files.
     * Each item of the array can be a string or an array containing filename and
     * attributes array.
     * 
     * @param string|int $version Resource version
     * @param string $type Type of resource (css, js or img)
     * @param array $attributes HTML extra / override attributes
     * @return void
     * @throws \RuntimeException 
     */
    public function getHtml($file, $version = null, $type = null, $attributes = array())
    {
        if (!is_array($attributes))
            $attributes = array();
        $html = "";
        if (is_array($file)) {
            foreach ($file as $f) {
                if (is_array($f)) {
                    $attributes = array_merge($attributes, $f[1]);
                    $f = $f[0];
                }
                $html.="\n" . $this->getHtml($f, $version, $type, $attributes);
            }
        } else {
            if (empty($type)) {
                $type = $this->type($file);
            }
            if ($type == false) {
                \Komet\K::app()->logger->warning("Asset type of file '{$file}' is not registered", array("method", __METHOD__));
                return "";
            }
            if (Str::isUrl($file)) {
                $fileurl = $file;
            } else {
                $filename = $this->path($file, $type);
                $fileurl = $this->url($file, $version, $type);

                if (!is_readable($filename)) {
                    \Komet\K::app()->logger->warning("Asset file '{$filename}' does not exist", array("method", __METHOD__));
                }
            }
            $html = $this->handlers[$type]["generator"]($fileurl, $attributes);
        }
        return $html;
    }

    /**
     * Generates and prints the HTML for a given asset resource
     * 
     * @param string|array $file This can be one file or an array of files.
     * Each item of the array can be a string or an array containing filename and
     * attributes array.
     * 
     * @param string|int $version Resource version
     * @param string $type Type of resource (css, js or img)
     * @param array $attributes HTML extra / override attributes
     * @return void
     * @throws \RuntimeException 
     */
    public function import($file, $version = null, $type = null, $attributes = array())
    {
        $html = $this->getHtml($file, $version, $type, $attributes);
        if (is_array($file)) {
            foreach ($file as $f) {
                $this->imported[] = $this->getHash($f, $version, $type);
            }
        } else {
            $this->imported[] = $this->getHash($file, $version, $type);
        }
        echo $html;
    }

    protected function getHash($file, $version = null, $type = null)
    {
        return md5($file . ";" . $version . ";" . $type);
    }

    /**
     * Generates and prints the HTML for a given asset resource (only if exists)
     * 
     * @param string|array $file This can be one file or an array of files.
     * Each item of the array can be a string or an array containing filename and
     * attributes array.
     * 
     * @param string|int $version Resource version
     * @param string $type Type of resource (css, js or img)
     * @param array $attributes HTML extra / override attributes
     * @return void
     * @throws \RuntimeException 
     */
    public function importIfExists($file, $version = null, $type = null, $attributes = array())
    {
        if (is_array($file)) {
            foreach ($file as $f) {
                $this->loadIfExists($f, $version, $type, $attributes);
            }
        } else {
            if (Str::isUrl($file) or $this->exists($file, $type)) {
                $this->load($file, $version, $type, $attributes);
            }
        }
    }

    public function importOnce($file, $version = null, $type = null, $attributes = array())
    {
        if (!$this->isImported($file, $version, $type)) {
            $this->import($file, $version, $type, $attributes);
        }

        if (is_array($file)) {
            foreach ($file as $f) {
                if (!$this->isImported($f, $version, $type)) {
                    $this->import($f, $version, $type, $attributes);
                }
            }
        } else {
            if (!$this->isImported($file, $version, $type)) {
                $this->import($file, $version, $type, $attributes);
            }
        }
    }

    /**
     * 
     */
    public function isImported($file, $version = null, $type = null)
    {
        return in_array($this->getHash($file, $version, $type), $this->imported);
    }

    /**
     *
     * @param string $file
     * @return string|null
     */
    public function type($file)
    {
        foreach ($this->handlers as $t => $h) {
            if (preg_match('/\.(' . $h["extensions"] . ')$/i', $file)) {
                return $t;
            }
        }
        return false;
    }

    /**
     *
     * @param string $file
     * @param string $type
     * @return boolean 
     */
    public function exists($file, $type = null)
    {
        return is_readable($this->path($file, $type));
    }

    /**
     *
     * @param string $file
     * @param string $type
     * @return string 
     */
    public function path($file, $type = null)
    {
        if (empty($type))
            $type = $this->type($file);

        if ($type == false) {
            \Komet\K::app()->logger->warning("Asset handler of file '{$file}' is not registered", array("method", __METHOD__));
            return false;
        }

        return \Komet\K::app()->path("static") . "assets" . DIRECTORY_SEPARATOR . $this->handlers[$type]["folder"] . "/" . trim($file, "/");
    }

    /**
     *
     * @param string $file
     * @param string|int $version Resource version
     * @param string $type
     * @return string 
     */
    public function url($file, $version = null, $type = null)
    {
        $version = strval($version);
        if (empty($type))
            $type = $this->type($file);

        if ($type == false) {
            \Komet\K::app()->logger->warning("Asset handler of file '{$file}' is not registered", array("method", __METHOD__));
            return false;
        }

        if (strlen($version) > 0)
            $version = "?v=" . $version;

        return \Komet\K::app()->getRequest()->url("static") . "assets/" . $this->handlers[$type]["folder"] . "/" . $file . $version;
    }

}