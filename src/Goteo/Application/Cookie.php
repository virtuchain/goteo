<?php

namespace Goteo\Application;

class Cookie {

    const DEFAULT_TTL = 31536000; // 3600 * 24 * 365;
    static protected $path = '/';

    static function setPath($path) {
        self::$path = $path;
    }

    /**
     * Stores some value in cookie
     * @param  [type] $key   [description]
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    static public function store($key, $value, $ttl = null) {
        global $_COOKIE;
        $ttl = (int) $ttl;
        if(empty($ttl)) $ttl = self::DEFAULT_TTL;
        if (PHP_SAPI !== 'cli') {
            setcookie($key, $value, time() + $ttl, self::$path);
        }
        return $_COOKIE[$key] = $value;
    }

    /**
     * Retrieve some value in cookie
     * @param  [type] $key [description]
     * @return [type]      [description]
     */
    static public function get($key) {
        global $_COOKIE;
        return $_COOKIE[$key];
    }

    /**
     * Delete some value in cookie
     * @param  [type] $key [description]
     * @return [type]      [description]
     */
    static public function del($key) {
        global $_COOKIE;
        unset($_COOKIE[$key]);
        if (PHP_SAPI !== 'cli') {
            setcookie($key, '', time() - 3600, self::$path);
        }
        return !self::exists($key);
    }

    /**
     * Check if a value exists in cookie
     * @param  [type] $key [description]
     * @return [type]      [description]
     */
    static public function exists($key) {
        global $_COOKIE;
        return is_array($_COOKIE) && array_key_exists($key, $_COOKIE);
    }

}
