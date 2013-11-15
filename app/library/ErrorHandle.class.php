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

    private static $array_map = array('0' => 'EXCEPTION', '1' => 'ERROR', '2' => 'WARNING', '4' => 'PARSE', '8' => 'NOTICE', '16' => 'CORE_ERROR', '32' => 'CORE_WARNING', '64' => 'COMPILE_ERROR', '128' => 'COMPILE_WARNING', '256' => 'USER_ERROR', '512' => 'USER_WARNING', '1024' => 'USER_NOTICE', '2048' => 'STRICT', '4096' => 'RECOVERABLE_ERROR', '8192' => 'DEPRECATED', '16384' => 'USER_DEPRECATED');

    public static function getPath() {
        return WoniuLoader::$system['application_folder'] . '/cache/error.log';
    }
    public static function getUrl() {
        return 'http://'.$_SERVER['HTTP_HOST'].'/'.$_SERVER['REQUEST_URI'];
    }
    public static function error_handle($errno, $errstr, $errfile, $errline, $strace) {
        self::log(self::$array_map[$errno].":{$errstr}\n File:{$errfile}\nLine:{$errline}\n{$strace}");
    }

    public static function exception_handle($errno, $errstr, $errfile, $errline, $strace) {
        self::log(self::$array_map[$errno].":{$errstr}\n File:{$errfile}\nLine:{$errline}\n{$strace}");
    }

    public static function db_error_handle($errmsg, $strace) {
        self::log("db error :{$errmsg}\n{$strace}");
    }

    public static function fixContent($content) {
        $content= 'url:'.self::getUrl()."\n".$content;
        return date('Y-m-d H:i:s') . ' ' . $content . "\n";
    }

    public static function log($content) {
        $filename = self::getPath();
        file_put_contents($filename, $content, FILE_APPEND | LOCK_EX);
    }

}
