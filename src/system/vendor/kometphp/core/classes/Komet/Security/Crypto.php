<?php

namespace Komet\Security;

/**
 * Crypto utilities
 * 
 * @package Komet/Security
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */
class Crypto {

    /**
     * Encrypts an string using MCRYPT_RIJNDAEL_256 and MCRYPT_MODE_ECB
     * 
     * @param string $text The RAW string
     * @param string $key The key with which the data will be encrypted.
     * @return string|false The encrypted and base64-safe-encoded string (safe for urls)
     */
    public static function encrypt($text, $key = null) {
        if (empty($text)) {
            return false;
        }
        if (empty($key))
            $key = config("salt");

        return \Komet\Format::base64Encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $text, MCRYPT_MODE_CBC, md5(md5($key))), true);
    }

    /**
     * Decrypts an string previously encrypted using crypto_encrypt
     * 
     * @param string $encrypted The RAW encrypted string
     * @param string $salt The key with which the data was encrypted.
     * @return string|false The decrypted string 
     */
    public static function decrypt($encrypted, $key = null) {
        if (empty($encrypted)) {
            return false;
        }
        if (empty($key))
            $key = config("salt");
        return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), \Komet\Format::base64Decode($encrypted, true), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
    }

    /**
     * Generate a keyed hash value using the HMAC method
     * @link http://php.net/manual/en/function.hash-hmac.php
     * @param string $algo <p>
     * Name of selected hashing algorithm (i.e. "md5", "sha256", "haval160,4", etc..) See <b>hash_algos</b> for a list of supported algorithms.
     * </p>
     * @param string $data <p>
     * Message to be hashed.
     * </p>
     * @param string $key <p>
     * Shared secret key used for generating the HMAC variant of the message digest.
     * </p>
     * @param bool $raw_output [optional] <p>
     * When set to true, outputs raw binary data.
     * false outputs lowercase hexits.
     * </p>
     * @return string a string containing the calculated message digest as lowercase hexits
     * unless <i>raw_output</i> is set to true in which case the raw
     * binary representation of the message digest is returned.
     */
    public static function hmac($algo, $data, $key, $raw_output = false) {
        if (function_exists("hash_hmac")) {
            return hash_hmac($algo, $data, $key, $raw_output);
        } else {
            $blocksize = 64;
            if (strlen($key) > $blocksize)
                $key = pack('H*', $algo($key));

            $key = str_pad($key, $blocksize, chr(0x00));
            $ipad = str_repeat(chr(0x36), $blocksize);
            $opad = str_repeat(chr(0x5c), $blocksize);
            $hmac = pack('H*', $algo(($key ^ $opad) . pack('H*', $algo(($key ^ $ipad) . $data))));

            return $raw_output ? $hmac : bin2hex($hmac);
        }
    }

    /**
     * Generate a hash value using the sha256 algorithm
     * 
     * @param string $data Message to be hashed.
     * @param boolean $raw_output
     * @return string 
     */
    public static function sha256($data, $raw_output = false) {
        return hash("sha256", $data, $raw_output);
    }

    /**
     * Generate a hash value using the sha512 algorithm
     * 
     * @param type $data Message to be hashed.
     * @param boolean $raw_output
     * @return string 
     */
    public static function sha512($data, $raw_output = false) {
        return hash("sha512", $data, $raw_output);
    }

}