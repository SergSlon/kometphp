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
 */
class Input
{

    /**
     * HTTP Request Method
     * @var string 
     */
    protected $method;

    /**
     * URL base directory
     * @var string 
     */
    protected $detectedBaseDir;

    /**
     * Full host URL
     * @var string 
     */
    protected $detectedHostUrl;

    /**
     * @var int
     */
    protected $detectedPort = null;

    /**
     * @var string 
     */
    protected $domainId = null;

    /**
     * @var string 
     */
    protected $subdomainId = null;

    /**
     * Current URI
     * @var string 
     */
    protected $detectedUri;

    /**
     * URI extension
     * @var string 
     */
    protected $detectedExtension;

    /**
     * $_SERVER
     * @var array 
     */
    protected $serverVars = array();

    /**
     * $_ENV
     * @var array 
     */
    protected $envVars = array();

    /**
     * merged PUT, DELETE, POST and GET variables
     * @var array 
     */
    protected $inputVars = array();

    /**
     * $_POST
     * @var array 
     */
    protected $postVars = array();

    /**
     * $_GET
     * @var array 
     */
    protected $getVars = array();

    /**
     * $argv
     * @var array 
     */
    protected $argVars = array();

    /**
     * $_COOKIE
     * @var array 
     */
    protected $cookieVars = array();

    /**
     * $_FILES
     * @var array 
     */
    protected $files = array();

    /**
     *
     * @var string 
     */
    protected $rawInput;

    /**
     * Original superglobals
     * @var array An array with the varsExport() structure
     */
    protected $originals = array();

    public function __construct(array $vars = array())
    {
        $this->originals = $this->varsDetect();
        $this->varsImport($vars, true);
        $this->detectedUri();
        $this->detectedBaseUrl();
        $this->detectedBaseDir();
        $this->detectedExtension();
        $this->detectedPort();
        $this->detectedHostUrl();
    }

    /**
     * Imports global variables and other resolved values
     * 
     * @param array $vars Variables to import. Valid keys are:
     *  method, basedir, hosturl, uri, extension, server, env, input, post, get,
     *  arg, cookie, files, rawinput
     * @param bool $merge Whether to merge recursively with current instance variables
     *  (as defaults) or simply replace them entirely without merging
     */
    public function varsImport(array $vars, $merge = true)
    {
        if ($merge) {
            $vars = Arr::merge($this->varsExport(), $vars);
        }
        isset($vars['server']) and $this->serverVars = $vars['server'];

        $this->method = isset($vars['method']) ? $vars['method'] :
                (strtoupper($this->server('HTTP_X_HTTP_METHOD_OVERRIDE', null, $this->server('REQUEST_METHOD')) ? : null));
        $this->detectedBaseDir = isset($vars['basedir']) ?
                $vars['basedir'] : null;
        $this->detectedHostUrl = isset($vars['hosturl']) ?
                $vars['hosturl'] : null;
        $this->detectedUri = isset($vars['uri']) ? $vars['uri'] : null;
        $this->detectedExtension = isset($vars['extension']) ?
                $vars['extension'] : null;

        isset($vars['env']) and $this->envVars = $vars['env'];
        isset($vars['input']) and $this->inputVars = $vars['input'];
        isset($vars['post']) and $this->postVars = $vars['post'];
        isset($vars['get']) and $this->getVars = $vars['get'];
        isset($vars['arg']) and $this->argVars = $vars['arg'];
        isset($vars['cookie']) and $this->cookieVars = $vars['cookie'];
        isset($vars['files']) and $this->files = $vars['files'];
        isset($vars['rawinput']) and $this->rawInput = $vars['rawinput'];
    }

