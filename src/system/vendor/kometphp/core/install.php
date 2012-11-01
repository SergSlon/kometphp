<?php
/**
 * install.php file based on kohana's
 * 
 * Part of the KometPHP Framework
 * 
 * @package Komet
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */

// Sanity check, install should only be checked from index.php
isset($environment) or exit('Install tests must be loaded from within index.php!');

if (version_compare(PHP_VERSION, '5.3', '<'))
{
	// Clear out the cache to prevent errors. This typically happens on Windows/FastCGI.
	clearstatcache();
}
else
{
	// Clearing the realpath() cache is only possible PHP 5.3+
	clearstatcache(TRUE);
}

?><!DOCTYPE html>
<html lang="en">
<head>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>KometPHP Installation</title>

	<style type="text/css">
	body { width: 640px; margin: 20px auto; font-family: "Open Sans", helvetica, arial, sans-serif; background: #fff; font-size: 12px; }
	h1 { letter-spacing: -0.04em; font-size:200%; }
        h1 img{ margin-right:10px; }
	h1 + p { margin: 0 0 2em; color: #333; font-size: 95%; font-style: italic; }
        a {color: inherit;}
	code { font-family: monaco, monospace; }
	table { border-collapse: collapse; width: 100%; }
		table th,
		table td { padding: 0.4em; text-align: left; vertical-align: top; }
		table th { width: 18em; font-weight: normal; }
		table tr:nth-child(odd) { background: #f4f4f4; }
		table td.pass { color: #191; }
		table td.fail { color: #911; font-weight: bold; }
        table.tests-optional td.fail { color: #999; font-weight: normal;  }
        table.tests-optional td.fail a{ color: #777; }
	#results { padding: 0.8em; color: #fff; font-size: 140%; }
	#results.pass { background: #191; }
	#results.fail { background: #911; }
	</style>

</head>
<body>

	<h1><img title="KometPHP Framework" src="https://raw.github.com/kometphp/kometphp/master/logo.png" height="26" alt="KometPHP" /> Environment Tests</h1>

	<p>
		The following tests have been run to determine if <a target="_blank" href="https://github.com/kometphp/kometphp">KometPHP</a> will work in your environment.
		If any of the tests have failed, consult the <a target="_blank" href="https://github.com/kometphp/kometphp/wiki">documentation</a>
		for more information on how to correct the problem.
	</p>

	<?php $failed = FALSE ?>

	<table class="tests-required" cellspacing="0">
		<tr>
			<th>PHP Version 5.4.0+</th>
			<?php if (version_compare(PHP_VERSION, '5.4.0', '>=')): ?>
				<td class="pass"><?php echo PHP_VERSION ?></td>
			<?php else: $failed = TRUE ?>
				<td class="fail">KometPHP requires PHP 5.4.0 or newer, this version is <?php echo PHP_VERSION ?>.</td>
			<?php endif ?>
		</tr>
		<tr>
			<th>Safe Mode disabled</th>
			<?php if ((ini_get('safe_mode') == false)): ?>
				<td class="pass">Pass</td>
			<?php else: $failed = TRUE ?>
				<td class="fail">KometPHP cannot run under PHP safe mode.</td>
			<?php endif ?>
		</tr>
		<tr>
			<th>Composer autoloader</th>
			<?php if (is_readable($environment["paths"]["vendor"] . "autoload.php")): ?>
				<td class="pass"><?php echo $environment["paths"]["vendor"] . "autoload.php" ?></td>
			<?php else: $failed = TRUE ?>
				<td class="fail">Composer is not installed. Please <a target="_blank" href="https://github.com/kometphp/kometphp/blob/master/README.md">install all dependencies using composer</a> before running KometPHP.</td>
			<?php endif ?>
		</tr>
		<tr>
			<th>Root Directory</th>
			<?php if (is_dir($environment["paths"]["root"]) AND is_writable($environment["paths"]["root"])): ?>
				<td class="pass"><?php echo $environment["paths"]["root"] ?></td>
			<?php else: $failed = TRUE ?>
				<td class="fail">The <code><?php echo $environment["paths"]["root"] ?></code> directory does not exist or is not writable.</td>
			<?php endif ?>
		</tr>
		<tr>
			<th>Public Directory</th>
			<?php if (is_dir($environment["paths"]["public"]) AND is_writable($environment["paths"]["public"])): ?>
				<td class="pass"><?php echo $environment["paths"]["public"] ?></td>
			<?php else: $failed = TRUE ?>
				<td class="fail">The <code><?php echo $environment["paths"]["public"] ?></code> directory does not exist or is not writable.</td>
			<?php endif ?>
		</tr>
		<tr>
			<th>Vendor Directory</th>
			<?php if (is_dir($environment["paths"]["vendor"]) AND is_writable($environment["paths"]["vendor"])): ?>
				<td class="pass"><?php echo $environment["paths"]["vendor"] ?></td>
			<?php else: $failed = TRUE ?>
				<td class="fail">The <code><?php echo $environment["paths"]["vendor"] ?></code> directory does not exist or is not writable.</td>
			<?php endif ?>
		</tr>
		<tr>
			<th>PCRE UTF-8</th>
			<?php if ( ! @preg_match('/^.$/u', 'ñ')): $failed = TRUE ?>
				<td class="fail"><a target="_blank" href="http://php.net/pcre">PCRE</a> has not been compiled with UTF-8 support.</td>
			<?php elseif ( ! @preg_match('/^\pL$/u', 'ñ')): $failed = TRUE ?>
				<td class="fail"><a target="_blank" href="http://php.net/pcre">PCRE</a> has not been compiled with Unicode property support.</td>
			<?php else: ?>
				<td class="pass">Pass</td>
			<?php endif ?>
		</tr>
		<tr>
			<th>SPL Enabled</th>
			<?php if (function_exists('spl_autoload_register')): ?>
				<td class="pass">Pass</td>
			<?php else: $failed = TRUE ?>
				<td class="fail">PHP <a target="_blank" href="http://www.php.net/spl">SPL</a> is either not loaded or not compiled in.</td>
			<?php endif ?>
		</tr>
		<tr>
			<th>Reflection Enabled</th>
			<?php if (class_exists('ReflectionClass')): ?>
				<td class="pass">Pass</td>
			<?php else: $failed = TRUE ?>
				<td class="fail">PHP <a target="_blank" href="http://www.php.net/reflection">reflection</a> is either not loaded or not compiled in.</td>
			<?php endif ?>
		</tr>
		<tr>
			<th>Filters Enabled</th>
			<?php if (function_exists('filter_list')): ?>
				<td class="pass">Pass</td>
			<?php else: $failed = TRUE ?>
				<td class="fail">The <a target="_blank" href="http://www.php.net/filter">filter</a> extension is either not loaded or not compiled in.</td>
			<?php endif ?>
		</tr>
		<tr>
			<th>Mcrypt Enabled</th>
			<?php if (extension_loaded('mcrypt')): ?>
				<td class="pass">Pass</td>
			<?php else: $failed = TRUE ?>
				<td class="fail">The <a target="_blank" href="http://www.php.net/mcrypt">filter</a> extension is either not loaded or not compiled in.</td>
			<?php endif ?>
		</tr>
		<tr>
			<th>Hash Enabled</th>
			<?php if (extension_loaded('hash')): ?>
				<td class="pass">Pass</td>
			<?php else: $failed = TRUE ?>
				<td class="fail">The <a target="_blank" href="http://www.php.net/hash">filter</a> extension is either not loaded or not compiled in.</td>
			<?php endif ?>
		</tr>
		<tr>
			<th>Iconv Extension Loaded</th>
			<?php if (extension_loaded('iconv')): ?>
				<td class="pass">Pass</td>
			<?php else: $failed = TRUE ?>
				<td class="fail">The <a target="_blank" href="http://php.net/iconv">iconv</a> extension is not loaded.</td>
			<?php endif ?>
		</tr>
		<?php if (extension_loaded('mbstring')): ?>
		<tr>
			<th>Mbstring Not Overloaded</th>
			<?php if (ini_get('mbstring.func_overload') & MB_OVERLOAD_STRING): $failed = TRUE ?>
				<td class="fail">The <a target="_blank" href="http://php.net/mbstring">mbstring</a> extension is overloading PHP's native string functions.</td>
			<?php else: ?>
				<td class="pass">Pass</td>
			<?php endif ?>
		</tr>
		<?php endif ?>
		<tr>
			<th>Character Type (CTYPE) Extension</th>
			<?php if ( ! function_exists('ctype_digit')): $failed = TRUE ?>
				<td class="fail">The <a target="_blank" href="http://php.net/ctype">ctype</a> extension is not enabled.</td>
			<?php else: ?>
				<td class="pass">Pass</td>
			<?php endif ?>
		</tr>
		<tr>
			<th>URI Determination</th>
			<?php if (isset($_SERVER['REQUEST_URI']) OR isset($_SERVER['PHP_SELF']) OR isset($_SERVER['PATH_INFO'])): ?>
				<td class="pass">Pass</td>
			<?php else: $failed = TRUE ?>
				<td class="fail">Neither <code>$_SERVER['REQUEST_URI']</code>, <code>$_SERVER['PHP_SELF']</code>, or <code>$_SERVER['PATH_INFO']</code> is available.</td>
			<?php endif ?>
		</tr>
	</table>

	<?php if ($failed === TRUE): ?>
		<p id="results" class="fail">✘ KometPHP may not work correctly with your environment.</p>
	<?php else: ?>
		<p id="results" class="pass">✔ Your environment passed all requirements.<br />
			You can now delete the src/check.php file and refresh this page.
                </p>
	<?php endif ?>

	<h1>Optional Tests</h1>

	<p>
		The following extensions are not required to run the KometPHP core, but if enabled can provide access to additional features.
	</p>

	<table class="tests-optional" cellspacing="0">
<!--		<tr>
			<th>GD Enabled</th>
			<?php if (function_exists('gd_info')): ?>
				<td class="pass">Pass</td>
			<?php else: ?>
				<td class="fail">KometPHP requires the <a target="_blank" href="http://php.net/gd">GD</a> v2 extension for the <a target="_blank" href="https://github.com/kometphp/image">Image</a> package.</td>
			<?php endif ?>
		</tr>
		<tr>
			<th>Memcache Enabled</th>
			<?php if (extension_loaded('memcache')): ?>
				<td class="pass">Pass</td>
			<?php else: ?>
				<td class="fail">KometPHP <a target="_blank" href="https://github.com/kometphp/cache">Cache</a> package requires this extension for <a target="_blank" href="http://php.net/memcache">Memcache</a> support.</td>
			<?php endif ?>
		</tr>
		<tr>
			<th>APC Enabled</th>
			<?php if (extension_loaded('apc')): ?>
				<td class="pass">Pass</td>
			<?php else: ?>
				<td class="fail">KometPHP <a target="_blank" href="https://github.com/kometphp/cache">Cache</a> package requires this extension for <a target="_blank" href="http://php.net/apc">APC</a> support.</td>
			<?php endif ?>
		</tr>
		<tr>
			<th>XCache Enabled</th>
			<?php if (extension_loaded('xcache')): ?>
				<td class="pass">Pass</td>
			<?php else: ?>
				<td class="fail">KometPHP <a target="_blank" href="https://github.com/kometphp/cache">Cache</a> package requires this extension for <a target="_blank" href="http://php.net/xcache">XCache</a> support.</td>
			<?php endif ?>
		</tr>
		<tr>
			<th>cURL Enabled</th>
			<?php if (extension_loaded('curl')): ?>
				<td class="pass">Pass</td>
			<?php else: ?>
				<td class="fail"><a target="_blank" href="http://php.net/curl">cURL</a> extension is useful for performing external requests from PHP.</td>
			<?php endif ?>
		</tr>-->
		<tr>
			<th>PDO Enabled</th>
			<?php if (class_exists('PDO')): ?>
				<td class="pass">Pass</td>
			<?php else: ?>
				<td class="fail">KometPHP <a target="_blank" href="https://github.com/kometphp/dbal">DBAL</a> package requires this extension for <a target="_blank" href="http://php.net/pdo">PDO</a> support.</td>
			<?php endif ?>
		</tr>
		<tr>
			<th>Mongo Enabled</th>
			<?php if (class_exists('Mongo')): ?>
				<td class="pass">Pass</td>
			<?php else: ?>
				<td class="fail">KometPHP <a target="_blank" href="https://github.com/kometphp/dbal">DBAL</a> package requires this extension for <a target="_blank" href="http://php.net/mongo">Mongo</a> support.</td>
			<?php endif ?>
		</tr>
	</table>

</body>
</html>