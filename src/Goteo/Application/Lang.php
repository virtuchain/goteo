<?php

namespace Goteo\Application;

class Lang {
    static protected $default = '';

    static protected  $langs_available = array(
        'en' => array(
                    'name' => 'English',
                    'short' => 'ENG',
                    'public' => true,
                    'locale' => 'en_GB'
                    ),
        'es' => array(
                    'name' => 'Español',
                    'short' => 'ES',
                    'public' => true,
                    'locale' => 'es_ES'),
        'ca' => array(
                    'name' => 'Català',
                    'short' => 'CAT',
                    'public' => true,
                    'locale' => 'ca_ES',
                    'fallback' => 'es' //Overwrite fallback
                    ),
        'eu' => array(
                    'name' => 'Euskara',
                    'short' => 'EUSK',
                    'public' => true,
                    'locale' => 'eu_ES',
                    'fallback' => 'es'  //Overwrite fallback
                    ),
        'gl' => array(
                    'name' => 'Galego',
                    'short' => 'GAL',
                    'public' => true,
                    'locale' => 'gl_ES',
                    'fallback' => 'es'  //Overwrite fallback
                    ),
        'fr' => array(
                    'name' => 'Français',
                    'short' => 'FRA',
                    'public' => true,
                    'locale' => 'fr_FR'),
        'it' => array(
                    'name' => 'Italiano',
                    'short' => 'ITA',
                    'public' => true,
                    'locale' => 'it_IT',
                    'fallback' => 'es'  //Overwrite fallback
                    ),
        'nl' => array(
                    'name' => 'Dutch',
                    'short' => 'NL',
                    'public' => true,
                    'locale' => 'nl_NL'
                    ),
        'de' => array(
                    'name' => 'Deutsch',
                    'short' => 'N',
                    'public' => false,
                    'locale' => 'N'),
        'el' => array(
                    'name' => 'Ελληνικά',
                    'short' => 'GRK',
                    'public' => false,
                    'locale' => 'el_GR'),
        'pl' => array(
                    'name' => 'Polski',
                    'short' => 'POL',
                    'public' => false,
                    'locale' => 'pl_PL'),
        'pt' => array(
                    'name' => 'Português',
                    'short' => 'PORT',
                    'public' => false,
                    'locale' => 'pt_PT'
                    ),
    );

    // TODO: method to override by config??


    /**
     * Sets the default language
     * @param [type] $lang [description]
     */
    static public function setDefault($lang) {
        if(self::exists($lang)) {
            self::$default = $lang;
        }
    }
    /**
     * Sets the default language
     * @param [type] $lang [description]
     */
    static public function setPublic($lang, $public = true) {
        if(self::exists($lang)) {
            self::$langs_available[$lang]['public'] = (bool) $public;
        }
    }

    static public function isPublic($lang) {
        return self::get($lang, 'public');
    }

    /**
     * Returns the default language for a language
     * @param  string $lang [description]
     * @return [type]       [description]
     */
    static public function getDefault($lang = '') {
        $default = self::isPublic(self::$default) ? self::$default : '';

        foreach(self::$langs_available as $l => $info) {
            if($info['public']) {
                if(empty($default)) {
                    $default = $l;
                }
                if($lang === $l) {
                    $fallback = self::$langs_available[$lang]['fallback'];
                    if($fallback && self::isPublic($fallback)) {
                        $default = $fallback;
                    }
                    break;
                }
            }
        }
        return $default;
    }

    /**
     * set the system lang
     * @param [type] $lang [description]
     */
    static public function set($lang) {
        if(!self::isPublic($lang)) {
            // get the default
            $lang = self::getDefault($lang);
        }

        return Session::store('lang', $lang);
    }

    /**
     * Gets the current active language
     * @return [type] [description]
     */
    static public function current() {
        $current = '';
        if(Session::exists('lang')) {
            $current = Session::get('lang');
        }
        if(empty($current) || !self::isPublic($current)) {
            $current = self::getDefault();
        }
        return $current;
    }
    /**
     * Get the a language
     * @return [type] [description]
     */
    static public function get($lang, $method = 'object') {
        if(self::exists($lang)) {

            $info = self::$langs_available[$lang];

            if($method === 'id')  return $lang;
            elseif($method === 'name' && $info['name'])       return $info['name'];
            elseif($method === 'short' && $info['short'])  return $info['short'];
            elseif($method === 'locale' && $info['locale'])  return $info['locale'];
            elseif($method === 'public')  return (bool)$info['public'];
            elseif($method === 'array')  return $info;
            elseif($method === 'object') return (object) $info;

            return $lang;
        }
        return false;
    }

    static public function exists($lang) {
        return array_key_exists($lang, self::$langs_available);
    }
    /**
     * Returns if the lang is currently selected
     * @param  [type]  $lang [description]
     * @return boolean       [description]
     */
    static public function isActive($lang) {
        return self::current() === $lang;
    }

    static public function setFromGlobals() {
        // self::setDefault('es');

        $desired = array();
        // set Lang (forzado para el cron y el admin)
        $uri = strtok($_SERVER['REQUEST_URI'], '?');
        $desired[] = (strpos($uri, 'cron') !== false || strpos($uri, 'admin') !== false) ? 'es' : null;
        // set Lang by GET user request
        if(isset($_GET['lang'])) {
            $desired[] = $_GET['lang'];
        }
        // set lang by cookie if exists
        if(Cookie::exists('goteo_lang')) {
            $desired[] = Cookie::get('goteo_lang');
        }
        // set by navigator
        $desired[] = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

        // set the lang in order of preference
        foreach($desired as $l) {
            $lang = self::set($l);
            if($lang === $l) {
                break;
            }
        }

        //Si el idioma existe (y se ha especificado), guardar preferencias
        if($lang === $_GET['lang']) {
            //Enviar cookie
            Cookie::store('goteo_lang', $lang);
            if(Session::isLogged()) {
                //guardar preferencias de usuario
                Session::getUser()->updateLang($lang);
            }
        }

        // establecemos la constante
        // TODO: por desaparecer
        // usar Lang::get() en su lugar
        define('LANG', $lang);

        // cambiamos el locale
        setlocale(LC_TIME, self::getLocale($lang));

        return $lang;
    }

    /**
     * Retrieve the locale value for a lang
     * @param  [type] $lang [description]
     * @return [type]       [description]
     */
    static function getLocale($lang) {
        return self::get($lang ? $lang : self::current(), 'locale');
    }

    /**
     * Retrieve the name value for a lang
     * @param  [type] $lang [description]
     * @return [type]       [description]
     */
    static function getName($lang) {
        return self::get($lang ? $lang : self::current(), 'name');
    }

    /**
     * Retrieve the short name value for a lang
     * @param  [type] $lang [description]
     * @return [type]       [description]
     */
    static function getShort($lang = null) {
        return self::get($lang ? $lang : self::current(), 'short');
    }


    /**
     * Returns an array of langs => lang-name
     * @return [type] [description]
     */
    static function listAll($method = 'name', $public_only = true) {
        $ret = array();
        foreach(self::$langs_available as $lang => $info) {

            if(empty($info['public']) && $public_only) continue;

            $ret[$lang] = self::get($lang, $method);
        }
        return $ret;
    }
}