    /**
     * Merges global input variables into this object
     *
     * @param   array  $include  list of which superglobal variables to insert, empty for all
     * @return  array Array with exported values (the same as calling varsExport())
     */
    public function varsDetect(array $include = array())
    {
        $vars = array('server', 'env', 'input', 'post', 'get', 'arg', 'cookie', 'files');
        $vars = !$include ? $vars : array_intersect($include, $vars);

        in_array('server', $vars)
                and $this->serverVars += $_SERVER;

        if (is_null($this->method)) {
            if (isset($_SERVER['HTTP_X_REQUEST_METHOD'])) {
                $this->method = $_SERVER['HTTP_X_REQUEST_METHOD'];
            } elseif (isset($_SERVER['REQUEST_METHOD'])) {
                $this->method = $_SERVER['REQUEST_METHOD'];
            }
        }

        in_array('env', $vars)
                and $this->envVars += $_ENV;

        if (in_array('input', $vars)) {
            switch ($this->method) {
                case 'DELETE':
                case 'PUT':
                    $this->inputVars += $this->parseInput();
                    break;
                case 'POST':
                    $this->inputVars += $_POST;
                    break;
                case 'GET':
                default:
                    $this->inputVars += $_GET;
                    break;
            }
        }

        in_array('post', $vars)
                and $this->postVars += $_POST;

        in_array('get', $vars)
                and $this->getVars += $_GET;

        in_array('arg', $vars)
                and $this->argVars += $this->parseArgs();

        in_array('cookie', $vars)
                and $this->cookieVars += $_COOKIE;

        in_array('files', $vars)
                and $this->files += $_FILES;

        return $this->varsExport();
    }

    /**
     * 
     * @return array An array containing the following variables: method, basedir,
     * hosturl, uri, extension, server, env, input, post, get, arg, cookie,
     * files, rawinput
     * 
     */
    public function varsExport()
    {
        return array(
            "method" => $this->method,
            "basedir" => $this->detectedBaseDir,
            "hosturl" => $this->detectedHostUrl,
            "uri" => $this->detectedUri,
            "extension" => $this->detectedExtension,
            "server" => $this->serverVars,
            "env" => $this->envVars,
            "input" => $this->inputVars,
            "post" => $this->postVars,
            "get" => $this->getVars,
            "arg" => $this->argVars,
            "cookie" => $this->cookieVars,
            "files" => $this->files,
            "rawinput" => $this->rawInput
        );
    }

    /**
     * Original superglobals
     * @var array An array with the varsExport() structure
     */
    public function varsOriginals()
    {
        return $this->originals;
    }

    /**
     * Retrieves the base dir from the script name.
     *
     * @return  string  the base dir
     */
    public function detectedBaseDir()
    {
        if (\Komet\K::app()->isCLI) {
            return null;
        }
        if ($this->detectedBaseDir !== null) {
            return $this->detectedBaseDir;
        }
        $baseDir = '';
        if ($this->server('SCRIPT_NAME')) {
            $baseDir = str_replace('\\', '/', dirname($this->server('SCRIPT_NAME')));

            // Add a slash if it is missing
            $baseDir = rtrim($baseDir, '/') . '/';
        }
        $this->detectedBaseDir = $baseDir;
        return $this->detectedBaseDir;
    }

    public function detectedPort()
    {
        if (\Komet\K::app()->isCLI) {
            return null;
        }
        if ($this->detectedPort !== null) {
            return $this->detectedPort;
        }
        $this->detectedPort = intval($this->server('SERVER_PORT'));
        if (empty($this->detectedPort))
            $this->detectedPort = 80;
        return $this->detectedPort;
    }

    public function getPortPart()
    {
        $port = $this->detectedPort();
        return ($port == 80) ? "" : ":" . $port;
    }

    /**
     * Retrieves the host url.
     *
     * @return  string  the base url
     */
    public function detectedHostUrl()
    {
        if (\Komet\K::app()->isCLI) {
            return null;
        }
        if ($this->detectedHostUrl !== null) {
            return $this->detectedHostUrl;
        }
        $hostUrl = '';
        if ($this->server('SERVER_NAME')) {
            $hostUrl .= $this->protocol() . '://' . $this->server('SERVER_NAME') . $this->getPortPart() . "/";
        }
        $this->detectedHostUrl = $hostUrl;

        return $this->detectedHostUrl;
    }

    public function detectedBaseUrl()
    {
        if (\Komet\K::app()->isCLI) {
            return null;
        }
        return $this->detectedHostUrl() . ltrim($this->detectedBaseDir(), '/');
    }

