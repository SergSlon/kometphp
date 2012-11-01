<?php

if (!function_exists("msie_version")):

    function msie_version($userAgent)
    {
        $match = preg_match('/MSIE ([0-9]\.[0-9])/', $userAgent, $reg);
        if ($match == 0)
            return -1;
        else
            return floatval($reg[1]);
    }

endif;
if (!function_exists("msie_classes")):

    function msie_classes($userAgent, $max_version = 12)
    {
        $v = intval(msie_version($userAgent));
        if ($v == -1) {
            return "no-ie";
        } else {
            $classes = array("ie", "ie" . $v);
            for ($i = 6; $i <= $max_version; $i++) {
                if ($v < $i) {
                    $classes[] = "lt-ie" . $i;
                } elseif ($v > $i) {
                    $classes[] = "gt-ie" . $i;
                }
            }
            return implode(" ", $classes);
        }
    }

endif;

if (!function_exists("removecookie")):

    function removecookie($name, $path = null, $domain = null, $secure = false, $httponly = null)
    {
        return setcookie($name, "", time() - 3600, $path, $domain, $secure, $httponly);
    }

endif;

if (!function_exists("session_reset")):

    function session_reset()
    {
        if (session_id()) {

            $last_id = session_id();
            session_unset();
            session_destroy();
            session_start();
            //we ensure that it's a whole new session
            if (session_id() == $last_id) {
                session_regenerate_id(true);
            }
            return true;
        } else {
            session_start();
        }
        return false;
    }

endif;

if (!function_exists("session_validate_id")):

    function session_validate_id($fingerprint = null, $lifetime = 0)
    {
        if (!empty($fingerprint)) {
            if (isset($_SESSION['PHPSESSID_FINGERPRINT']) && ($_SESSION['PHPSESSID_FINGERPRINT'] != $fingerprint)) {
                session_reset();
                return false;
            } else {
                $_SESSION['PHPSESSID_FINGERPRINT'] = $fingerprint;
            }
        }

        //Regenerates the session ID if the current one is expired.
        if ($lifetime > 0) {
            if (isset($_SESSION['PHPSESSID_LIFETIME'])) {
                if (time() >= $_SESSION['PHPSESSID_LIFETIME']) {
                    // Create new session id without destroying the session data
                    session_regenerate_id(false);
                    $_SESSION['PHPSESSID_LIFETIME'] = time() + $lifetime;
                }
            } else {
                $_SESSION['PHPSESSID_LIFETIME'] = time() + $lifetime;
            }
        }
        return true;
    }

endif;

if (!function_exists("memory_get_available")):

    /**
     * Returns the current available PHP memory in bytes
     * 
     * @return int amount in bytes
     */
    function memory_get_available()
    {
        return memory_get_limit() - memory_get_usage(true);
    }

endif;

if (!function_exists("memory_get_limit")):

    /**
     * Returns the PHP memory limit in bytes
     * 
     * @return int amount in bytes
     */
    function memory_get_limit()
    {
        $memory_limit = ini_get("memory_limit");
        if (strpos($memory_limit, "M")) {
            $memory_limit = intval($memory_limit) * 1024 * 1024;
        } elseif (strpos($memory_limit, "K")) {
            $memory_limit = intval($memory_limit) * 1024;
        } else {
            $memory_limit = intval($memory_limit);
        }
        return $memory_limit;
    }


endif;