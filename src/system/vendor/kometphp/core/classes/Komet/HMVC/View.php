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
 * PHP Template parser
 * 
 * @package Komet/HMVC
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */
class View extends AbstractView
{

    public function parse($file)
    {
        extract($this->vars, EXTR_OVERWRITE);

        ob_start();
        if(is_readable($file)) {
            include $file;
        }else{
            \Komet\K::app()->logger->error("View file not found: ".$file);
        }
        return ob_get_clean();
    }

}