    /**
     * Detects and returns the current URI based on a number of different server
     * variables.
     *
     * @return  string
     * @throws  \RuntimeException
     */
    public function detectedUri()
    {
        if (\Komet\K::app()->isCLI) {
            $this->detectedUri = null;
            return null;
        }

        if ($this->detectedUri !== null) {
            return $this->detectedUri;
        }

        // We want to use PATH_INFO if we can.
        if ($this->server('PATH_INFO') != null) {
            $uri = $this->server('PATH_INFO');
        }
        // Only use ORIG_PATH_INFO if it contains the path
        elseif ($this->server('ORIG_PATH_INFO') != null
                and ($path = str_replace($this->server('SCRIPT_NAME'), '', $this->server('ORIG_PATH_INFO'))) != '') {
            $uri = $path;
        } else {
            // Fall back to parsing the REQUEST URI
            $uri = $this->server('REQUEST_URI');
            if (is_null($uri)) {
                throw new \RuntimeException('Unable to detect the URI.');
            }

            // Remove the base URL from the URI
            $base_url = parse_url($this->detectedBaseUrl(), PHP_URL_PATH);
            if ($uri != '' and strncmp($uri, $base_url, strlen($base_url)) === 0) {
                $uri = substr($uri, strlen($base_url));
            }

            // If we are using an index file (not mod_rewrite) then remove it
            $index_file = \Komet\K::app()->config("index_file");
            if ($index_file and strncmp($uri, $index_file, strlen($index_file)) === 0) {
                $uri = substr($uri, strlen($index_file));
            }

            // When index.php? is used and the config is set wrong, lets just
            // be nice and help them out.
            if ($index_file and strncmp($uri, '?/', 2) === 0) {
                $uri = substr($uri, 1);
            }

            // Lets split the URI up in case it contains a ?.  This would
            // indicate the server requires 'index.php?' and that mod_rewrite
            // is not being used.
            preg_match('#(.*?)\?(.*)#i', $uri, $matches);

            // If there are matches then lets set set everything correctly
            if (!empty($matches)) {
                $uri = $matches[1];
                $this->serverVars['QUERY_STRING'] = $matches[2];
                parse_str($matches[2], $this->getVars);
            }
        }

        // Strip the defined url suffix from the uri if needed
        $uriInfo = pathinfo($uri);
        if (!empty($uriInfo['extension'])) {
            $this->detectedExtension = $uriInfo['extension'];
            $uri = $uriInfo['dirname'] . '/' . $uriInfo['filename'];
        }
        $this->detectedUri = '/' . ltrim($uri, '/');
        return $this->detectedUri;
    }

    /**
     * Detects and returns the current URI extension
     *
     * @return  string
     */
    public function detectedExtension()
    {
        !isset($this->detectedExtension) and $this->detectedUri();
        return $this->detectedExtension;
    }

    public function domain($domain = null, $excludeSubdomain = false)
    {
        if ($domain == null)
            $domain = $this->server("SERVER_NAME");
        if (!$excludeSubdomain) {
            return $domain;
        } else {
            $dom = explode(".", $domain);
            if (count($dom) == 1)
                return $dom[0];
            else {
                $lev1 = array_pop($dom);
                return array_pop($dom) . "." . $lev1;
            }
        }
    }

    public function subdomain($domain = null)
    {
        if ($domain == null)
            $domain = $this->server("SERVER_NAME");
        $dom = explode(".", $domain, 3);
        if (count($dom) < 3)
            return null;
        else {
            return array_shift($dom);
        }
    }

    public function subdomainUrl($subdomain = "www", $protocol = "http", $domain = null)
    {
        return $protocol . "://" . trim($subdomain, "/. ") . "." . $this->domain($domain, true) . $this->getPortPart() . "/";
    }

    /**
     * Returns the input method used (GET, POST, DELETE, etc.)
     *
     * @return  string
     */
    public function method()
    {
        return $this->method ? : 'GET';
    }

    /**
     * $_SERVER variable
     * 
     * @param string $index Array key
     * @param mixed $validation FILTER_* constant value or regular expression.
     *      If true is passed, the function will only check if the $index is set or not
     * @param mixed $default Default value if the variable is not set or regexp is false
     * @return mixed
     */
    public function server($index = null, $validation = null, $default = null)
    {
        if (is_null($index) and func_num_args() === 0) {
            return $this->serverVars;
        }
        if ($validation === true)
            return isset($this->serverVars[$index]);
        else
            return Arr::check($this->serverVars, $index, $default, $validation);
    }

