<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package                MicroPHP
 * @author                狂奔的蜗牛
 * @email                672308444@163.com
 * @copyright          Copyright (c) 2013 - 2013, 狂奔的蜗牛, Inc.
 * @link                http://git.oschina.net/snail/microphp
 * @since                Version 1.0
 * @createdtime       {createdtime}
 */
class WoniuInput {

    public static $router;

    public static function get_post($key = null, $default = null,$xss_clean=false) {
        $get = self::gpcs('_GET', $key, $default);
        $val= $get === null ? self::gpcs('_POST', $key, $default) : $get;
        return $xss_clean?self::xss_clean($val):$val;
    }

    public static function get($key = null, $default = null,$xss_clean=false) {
        $val=self::gpcs('_GET', $key, $default);
        return $xss_clean?self::xss_clean($val):$val;
    }

    public static function post($key = null, $default = null,$xss_clean=false) {
        $val= self::gpcs('_POST', $key, $default);
        return $xss_clean?self::xss_clean($val):$val;
    }

    public static function cookie($key = null, $default = null,$xss_clean=false) {
        $val= self::gpcs('_COOKIE', $key, $default);
        return $xss_clean?self::xss_clean($val):$val;
    }

    public static function session($key = null, $default = null) {
        return self::gpcs('_SESSION', $key, $default);
    }

    public static function server($key = null, $default = null) {
        $key = strtoupper($key);
        return self::gpcs('_SERVER', $key, $default);
    }

    private static function gpcs($range, $key, $default) {
        global $$range;
        if ($key === null) {
            return $$range;
        } else {
            $range = $$range;
            return isset($range[$key]) ? $range[$key] : ( $default !== null ? $default : null);
        }
    }

    public static function isCli() {
        return php_sapi_name() == 'cli';
    }

    public static function is_ajax() {
        return (self::server('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest');
    }

    /*
     * XSS filter 
     *
     * It was tested against *most* exploits here: http://ha.ckers.org/xss.html
     * WARNING: Some weren't tested!!!
     *
     */

    public static function xss_clean($data) {
        // Fix &entity\n;
        $data = str_replace(array('&amp;', '&lt;', '&gt;'), array('&amp;amp;', '&amp;lt;', '&amp;gt;'), $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

        do {
            // Remove really unwanted tags
            $old_data = $data;
            $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        } while ($old_data !== $data);

        // we are done...
        return $data;
    }

}

/* End of file WoniuInput.php */