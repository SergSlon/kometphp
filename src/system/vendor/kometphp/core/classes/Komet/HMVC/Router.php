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
 * @package Komet/HMVC
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */
class Router extends AbstractRouter
{

    /**
     * @var string
     */
    public $actionId;

    /**
     * @var string
     */
    public $actionSlug;

    /**
     * @var string
     */
    public $callableUri;

    /**
     *
     * @var array
     */
    public $params;

    /**
     *
     * @var string
     */
    public $controller;

    /**
     *
     * @var string
     */
    public $controllerSlug;

    /**
     *
     * @var \Komet\HMVC\AbstractController
     */
    public $controllerInstance;

    /**
     * Module the controller belongs to
     * @var string
     */
    public $controllerModule;

    /**
     *
     * @var string
     */
    public $controllerFile;

    /**
     *
     * @var string
     */
    public $action;

    public function __construct(\Komet\Request $request)
    {
        parent::__construct($request);
    }

    /**
     * Route param segment
     * 
     * @param string $index Array key
     * @param mixed $default Default value if the variable is not set or regexp is false
     * @param mixed $validation FILTER_* constant value or regular expression
     * @return mixed
     */
    public function param($index = null, $default = null, $validation = null)
    {
        if (is_null($index) and func_num_args() === 0) {
            return $this->params;
        }
        return \Komet\Arr::check($this->params, $index, $default, $validation);
    }

    /**
     * @return \Komet\Module
     */
    public function module()
    {
        return \Komet\K::app()->getModule($this->controllerModule);
    }

    protected function resolve()
    {
        //Yet initialized
        if (!empty($this->controller))
            return true;

        \Komet\K::app()->trigger("router.before_resolve", $this);
        $r = $this->callableUri = strtolower(trim($this->request->uri, "/ "));

        // Prevent serve duplicated content when accessing '/', '/index' and '/index/index'
        if (($r == "index") || ($r == "index/index")) {
            $this->callableUri = "";
        }

        $this->findController();
        $this->findAction();

        \Komet\K::app()->trigger("router.resolve", $this);
    }

    /**
     * 
     * @param string $className
     * @return boolean 
     */
    protected function findControllerFile($className)
    {
        foreach (\Komet\K::app()->getModules() as $i => $m) {
            $fullClassName = '\\' . ltrim($m->config("hmvc.controller_prefix", ""), '\\') . $className;
            $controllerFile = $m->path() . "classes" . DIRECTORY_SEPARATOR .
                    str_replace(array("\\", "_"), DIRECTORY_SEPARATOR, trim($fullClassName, "\\/_ ")) . ".php";
            if (is_readable($controllerFile)) {
                include_once $controllerFile;
                if (class_exists($fullClassName)) {
                    $this->controllerModule = $m->name();
                    $this->controller = $fullClassName;
                    return true;
                }
            }
        }

        // class not found
        return false;
    }

    protected function findController()
    {
        $r = explode("/", $this->callableUri);
        $params = array();

        //Define default controller and controller url
        $defaultController = \Komet\K::app()->getModule()->config("hmvc.controller_prefix", \Komet\K::app()->config("hmvc.controller_prefix", "\Controller_")) . 
                \Komet\K::app()->getModule()->config("hmvc.default_controller", \Komet\K::app()->config("hmvc.default_controller", "Index"));

        $this->request->setUrl("controller", $this->request->url("mvc"));
        $this->controllerModule = \Komet\K::app()->mainModuleName();

        if ((count($r) > 0) && (!empty($r[0]))) {
            while (count($r) > 0) { //sub-controller support
                $className = \Komet\Format::slug(implode(" ", $r), "_");
                if ($this->findControllerFile($className)) {
                    $this->request->setUrl("controller", $this->request->url("mvc") . implode('/', $r) . "/");
                    $this->actionId = implode('-', $r);
                    $this->controllerSlug=implode('/', $r);
                    break;
                } else {
                    $params[] = array_pop($r);
                }
            }
        } else {
            $this->controller = $defaultController;
        }

        if (empty($this->controller)) {
            $this->controller = $defaultController;
        }

        $this->params = array_reverse($params);
        return $this->controller;
    }

    protected function findAction()
    {
        $controller = $this->controller;
        $r = $this->params;
        $this->request->setUrl("action", $this->request->url("controller"));

        if (!is_array($r))
            $r = explode("/", $r);

        if ((count($r) > 0) && (!empty($r[0]))) {
            $action = "actionHandle";
            $part = $r[0];

            $fns = $this->getActionNames(ucfirst(\Komet\Format::camelize($part)));

            foreach ($fns as $fn) {
                if (method_exists($controller, $fn)) {
                    $this->request->setUrl("action", $this->request->url("controller") . $part . "/");
                    $this->actionId .= empty($this->actionId) ? $part : "-" . $part;
                    $this->actionSlug = $part;
                    $action = $fn;
                    array_shift($this->params);
                    break;
                }
            }
        } else {
            $action = "actionIndex";
        }

        if (in_array($action, array("actionHandle", "actionIndex"))) {
            $fns = $this->getActionNames($action);

            foreach ($fns as $fn) {
                if (method_exists($controller, $fn)) {
                    $action = $fn;
                    break;
                }
            }
        }

        $this->action = $action;
        return $action;
    }

    /**
     * Examples:  actionGetIndex, actionPostLogin, actionAjaxPostCreatePage,
     * actionCreatePage, actionAjaxUserComments
     * 
     * @param string $action
     * @return array
     */
    protected function getActionNames($action)
    {
        $fns = array();
        $slug = ucfirst(preg_replace("/^action/","",$action));
        $verb = ucfirst(strtolower($this->request->method()));

        //AJAX actions
        if ($this->request->isAjax()) {
            $fns[] = "actionAjax" . $verb . $slug;
            $fns[] = "actionAjax" . $slug;
        }

        //REQUEST_METHOD specific action
        $fns[] = "action" . $verb . $slug;

        //normal action
        $fns[] = "action" . $slug;

        return $fns;
    }

    /**
     *
     * @return \Komet\Core\Response 
     */
    public function execute()
    {
        \Komet\K::app()->trigger("router.before_execute", $this);
        $this->resolve();

        $klass = $this->controller;
        $fn = $this->action;
        $validation_fn = preg_replace("/^action/", "validate", $fn);

        $this->controllerInstance = new $klass($this);

        if (!is_callable(array($this->controllerInstance, $fn))) {
            $fn = $this->action = "actionHandle";
        }

        \Komet\K::app()->trigger("router.before_call", $this);

        $passValidation = true;
        if (method_exists($klass, $validation_fn)) {
            $passValidation = $this->controllerInstance->$validation_fn($fn);
        } else {
            $passValidation = $this->controllerInstance->validate($fn);
        }

        if ($passValidation) {
            $this->controllerInstance->$fn();
        } else {
            $this->controllerInstance->actionHandle();
        }

        if (!($this->controllerInstance->response instanceof \Komet\Response)) {
            \Komet\K::app()->logger->critical("Error: Action return value must always be an instance of \Komet\Response, one of type '" . \Komet\Object::getType($this->controllerInstance->response) . "' returned.");
        }

        \Komet\K::app()->trigger("router.call", $this);
        \Komet\K::app()->trigger("router.execute", $this);

        return $this->controllerInstance->response;
    }
    
    public function moduleName()
    {
        return !empty($this->controllerModule) ? $this->controllerModule : \Komet\K::app()->mainModuleName();
    }

}