<?php

/**
 * Part of the KometPHP Framework
 * 
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */
namespace Welcome;

/**
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */
class Controller_Index extends \Komet\HMVC\AbstractController
{

    public function __construct(\Komet\HMVC\Router $router)
    {
        parent::__construct($router);
        $this->assetsVersion = 0;
        $this->response->contentType("text/html");
        $this->title = "KometPHP Framework";

        \Komet\K::logger()->debug("this is a debug");
        \Komet\K::logger()->info("this is an info");
        \Komet\K::logger()->warning("this is a warn");
        \Komet\K::logger()->error("this is an error");
        \Komet\K::logger()->critical("this is a critical");
    }

    public function actionIndex()
    {
        $this->viewID = "index";
        return $this->render(array("master_top.php", "index.php", "master_bottom.php"));
    }

    public function actionGetTest() //This function is only accessible via GET method
    {
        $this->viewID = "test";
        $this->title = "TEST";
        return $this->render(array("master_top.php", "index.php", "master_bottom.php"));
    }

    public function actionPostTest() //This function is only accessible via POST method
    {
        $this->viewID = "test";
        $this->title = "TEST POST";
        return $this->render(array("master_top.php", "index.php", "master_bottom.php"));
    }

    public function validateGetTest()
    { 
        //This function must invalidate the above function and turn it unaccessible
        return false;
    }

    public function actionHandle()
    {
        $this->response->status(404);
        $this->viewID = "error";
        return $this->render(array("master_top.php", "error.php", "master_bottom.php"));
    }

}