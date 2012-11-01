<?php

/**
 * Part of the KometPHP Framework
 * 
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */
namespace Demo;

/**
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */
class Controller_Say extends \Welcome\Controller_Index
{

    public function actionIndex()
    {
        $this->viewID = "demoModuleIndex";
        $this->title = "Say | KometPHP Framework";
        $this->text = \Komet\K::get("text", null, "Hello World!");
        
        return $this->render(array("master_top.php", "index.php", "master_bottom.php"), "null", "demo");
    }

}