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
 * String/data formatter/transformer functions
 * 
 * @package Komet
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 * 
 */
class Format
{

    /**
     * Returns an encoded string, safe for URLs
     * @param string $str
     * @param bool $url_safe
     * @return string 
     */
    public static function base64Encode($str, $url_safe = true)
    {
        $data = base64_encode($str);
        if ($url_safe) {
            $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);
        }
        return $data;
    }

    /**
     *
     * @param string $str String encoded using Format::base64Encode()
     * @param bool $url_safe
     * @return string 
     */
    public static function base64Decode($str, $url_safe = true)
    {
        if ($url_safe) {
            $data = str_replace(array('-', '_'), array('+', '/'), $str);
            $mod4 = strlen($data) % 4;
            if ($mod4) {
                $data .= substr('====', $mod4);
            }
        } else {
            $data = $str;
        }
        return base64_decode($data);
    }

    /**
     * (PHP 4 &gt;= 4.0.1, PHP 5)<br/>
     * Compress a string using the ZLIB format
     * @link http://php.net/manual/en/function.gzcompress.php
     * @param string $str <p>
     * The string to compress.
     * </p>
     * @param int $level [optional] <p>
     * The level of compression. Can be given as 0 for no compression up to 9
     * for maximum compression.
     * </p>
     * <p>
     * If -1 is used, the default compression of the zlib library is used which is 6.
     * </p>
     * @return string The compressed string as binary or <b>FALSE</b> if an error occurred.
     */
    public static function compress($str, $level = 9)
    {
        return gzcompress($str, $level);
    }

    /**
     * (PHP 4 &gt;= 4.0.1, PHP 5)<br/>
     * Uncompress a compressed string using the ZLIB format
     * @link http://php.net/manual/en/function.gzuncompress.php
     * @param string $str <p>
     * The binary string compressed by <b>gzcompress</b>.
     * </p>
     * @param int $maxLength [optional] <p>
     * The maximum length of data to decode.
     * </p>
     * @return string The original uncompressed string or <b>FALSE</b> on error.
     * </p>
     * <p>
     * The function will return an error if the uncompressed string is more than
     * 32768 times the length of the compressed input <i>string</i>
     * or more than the optional parameter <i>maxLength</i>.
     */
    public static function uncompress($str, $maxLength = 0)
    {
        return gzuncompress($str, $maxLength);
    }

    /**
     * Returns a camelized string.
     * @param string $str
     * @return string
     */
    public static function camelize($str)
    {
        return lcfirst(trim(str_replace(' ', '', ucwords(strtolower(static::slug($str, ' '))))));
    }

    /**
     * Humanizes a camelized string, separating words
     * 
     * @param string $str Camelized string
     * @param string $delimiter Used for separating words
     * @return string 
     */
    public static function uncamelize($str, $delimiter = " ")
    {
        $str = preg_replace('/(?!^)[[:upper:]][[:lower:]]/', '$0', preg_replace('/(?!^)[[:upper:]]+/', $delimiter . '$0', $str));
        return strtolower($str);
    }

    /**
     * Converts any string to a friendly-url string.
     * Uses transliteration for special chars.
     * @param string $str
     * @param string $delimiter
     * @param array $replace Extra characters to be replaced with delimiter
     * @return string
     */
    public static function slug($str, $delimiter = '-', $replace = array())
    {
        if (!empty($replace)) {
            $str = str_replace((array) $replace, ' ', $str);
        }

        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
        $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
        $clean = strtolower(trim($clean, '- '));
        $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

        return $clean;
    }

    /**
     * Converts a friendly url-like formatted string to a human readable string.
     * Detects '-' and '_' word separators by default.
     * @param string $str
     * @param string $delimiter
     * @return string
     */
    public static function unslug($str, $delimiter = "-")
    {
        return str_replace($delimiter, " ", $str);
    }

    /**
     * Cleans a string with the specified rules
     * @param string $str
     * @param array $remove options
     * 
     * @return string 
     */
    public static function clean($str, $remove = array("html" => true, "quotes" => true, "backslashes" => true, "trim" => true))
    {
        $remove = array_merge(array("html" => true, "quotes" => true, "backslashes" => true, "trim" => true), $remove);

        if ($remove["html"]) { // remove html
            $str = strip_tags($str);
        }
        if ($remove["quotes"]) { // replace quotes and diacritic accents
            $str = str_replace(array("'", '"', '´', '`'), "", $str);
        }
        if ($remove["backslashes"]) { // remove backslashes
            $str = str_replace(array("\\"), "", $str);
        }
        if ($remove["trim"]) { // remove trailing whitespace and newlines
            $str = trim($str, "\n ");
        }
        return $str;
    }

    /**
     * Has the same behaviour than mysql_escape_string()
     * @param type $str
     * @return string 
     */
    public static function escape($str)
    {
        if (!function_exists("mysql_real_escape_string")) {
            $s = $str;
            $sl = strlen($s);
            for ($a = 0; $a < $sl; $a++) {
                $c = substr($s, $a, 1);

                switch (ord($c)) {
                    case 0:
                        $c = "\\0";
                        break;
                    case 10:
                        $c = "\\n";
                        break;
                    case 9:
                        $c = "\\t";
                        break;
                    case 13:
                        $c = "\\r";
                        break;
                    case 8:
                        $c = "\\b";
                        break;
                    case 39:
                        $c = "\\'";
                        break;
                    case 34:
                        $c = "\\\"";
                        break;
                    case 92:
                        $c = "\\\\";
                        break;
                    case 37:
                        $c = "\\%";
                        break;
                    case 95:
                        $c = "\\_";
                        break;
                }
                $s2.=$c;
            }
            return $s2;
        }
        return mysql_real_escape_string($str);
    }

    /**
     * Concatenates one or more strings, but only if they are not empty
     * 
     * @param string $glue
     * @param array|string $pieces An array of pieces or you can
     * pass multiple arguments as strings ($piece1, $piece2, ...)
     */
    public static function concat($glue, $pieces)
    {
        $args = func_get_args();
        $glue = array_shift($args);

        if ((count($args) == 1) && is_array($args[0])) {
            $pieces = $args[0];
        } else {
            $pieces = $args;
        }

        $pieces2 = array();
        foreach ($pieces as $i => $p) {
            if (!empty($p)) {
                $pieces2[] = $p;
            }
        }

        return implode($glue, $pieces2);
    }

    /**
     * Cuts a string if it exceeds the given $length, and appends the $append param
     * @param string $str Original string
     * @param int $length Max length
     * @param string $append String that will be appended if the original string exceeds $length
     * @return string 
     */
    public static function truncate($str, $length, $append = "")
    {
        if (($length > 0) && (strlen($str) > $length)) {
            return substr($str, 0, $length) . $append;
        }else
            return $str;
    }

    /**
     * Cuts a string by entire words if it exceeds the given $length, and appends the $append param
     * @param string $str Original string
     * @param int $length Max length
     * @param string $append String that will be appended if the original string exceeds $length
     * @return string 
     */
    public static function truncateWords($str, $length, $append = "")
    {
        $str2 = static::replaceRepeated($str, '\\s', ' ');
        $words = explode(" ", $str2);
        if (($length > 0) && (count($words) > $length)) {
            return implode(" ", array_slice($words, 0, $length)) . $append;
            //return substr($str, 0, $length).$append;
        }else
            return $str;
    }

    /**
     * Replaces repeated characters
     * 
     * @param string $str
     * @param string $char
     * @param string $replacement
     * @return string
     */
    public static function replaceRepeated($str, $char, $replacement = "")
    {
        return preg_replace('/' . $char . $char . '+/', $replacement, $str);
    }

}