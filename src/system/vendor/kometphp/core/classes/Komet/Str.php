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
 * 
 * String validator and generator functions
 * 
 * @package Komet
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 * 
 */
class Str
{

    /**
     * Generates a random string
     * @param int $length
     * @param string $charset 'alpha', 'alphanum', 'alpha_ci', 'alphanum_ci', 'num', 'symbol', 'hex', 'any', or a string with custom set of chars
     * @return string
     */
    public static function random($length = 32, $charset = "alphanum")
    {
        if ($charset == "alpha")
            $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        else if ($charset == "alphanum")
            $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        if ($charset == "alpha_ci")
            $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        else if ($charset == "alphanum_ci")
            $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        else if ($charset == "num")
            $chars = "0123456789";
        else if ($charset == "symbol")
            $chars = "{}()[]<>!?|@#%&/=^*;,:.-_+";
        else if ($charset == "hex")
            $chars = "ABCDEF0123456789";
        else if ($charset == "any")
            $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789{}()[]<>!?|@#%&/=^*;,:.-_+";
        else
            $chars = $charset;

        $plength = mb_strlen($chars);
        mt_srand((double) microtime() * 10000000);
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[mt_rand(0, $plength - 1)];
        }
        return $str;
    }

    public static function isHtml($str)
    {
        return (preg_match('/<\/?\w+((\s+\w+(\s*=\s*(?:".*?"|\'.*?\'|[^\'">\s]+))?)+\s*|\s*)\/?>/i', $str) > 0);
    }

    public static function isMsWord($str)
    {
        return preg_replace('/class="?Mso|style="[^"]*\bmso-|w:WordDocument/i');
    }

    public static function isUrl($str)
    {
        // Supports protocol agnostic urls starting with double dash '//'
        return (filter_var($str, FILTER_VALIDATE_URL) !== false) || (preg_match('/^\/\/.+/', $str) == true);
    }

    public static function isEmail($str)
    {
        return filter_var($str, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function isWebFile($str, $exts = null)
    {
        $exts = $exts ? $exts : 'js|j|css|less|xml|xss|xslt|rss|atom|json|cur|ani|ico|bmp|jpg|png|apng|gif|swf|' .
                'svg|svgz|otf|eot|woff|ttf|avi|fla|flv|mp3|mp4|mpg|mov|mpeg|mkv|ogg|ogv|oga|aac|wmv|wma|rm|webm|webp|pdf';
        return (preg_match("/\.({$exts})$/", $str) != false);
    }
    
    public static function isRegex($str){
        return (preg_match('/^\/.*\/[imsxeADSUXJu]*$/', $str)) > 0;
    }

}