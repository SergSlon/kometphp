<?php

/**
 * Part of the KometPHP Framework
 * 
 * @package Komet/Core
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */

namespace Komet;

/**
 * Event handler
 * 
 * Listener functions should be implemented in the child class.
 * This will prevent hardcoding event names.
 * 
 * @package Komet/Core
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */
trait Listenable
{

    /**
     * All registered event listeners
     * @var array[string][array]
     */
    protected $listeners;

    /**
     * Event log and profiler
     * @var array 
     */
    protected $log = array();

    /**
     * Trigger all event listeners for that event
     * 
     * @param   string  $event       The event name or name REGEXP
     * @param   mixed   $arg   (Optional) Argument that will be passed BY REFERENCE to the listeners
     * @return  void
     */
    public function trigger($event, &$arg = null)
    {
        if (Str::isRegex($event)) {
            foreach ($this->listeners as $e => $priorities) {
                if (preg_match($event, $e)) {
                    $this->trigger($event, $arg);
                }
            }
            return;
        }
        $log = array("event" => $event, "listened" => 0, "start_time" => microtime(true), "start_mem" => memory_get_usage());
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = array(array());
        }
        if (!empty($this->listeners[$event])) {
            // Sort by priority, high to low, if there's more than one priority
            if (count($this->listeners[$event]) > 1) {
                ksort($this->listeners[$event], SORT_DESC);
            }
            foreach ($this->listeners[$event] as $priority) {
                if (!empty($priority)) {
                    foreach ($priority as $callable) {
                        call_user_func($callable, $arg);
                        $log["listened"]++;
                    }
                }
            }
        }
        $log["end_time"] = microtime(true);
        $log["end_mem"] = memory_get_usage();
        $this->log[] = $log;
    }

    /**
     * Add an event listener
     * 
     * @param   string  $event       The event name or name REGEXP
     * @param   callable   $callable   A callable function
     * @param   int     $priority   The event priority; 0 = higher, 10 = lower
     * @return  boolean
     */
    public function bind($event, $callable, $priority = 10)
    {
        if (Str::isRegex($event)) {
            $binded = true;
            foreach ($this->listeners as $e => $priorities) {
                if (preg_match($event, $e)) {
                    $binded = $binded && $this->bind($event, $callable, $priority);
                }
            }
            return $binded;
        }
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = array(array());
        }
        if (is_callable($callable)) {
            $this->listeners[$event][(int) $priority][] = $callable;
            return true;
        }
        return false;
    }

    /**
     * Remove the specified event listeners.
     *
     * @param   string  $event The event name or name REGEXP
     * @return  boolean
     */
    public function unbind($event)
    {
        if (Str::isRegex($event)) {
            $unbinded = true;
            foreach ($this->listeners as $e => $priorities) {
                if (preg_match($event, $e)) {
                    $unbinded = $unbinded && $this->unbind($event);
                }
            }
            return $unbinded;
        }
        if (!empty($event) && isset($this->listeners[(string) $event])) {
            $this->listeners[(string) $event] = array(array());
            return true;
        }
        return false;
    }

    /**
     * 
     * @return array
     */
    public function getListeners()
    {
        return $this->listeners;
    }

    public function getEventLog()
    {
        return $this->log;
    }

    public function resetEventLog()
    {
        $this->log = array();
    }

}