    /**
     * $_ENV variable
     * 
     * @param string $index Array key
     * @param mixed $validation FILTER_* constant value or regular expression.
     *      If true is passed, the function will only check if the $index is set or not
     * @param mixed $default Default value if the variable is not set or regexp is false
     * @return mixed
     */
    public function env($index = null, $validation = null, $default = null)
    {
        if (is_null($index) and func_num_args() === 0) {
            return $this->envVars;
        }
        if ($validation === true)
            return isset($this->envVars[$index]);
        else
            return Arr::check($this->envVars, $index, $default, $validation);
    }

    /**
     * php://input variable (mixed DELETE, PUT, POST, GET)
     * 
     * @param string $index Array key
     * @param mixed $validation FILTER_* constant value or regular expression.
     *      If true is passed, the function will only check if the $index is set or not
     * @param mixed $default Default value if the variable is not set or regexp is false
     * @return mixed
     */
    public function input($index = null, $validation = null, $default = null)
    {
        if (is_null($index) and func_num_args() === 0) {
            return $this->inputVars;
        }

        if ($validation === true)
            return isset($this->inputVars[$index]);
        else
            return Arr::check($this->inputVars, $index, $default, $validation);
    }

    /**
     * $_POST variable
     * 
     * @param string $index Array key
     * @param mixed $validation FILTER_* constant value or regular expression.
     *      If true is passed, the function will only check if the $index is set or not
     * @param mixed $default Default value if the variable is not set or regexp is false
     * @return mixed
     */
    public function post($index = null, $validation = null, $default = null)
    {
        if (is_null($index) and func_num_args() === 0) {
            return $this->postVars;
        }

        if ($validation === true)
            return isset($this->postVars[$index]);
        else
            return Arr::check($this->postVars, $index, $default, $validation);
    }

    /**
     * $_GET variable
     * 
     * @param string $index Array key
     * @param mixed $validation FILTER_* constant value or regular expression.
     *      If true is passed, the function will only check if the $index is set or not
     * @param mixed $default Default value if the variable is not set or regexp is false
     * @return mixed
     */
    public function get($index = null, $validation = null, $default = null)
    {
        if (is_null($index) and func_num_args() === 0) {
            return $this->getVars;
        }

        if ($validation === true)
            return isset($this->getVars[$index]);
        else
            return Arr::check($this->getVars, $index, $default, $validation);
    }

    /**
     * $_SERVER["argv"] variable (associated)
     * 
     * @param string $index Array key
     * @param mixed $validation FILTER_* constant value or regular expression.
     *      If true is passed, the function will only check if the $index is set or not
     * @param mixed $default Default value if the variable is not set or regexp is false
     * @return mixed
     */
    public function arg($index = null, $validation = null, $default = null)
    {
        if (is_null($index) and func_num_args() === 0) {
            return $this->argVars;
        }

        if ($validation === true)
            return isset($this->argVars[$index]);
        else
            return Arr::check($this->argVars, $index, $default, $validation);
    }

    /**
     * $_COOKIE variable
     * 
     * @param string $index Array key
     * @param mixed $validation FILTER_* constant value or regular expression.
     *      If true is passed, the function will only check if the $index is set or not
     * @param mixed $default Default value if the variable is not set or regexp is false
     * @return mixed
     */
    public function cookie($index = null, $validation = null, $default = null)
    {
        if (is_null($index) and func_num_args() === 0) {
            return $this->cookieVars;
        }

        if ($validation === true)
            return isset($this->cookieVars[$index]);
        else
            return Arr::check($this->cookieVars, $index, $default, $validation);
    }

    /**
     * $_FILES variable
     * 
     * @param string $index Array key
     * @param mixed $validation FILTER_* constant value or regular expression.
     *      If true is passed, the function will only check if the $index is set or not
     * @param mixed $default Default value if the variable is not set or regexp is false
     * @return mixed
     */
    public function file($index = null, $validation = null, $default = null)
    {
        if (is_null($index) and func_num_args() === 0) {
            return $this->files;
        }

        if ($validation === true)
            return isset($this->files[$index]);
        else
            return Arr::check($this->files, $index, $default, $validation);
    }

