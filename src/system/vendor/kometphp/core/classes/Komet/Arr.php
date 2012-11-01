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
 * Array utilities
 * 
 * @package Komet
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 * 
 */
class Arr
{

    /**
     * Gets the first value of an array
     * 
     * @param array $arr
     * @return mixed 
     */
    public static function first($arr)
    {
        return reset($arr);
    }

    /**
     * Gets the last value of an array
     * 
     * @param array $arr
     * @return mixed 
     */
    public static function last($arr)
    {
        return end($arr);
    }

    /**
     * Checks if a value exists in an array
     * 
     * @param mixed $needle The searched value.
     * @param array $haystack The array to search in
     * @param boolean $strict [optional] If the third parameter strict is set to true then the in_array function will also check the types of the needle in the haystack.
     * @param boolean $case_insensitive If needle is a string, the comparison can be done in a case-insensitive manner.
     * @return boolean 
     */
    public static function contains($needle, $haystack, $strict = false, $case_insensitive = false)
    {
        return in_array(strtolower($needle), $case_insensitive ? array_map('strtolower', $haystack) : $haystack, $strict);
    }

    /**
     * Checks if a variable exists inside an array and matches the given php filter or regular expression.
     * If it matches returns the variable value, otherwise returns $default
     * 
     * @param array $arr Associated array of values
     * @param string $key Array key name
     * @param mixed $default Default value if the variable is not set or regexp is false
     * @param mixed $validation FILTER_* constant value, regular expression or callable method/function (that returns a boolean i.e. is_string)
     * @return mixed The variable value
     */
    public static function check($arr, $key, $default = null, $validation = null)
    {
        if (!is_array($arr))
            return $default;
        if (static::dotisset($arr, $key)) {
            $value = static::dotget($arr, $key, $default);
            if ($validation != null) {
                if (is_string($validation) && ($validation{0} == "/")) {
                    //regexp
                    return (preg_match($validation, $value) > 0) ? $value : $default;
                } elseif (is_int($validation)) {
                    return filter_var($value, $validation) ? $value : $default;
                } elseif (is_callable($validation)) {
                    return $validation($value) ? $value : $default;
                }
            }else
                return $value;
        }else
            return $default;
    }

    /**
     * Merges arrays recursively with the same behaviour as array_merge
     * @return type 
     */
    public static function merge()
    {
        if (func_num_args() < 2) {
            trigger_error(__METHOD__ . ' needs two or more array arguments', E_USER_WARNING);
            return;
        }
        $arrays = func_get_args();
        $merged = array();
        while ($arrays) {
            $array = array_shift($arrays);
            if (!is_array($array)) {
                trigger_error(__METHOD__ . ' encountered a non array argument', E_USER_WARNING);
                return;
            }
            if (!$array)
                continue;
            foreach ($array as $key => $value)
                if (is_string($key))
                    if (is_array($value) && array_key_exists($key, $merged) && is_array($merged[$key]))
                        $merged[$key] = call_user_func(__METHOD__, $merged[$key], $value);
                    else
                        $merged[$key] = $value;
                else
                    $merged[] = $value;
        }
        return $merged;
    }

