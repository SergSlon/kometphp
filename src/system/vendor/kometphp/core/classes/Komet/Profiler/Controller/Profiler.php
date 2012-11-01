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
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */
class Controller_Profiler extends \Komet\HMVC\AbstractController
{

    public function __construct(\Komet\HMVC\Router $router)
    {
        parent::__construct($router);
        $this->response->contentType("text/html");
    }

    public function actionIndex()
    {
        if ($this->extension() == "json") {
            return $this->actionAjaxIndex();
        } else {
            $this->data = Profiler::getInstance()->getData(true);
            return $this->render(array("top.php","profiler.php","bottom.php"), null, "profiler");
        }
    }

    public function actionAjaxIndex()
    {
        $this->response->contentType("application/json");
        $this->response->body(json_encode(Profiler::getInstance()->getData(true)));
        return $this->response;
    }

}