    /**
     * Raw php://input
     * @return string 
     */
    public function rawInput()
    {
        return $this->rawInput;
    }

    /**
     * Parses the CLI parameters from $_SERVER['argv']
     *
     * @return  void
     */
    protected function parseArgs()
    {
        $this->argVars = $this->server('argv') ? : array();
        foreach ($this->argVars as $i => $arg) {
            $arg = explode('=', $arg);
            $this->argVars[$i] = reset($arg);

            if (count($arg) > 1 or strncmp(reset($arg), '-', 1) === 0) {
                $this->argVars[ltrim(reset($arg), '-')] = isset($arg[1]) ? $arg[1] : true;
            }
        }

        return $this->argVars;
    }

    /**
     * Returns the PUT/DELETE parameters fetched from php://input
     * (this is static as it can only be fetched once)
     *
     * @return  array
     */
    protected function parseInput()
    {
        parse_str(file_get_contents('php://input'), $this->rawInput);

        return $this->rawInput;
    }

    /**
     * Get the public ip address of the user.
     *
     * @param   string  $default
     * @return  string
     */
    public function ip($default = '0.0.0.0')
    {
        if ($this->server('REMOTE_ADDR') !== null) {
            return $this->server('REMOTE_ADDR');
        }
        return $default;
    }

    /**
     * Get the real ip address of the user.  Even if they are using a proxy.
     *
     * @param   string  @default  default return value when no IP is detected
     * @return  string  the real ip address of the user
     */
    public function realIp($default = '0.0.0.0')
    {
        if ($this->server('HTTP_X_CLUSTER_CLIENT_IP') !== null) {
            return $this->server('HTTP_X_CLUSTER_CLIENT_IP');
        } elseif ($this->server('HTTP_X_FORWARDED_FOR') !== null) {
            return $this->server('HTTP_X_FORWARDED_FOR');
        } elseif ($this->server('HTTP_CLIENT_IP') !== null) {
            return $this->server('HTTP_CLIENT_IP');
        } elseif ($this->server('REMOTE_ADDR') !== null) {
            return $this->server('REMOTE_ADDR');
        }

        // detection failed, return the default
        return $default;
    }

    /**
     * Return the country code of the IP using shell comand 'whois'
     * @param string $ip
     * @return string
     */
    public function ipCountry($ip)
    {
        return @shell_exec('whois ' . $ip . ' -H | grep country | awk \'{print $2}\'');
    }

    /**
     * Returns the protocol that the request was made with
     *
     * @return  string
     */
    public function protocol()
    {
        $https = strtolower($this->server('HTTPS', null, $this->server('HTTP_HTTPS', null, "off")));
        if ((!is_null($https) and $https != 'off')
                or (is_null($https) and $this->server('SERVER_PORT') == 443)) {
            return 'https';
        }

        return 'http';
    }

    public function isHttps()
    {
        return $this->protocol() == "https";
    }

    /**
     * Returns whether this is an AJAX request or not
     *
     * @return  bool
     */
    public function isXmlHttpRequest()
    {
        return ($this->server('HTTP_X_REQUESTED_WITH') !== null)
                and strtolower($this->server('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest';
    }

    /**
     * Alias for \Komet\Input::isXmlHttpRequest()
     *
     * @return  bool
     */
    public function isAjax()
    {
        return $this->isXmlHttpRequest();
    }

    /**
     * Returns the referrer
     *
     * @param   string  $default
     * @return  string
     */
    public function referrer($default = '')
    {
        return $this->server('HTTP_REFERER', null, $default);
    }

    /**
     * Returns the user agent
     *
     * @param   string  $default
     * @return  string
     */
    public function userAgent($default = '')
    {
        return $this->server('HTTP_USER_AGENT', null, $default);
    }

    public function authUser()
    {
        return $this->server("PHP_AUTH_USER", false);
    }

    public function authPassword()
    {
        return $this->server("PHP_AUTH_PW", false);
    }

    public function authDigest()
    {
        return $this->server("PHP_AUTH_DIGEST", false);
    }

}