    /**
     * Sorts an array of associative arrays or objects by field
     * @param string $field
     * @param array $arr
     * @param int $sorting
     * @param boolean $case_insensitive
     * @return boolean 
     */
    public static function sortby($field, &$arr, $sorting = SORT_ASC, $case_insensitive = true)
    {
        if (is_array($arr) && (count($arr) > 0)) {
            if ($case_insensitive == true)
                $strcmp_fn = "strnatcasecmp";
            else
                $strcmp_fn = "strnatcmp";

            if ($sorting == SORT_ASC) {
                $fn = create_function('$a,$b', '
                if(is_object($a) && is_object($b) && isset($a->' . $field . ') && isset($b->' . $field . ')){
                    return ' . $strcmp_fn . '($a->' . $field . ', $b->' . $field . ');
                }else if(is_array($a) && is_array($b) && isset($a["' . $field . '"]) && isset($b["' . $field . '"])){
                    return ' . $strcmp_fn . '($a["' . $field . '"], $b["' . $field . '"]);
                }else return 0;
            ');
            } else {
                $fn = create_function('$a,$b', '
                if(is_object($a) && is_object($b) && isset($a->' . $field . ') && isset($b->' . $field . ')){
                    return ' . $strcmp_fn . '($b->' . $field . ', $a->' . $field . ');
                }else if(is_array($a) && is_array($b) && isset($a["' . $field . '"]) && isset($b["' . $field . '"])){
                    return ' . $strcmp_fn . '($b["' . $field . '"], $a["' . $field . '"]);
                }else return 0;
            ');
            }
            usort($arr, $fn);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns an array containing the values of the specified field key, with
     * @param array $arr array of objects or/and associated arrays
     * @param string $key Field to use as array value.
     * @param string $index Field to use as array index. Optional.
     * @return array
     */
    public static function pluck($arr, $key, $index = null)
    {
        if (empty($arr) || (!is_array($arr)))
            return array();

        $values = array();

        foreach ($arr as $item) {
            if (is_array($item) && isset($item[$key])) {
                if ($index && isset($item[$index]))
                    $values[$item[$index]] = $item[$key];
                else
                    $values[] = $item[$key];
            }elseif (is_object() && isset($item->$key)) {
                if ($index && isset($item->$index))
                    $values[$item->$index] = $item->$key;
                else
                    $values[] = $item->$key;
            }
        }
        return $values;
    }

    /**
     * Flattens a multi-dimensional associative array down into a 1 dimensional
     * associative array.
     *
     * @param   array   the array to flatten
     * @param   string  what to glue the keys together with
     * @param   bool    whether to reset and start over on a new array
     * @param   bool    whether to flatten only associative array's, or also indexed ones
     * @return  array
     */
    public static function flatten($array, $glue = '.', $reset = true, $indexed = true)
    {
        static $return = array();
        static $curr_key = array();

        if ($reset) {
            $return = array();
            $curr_key = array();
        }

        foreach ($array as $key => $val) {
            $curr_key[] = $key;
            if (is_array($val) and ($indexed or array_values($val) !== $val)) {
                static::flatten($val, $glue, false, false);
            } else {
                $return[implode($glue, $curr_key)] = $val;
            }
            array_pop($curr_key);
        }
        return $return;
    }

    /**
     * Reverse a flattened array in its original form.
     *
     * @param   array   $array  flattened array
     * @param   string  $glue   glue used in flattening
     * @return  array   the unflattened array
     */
    public static function unflatten($array, $glue = '.')
    {
        $return = array();

        foreach ($array as $key => $value) {
            if (stripos($key, $glue) !== false) {
                $keys = explode($glue, $key);
                $temp = & $return;
                while (count($keys) > 1) {
                    $key = array_shift($keys);
                    $key = is_numeric($key) ? (int) $key : $key;
                    if (!isset($temp[$key]) or !is_array($temp[$key])) {
                        $temp[$key] = array();
                    }
                    $temp = & $temp[$key];
                }

                $key = array_shift($keys);
                $key = is_numeric($key) ? (int) $key : $key;
                $temp[$key] = $value;
            } else {
                $key = is_numeric($key) ? (int) $key : $key;
                $return[$key] = $value;
            }
        }

        return $return;
    }

    /**
     * Get a value from an array according to a dot-notated key
     *
     * @param   array|\ArrayAccess  $arr
     * @param   string              $key
     * @param   mixed              $default Default value if key is not set
     * @return  mixed
     * @throws  \InvalidArgumentException
     *
     */
    public static function dotget(&$arr, $key, $default = null)
    {
        if (!is_array($arr) and !$arr instanceof \ArrayAccess) {
            throw new \InvalidArgumentException('The first argument of dotGet() must be an array or ArrayAccess object.');
        }

        if (isset($arr[$key]))
            return $arr[$key];

        // Explode the key and start iterating
        $keys = explode('.', $key);

        while (count($keys) > 0) {
            $key = array_shift($keys);
            if (!isset($arr[$key])) {
                return $default;
            }
            $arr = & $arr[$key];
        }

        return $arr;
    }

    /**
     * Set a value on an array according to a dot-notated key
     *
     * @param   array|\ArrayAccess  $arr
     * @param   string              $key
     * @param   mixed               $value
     * @return  bool
     * @throws  \InvalidArgumentException
     *
     */
    public static function dotset(&$arr, $key, &$value)
    {
        if (!is_array($arr) and !$arr instanceof \ArrayAccess) {
            throw new \InvalidArgumentException('The first argument of dotSet() must be an array or ArrayAccess object.');
        }

        // Explode the key and start iterating
        $keys = explode('.', $key);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($arr[$key])
                    or (!empty($keys) and !is_array($arr[$key]) and !$arr[$key] instanceof \ArrayAccess)) {

                // Create new subarray or overwrite non array
                $arr[$key] = array();
            }
            $arr = & $arr[$key];
        }
        $key = array_shift($keys);

        $arr[$key] = $value;

        return true;
    }

    /**
     * Checks if a key exists in an array according to a dot-notated key
     *
     * @param   array|\ArrayAccess  $arr
     * @param   string              $key
     * @return  boolean
     * @throws  \InvalidArgumentException
     *
     */
    public static function dotisset($arr, $key)
    {
        if (!is_array($arr) and !$arr instanceof \ArrayAccess) {
            throw new \InvalidArgumentException('The first argument of dotIsset() must be an array or ArrayAccess object.');
        }

        if (isset($arr[$key]))
            return true;
        // Explode the key and start iterating
        $keys = explode('.', $key);

        while (count($keys) > 0) {
            $key = array_shift($keys);
            if (!isset($arr[$key])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Unset a value on an array according to a dot-notated key
     *
     * @param   array|\ArrayAccess  $arr
     * @param   string              $key
     * @return  bool
     * @throws  \InvalidArgumentException
     *
     */
    public static function dotunset(&$arr, $key)
    {
        if (!is_array($arr) and !$arr instanceof \ArrayAccess) {
            throw new \InvalidArgumentException('The first argument of dotUnset() must be an array or ArrayAccess object.');
        }

        // Explode the key and start iterating
        $keys = explode('.', $key);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($arr[$key])
                    or (!empty($keys) and !is_array($arr[$key]) and !$arr[$key] instanceof \ArrayAccess)) {
                // Unset impossible
                return false;
            }
            $arr = & $arr[$key];
        }
        $key = array_shift($keys);

        if (!isset($arr[$key])) {
            return false;
        }

        unset($arr[$key]);

        return true;
    }

}