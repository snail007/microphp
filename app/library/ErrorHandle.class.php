<?php

/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		MicroPHP
 * @author		狂奔的蜗牛
 * @email		672308444@163.com
 * @copyright           Copyright (c) 2013 - 2013, 狂奔的蜗牛, Inc.
 * @link		http://git.oschina.net/snail/microphp
 * @since		Version 2.2.0
 * @createdtime         2013-11-14 23:03:21
 */
class ErrorHandle {

    public static function error_handle($errno, $errstr, $errfile, $errline, $strace) {
        print_r('log error ' . $errstr . "\n");
    }

    public static function exception_handle($errno, $errstr, $errfile, $errline, $strace) {
        print_r('log exception ' . $errstr . "\n");
    }

    public static function db_error_handle($errmsg, $strace) {
        print_r('log db error ' . $errmsg . "\n" . $strace);
    }

}
