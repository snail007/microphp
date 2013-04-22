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
 * @package		MicroPHP
 * @author		狂奔的蜗牛
 * @email		672308444@163.com
 * @copyright	        Copyright (c) 2013 - 2013, 狂奔的蜗牛, Inc.
 * @link		https://bitbucket.org/snail/microphp/
 * @since		Version 1.0
 * @createdtime       {createdtime}
 */
class WoniuInput {

    public  function get($key = null, $default = null) {
        return self::gpcs('_GET', $key, $default);
    }

    public  function post($key = null, $default = null) {
        return self::gpcs('_POST', $key, $default);
    }

    public  function cookie($key = null, $default = null) {
        return self::gpcs('_COOKIE', $key, $default);
    }

    public  function session($key = null, $default = null) {
        return self::gpcs('_SESSION', $key, $default);
    }

    public  function server($key = null, $default = null) {
        $key = strtoupper($key);
        return self::gpcs('_SERVER', $key, $default);
    }

    private  function gpcs($range, $key, $default) {
        global $$range;
        if ($key === null) {
            return $$range;
        } else {
            $range = $$range;
            return isset($range[$key]) ? $range[$key] : ( $default !== null ? $default : null);
        }
    }

}

/* End of file WoniuInput.php */
