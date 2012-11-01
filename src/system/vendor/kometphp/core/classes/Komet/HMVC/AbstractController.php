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
abstract class AbstractController extends \Komet\Object
{

    /**
     * Supported HTTP methods and their resource formats
     * 
     * For support all formats the value will be '*',
     * for specific formats, the value will be an array containing the supported
     * format extensions i.e. array("html", 'json', 'xml')
     * 
     * @var array 
     */
    public $supports = array("HEAD" => '*', "GET" => '*', "POST" => '*');

    /**
     *
     * @var Router
     */
    public $router;

    /**
     *
     * @var \Komet\Response
     */
    public $response;

    public function __construct(Router $router)
    {
        $this->router = $router;
        $this->response = new \Komet\Response($router->request()->method(), "", 200, "text/plain");
    }

    /**
     * Default function 
     */
    abstract public function actionIndex();

    /**
     * Not-found handler function 
     */
    public function actionHandle()
    {
        $this->response->status(404);
        if(\Komet\Str::isWebFile($this->router->request()->uri)){
            $this->response->body('');
        }else{
            $this->response->body('404 NOT FOUND');
        }
        return $this->response;
    }

    /**
     * Request validation function
     */
    public function validate()
    {
        $method = $this->router->request()->method();

        if (!isset($this->supports[$method])) {
            $this->response->status(405); // Method Not Allowed
            return false;
        } else {
            if (($this->supports[$method] != "*")) {
                if (is_array($this->supports[$method])) {
                    if (!in_array($this->router->request()->extension, $this->supports[$method])) {
                        $this->response->status(415); // Unsupported Media Type
                        return false;
                    }
                } elseif ($this->supports[$method] != $this->router->request()->extension) {
                    $this->response->status(415); // Unsupported Media Type
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 
     * @param string|array $files File path or array of file paths that will generate the full view
     * @param array $vars View variables
     * @param string $module Default module to load the views from (used on relative file paths)
     * @return \Komet\Response
     */
    public function render($files, $vars = null, $module = null)
    {
        if (!is_array($vars)) {
            $vars = $this->vars;
        }

        $this->response->body(View::create($files, $vars, $module)->render());

        return $this->response;
    }

    /**
     * 
     * @return \Komet\Module The module the controller belongs to
     */
    public function module()
    {
        return $this->router->module();
    }

    public function extension()
    {
        return $this->router->request()->detectedExtension();
    }

    public function method()
    {
        return $this->router->request()->method();
    }

}