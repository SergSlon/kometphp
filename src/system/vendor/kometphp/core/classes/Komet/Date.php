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
 * @package Komet
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 * 
 */
class Date
{

    public static function utc($time = null)
    {
        if ($time == null)
            $time = time();
        return date('Y-m-d\\TH:i:s\\.000\\Z', $time - date("Z"));
    }

    public static function fromStr($str_timestamp, $format = "d/m/Y")
    {
        return date($format, strtotime($str_timestamp));
    }

    public static function daysBetween($from, $to, $return_ts = false)
    {
        $start = strtotime($from);
        $end = strtotime($to);
        $num_days = round(($end - $start) / 86400 /* day in seconds */) + 1;
        $days = array();
        for ($d = 0; $d < $num_days; $d++) {
            $days[] = $start + ($d * 86400);
        }
        // Return days
        if (!$return_ts)
            return count($days);
        else
            return $days;
    }

    public static function inRange($date, $from, $to)
    {
        $times = static::daysBetween($from, $to);
        return in_array(strtotime($date), $times);
    }

    public static function secondsToHms($sec, $padHours = false)
    {

// start with a blank string
        $hms = "";

// do the hours first: there are 3600 seconds in an hour, so if we divide
// the total number of seconds by 3600 and throw away the remainder, we're
// left with the number of hours in those seconds
        $hours = intval(intval($sec) / 3600);

// add hours to $hms (with a leading 0 if asked for)
        $hms .= ( $padHours) ? str_pad($hours, 2, "0", STR_PAD_LEFT) . ":" : $hours . ":";

// dividing the total seconds by 60 will give us the number of minutes
// in total, but we're interested in *minutes past the hour* and to get
// this, we have to divide by 60 again and then use the remainder
        $minutes = intval(($sec / 60) % 60);

// add minutes to $hms (with a leading 0 if needed)
        $hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT) . ":";

// seconds past the minute are found by dividing the total number of seconds
// by 60 and using the remainder
        $seconds = intval($sec % 60);

// add seconds to $hms (with a leading 0 if needed)
        $hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);

// done!
        return $hms;
    }

    /**
     * Returns the number of days in the requested month
     *
     * @param   int  month as a number (1-12), leave empty for current
     * @param   int  the year, leave empty for current
     * @return  int  the number of days in the month
     */
    public static function daysInMonth($month = null, $year = null)
    {
        $year = !empty($year) ? (int) $year : (int) date('Y');
        $month = !empty($month) ? (int) $month : (int) date('n');

        if ($month < 1 or $month > 12) {
            throw new \UnexpectedValueException('Invalid input for month given.');
        } elseif ($month == 2) {
            if ($year % 400 == 0 or ($year % 4 == 0 and $year % 100 != 0)) {
                return 29;
            }
        }

        $days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        return $days_in_month[$month - 1];
    }

    /**
     * Returns the time ago
     *
     * @param	int		UNIX timestamp from current server
     * @param	int		UNIX timestamp to compare against. Default to the current time
     * @param	string	Unit to return the result in
     * @return	string	Time ago
     */
    public static function timeAgo($timestamp, $from_timestamp = null, $unit = null, $periods = array(), $periods_plural = array())
    {
        if ($timestamp === null) {
            return '';
        }
        if (!is_numeric($timestamp))
            $timestamp = strtotime($timestamp);

        if (empty($from_timestamp))
            $from_timestamp = time();
        elseif (!is_numeric($from_timestamp))
            $from_timestamp = strtotime($from_timestamp);

        $difference = $from_timestamp - $timestamp;

        !empty($periods) or $periods = array('second', 'minute', 'hour', 'day', 'week', 'month', 'year', 'decade');
        !empty($periods_plural) or $periods_plural = array('seconds', 'minutes', 'hours', 'days', 'weeks', 'months', 'years', 'decades');

        $lengths = array(60, 60, 24, 7, 4.35, 12, 10);

        for ($j = 0; isset($lengths[$j]) and $difference >= $lengths[$j] and (empty($unit) or $unit != $periods[$j]); $j++) {
            $difference /= $lengths[$j];
        }

        $difference = round($difference);

        if ($difference != 1) {
            $periods[$j] = $periods_plural[$j];
        }

        return $difference . " " . $periods[$j];
    }

    /**
     *
     * @param float $start_microtime
     * @param float $end_microtime
     * @param boolean $detailed_result
     * @return float|string 
     */
    public static function elapsedTime($start_microtime, $end_microtime = null, $decimal_precision = 3, $in_ms = false)
    {
        if ($end_microtime == null) {
            $end_microtime = microtime(true);
        }

        if ($in_ms)
            $decimal_precision+=3;

        $elapsed_time = round(($end_microtime - $start_microtime), $decimal_precision);

        if ($in_ms) {
            return $elapsed_time * 1000;
        } else {
            return $elapsed_time;
        }
    }

}