<?php

/**
 * KometPHP facade shortcut class
 * 
 * You can extend \Komet\K here
 * 
 */
class K extends \Komet\K
{

    /**
     * 
     * @return \Komet\Flash
     */
    public static function flash()
    {
        if (!isset(self::app()->flash)) {
            self::app()->flash = new \Komet\Flash("main");
        }
        return self::app()->flash;
    }

}