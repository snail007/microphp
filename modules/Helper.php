<?php
/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		MicroPHP
 * @author		狂奔的蜗牛
 * @email		672308444@163.com
 * @copyright	        Copyright (c) 2008 - 2013, 狂奔的蜗牛, Inc.
 * @link		https://bitbucket.org/snail/microphp/
 * @since		Version 1.0
 * @filesource
 */
function trigger404($msg = '<h1>Not Found</h1>') {
    global $system;
    header('HTTP/1.1 404 NotFound');
    if (!empty($system['error_page_404']) && file_exists($system['error_page_404'])) {
        include $system['error_page_404'];
    } else {
        echo $msg;
    }
}

function stripslashes_all() {
    if (!get_magic_quotes_gpc()) {
        return;
    }
    $strip_list = array('_GET', '_POST', '_COOKIE');
    foreach ($strip_list as $val) {
        $$val = stripslashes2($val);
    }
}

#过滤魔法转义，参数可以是字符串或者数组，支持嵌套数组

function stripslashes2($var) {
    if (!get_magic_quotes_gpc()) {
        return $var;
    }
    if (is_array($var)) {
        foreach ($var as $key => $val) {
            if (is_array($val)) {
                $var[$key] = stripslashes2($val);
            } else {
                $var[$key] = stripslashes($val);
            }
        }
    } elseif (is_string($var)) {
        $var = stripslashes($var);
    }
    return $var;
}
/* End of file Helper.php */
 