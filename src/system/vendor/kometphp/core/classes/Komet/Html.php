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
 * HTML helper class
 * 
 * @package Komet
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */
class Html
{

    /**
     * List of HTML5 void elements (self closing tag)
     * @var array
     */
    public static $void_elements = array("area", "base", "br", "col", "command", "embed",
        "hr", "img", "input", "keygen", "link", "meta", "param", "source", "track", "wbr");

    /**
     * Converts a key-value pair array into a html-attr string
     * @param array $arr
     * @return string 
     */
    public static function attr($arr)
    {
        if (!is_array($arr) || (count($arr) == 0))
            return "";

        $str = "";
        foreach ($arr as $key => $value) {
            $str.="{$key}=\"{$value}\" ";
        }
        $str = trim($str);
        return !empty($str) ? " " . $str : null;
    }

    public static function content($tagname, $content)
    {
        $tagname = strtolower($tagname);
        $str = "";
        if (is_array($content)) {
            foreach ($content as $i => $c) {
                //option
                if (($tagname == "select") or ($tagname == "optgroup") or
                        ($tagname == "datalist")) {
                    $str .= static::option(array("value" => $i), $c) . "\n";
                    //li
                } elseif (($tagname == "ul") or ($tagname == "ol")) {
                    $str .= static::li(null, $c) . "\n";
                    //td
                } elseif ($tagname == "tr") {
                    $str .= static::td(null, $c) . "\n";
                    //tr + td
                } elseif (($tagname == "table") or ($tagname == "thead") or
                        ($tagname == "tbody") or ($tagname == "tfoot")) {
                    if (is_array($c)) {
                        $str .= static::trOpen() . "\n";
                        foreach ($c as $j => $tdc) {
                            $str .= "\t" . static::td(null, $tdc);
                        }
                        $str .= static::trClose() . "\n";
                    } else {
                        $str .= static::tr(null, static::td(null, $c)) . "\n";
                    }
                    //a
                } elseif ($tagname == "nav") {
                    $str .= static::a(array("href" => $i), $c) . "\n";
                    //source
                } elseif (($tagname == "audio") or ($tagname == "video")) {
                    $str .= static::source(array("src" => $i, "type" => $c)) . "\n";
                } else {
                    $str .= strval($c) . " ";
                }
            }
            $str = trim($str);
        } else {
            $str = strval($content);
        }
        return $str;
    }

    public static function tag($tagname, $attr = array(), $content = null)
    {
        $tagname = strtolower($tagname);
        return static::tagOpen($tagname, $attr) . static::content($tagname, $content) . static::tagClose($tagname);
    }

    public static function tagOpen($tagname, $attr = array())
    {
        $tagname = strtolower($tagname);
        if (!in_array($tagname, static::$void_elements)) {
            return "<$tagname" . rtrim(static::attr($attr)) . ">";
        } else {
            return "<$tagname" . static::attr($attr);
        }
    }

    public static function tagClose($tagname)
    {
        $tagname = strtolower($tagname);
        if (in_array($tagname, static::$void_elements))
            return " />\n";
        else
            return "</{$tagname}>\n";
    }

    public static function attrPluck($str, $tagname = "a|img", $attrname = "href|src")
    {
        $tagname = strtolower($tagname);
        preg_match_all('/<' . $tagname . '[^>]+>/i', $str, $result);
        $elems = array();
        foreach ($result as $i => $elem) {
            preg_match_all('/(' . $attrname . ')=("[^"]*")/i', $elem, $elems[$i]);
        }
        $values = array();
        foreach ($elems as $i => $elem) {
            if (isset($elem[2]) && isset($elem[2][0])) {
                $values[] = $elem[2][0];
            }
        }
        return $values;
    }

    public static function formatAddLinks($str, $target_blank = false)
    {
        if ($target_blank)
            $target_blank = ' target="_blank" ';
        return str_replace('&', '&amp;', preg_replace('/([\w]+:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/i', '<a' .
                                $target_blank . ' href="$1">$1</a>', $str));
    }

    public static function formatAddMarks($string, $words)
    {
        if (!is_array($words))
            $words = array($words);
        foreach ($words as $word) {
            $string = str_ireplace($word, '<mark>' . $word . '</mark>', strip_tags($string));
        }
        return $string;
    }

    public static function __callStatic($name, $arguments)
    {
        $klass = get_called_class();
        array_unshift($arguments, str_replace(array("Open", "Close"), "", $name));

        if (preg_match("/Open$/i", $name)) {
            return call_user_func_array($klass . "::tagOpen", $arguments);
        } elseif (preg_match("/Close$/i", $name)) {
            return call_user_func_array($klass . "::tagClose", $arguments);
        } else {
            return call_user_func_array($klass . "::tag", $arguments);
        }
    }

    public function __call($name, $arguments)
    {
        return static::__callStatic($name, $arguments);
    }

}