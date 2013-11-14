<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TestLibrary
 *
 * @author Administrator
 */
class ErrorHandle {
    public static function error_handle($errno, $errstr, $errfile, $errline,$strace) {
        print_r('log error '.$errstr."\n");
    }
    public static function exception_handle($errno, $errstr, $errfile, $errline,$strace) {
        print_r('log exception '.$errstr."\n");
    }
    public static function db_error_handle($errmsg,$strace) {
        //print_r('log db error '.$errmsg."\n".$strace);
    }
}
 