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

    /**
     * hmvc模块名称，没有模块就为空
     * @return type
     */
    public static function module_name() {
        return self::$router['module'];
    }

    /**
     * url中方法的路径<br/>
     * 比如：<br>
     * 1.home.index<br>
     * 2.user.home.index ，user是文件夹<br>
     * @return type
     */
    public static function method_path() {
        return self::$router['mpath'];
    }

    /**
     * url中方法名称<br/>
     * 比如：<br>
     * 1.index<br>
     * @return type
     */
    public static function method_name() {
        return self::$router['m'];
    }

    /**
     * $system配置中方法前缀,比如：do
     * @return type
     */
    public static function method_prefix() {
        return self::$router['prefix'];
    }

    /**
     * url中控制器的路径<br/>
     * 比如：<br>
     * 1.home<br>
     * 2.user.home ，user是文件夹<br>
     * @return type
     */
    public static function controller_path() {
        return self::$router['cpath'];
    }

    /**
     * url中控制器名称<br/>
     * 比如：<br>
     * 1.home<br>
     * @return type
     */
    public static function controller_name() {
        return self::$router['c'];
    }

    /**
     * url中文件夹名称，没有文件夹返回空<br/>
     * 比如：<br/>
     * 1.user
     */
    public static function folder_name() {
        return self::$router['folder'];
    }

    /**
     * 请求的控制器文件绝对路径<br/>
     * 比如：/home/www/app/controllers/home.php<br/>
     * 
     */
    public static function controller_file() {
        return self::$router['file'];
    }

    /**
     * 请求的控制器类名称<br/>
     * 比如：Home
     */
    public static function class_name() {
        return self::$router['class'];
    }

    /**
     * 请求的控制器方法名称<br/>
     * 比如：doIndex
     */
    public static function class_method_name() {
        return self::$router['method'];
    }

    /**
     * 传递给控制器方法的所有参数的数组，参数为空时返回空数组<br/>
     * 比如：<br/>
     * 1.home.index/username/1234，那么返回的参数数组就是：array('username','1234')<br/>
     * 2.如果传递了$key,比如$key是1， 那么将返回1234。如果$key是2那么将返回null。
     * @param type $key 参数的索引从0开始，如果传递了索引那么将返回索引对应的参数,不存在的索引将返回null
     * @return null
     */
    public static function parameters($key = null) {
        if (!is_null($key)) {
            if (isset(self::$router['parameters'][$key])) {
                return self::$router['parameters'][$key];
            } else {
                return null;
            }
        } else {
            return self::$router['parameters'];
        }
    }

    private static function get_int_type($type, $key, $min = 1, $max = null, $default = null) {
        $val = null;
        switch ($type) {
            case 'get':
                $val = self::get($key);
                break;
            case 'post':
                $val = self::post($key);
                break;
            case 'get_post':
                $val = self::get_post($key);
                break;
            case 'post_get':
                $val = self::post_get($key);
                break;
        }
        if (!is_null($val) && $val == intval($val)) {
            if (is_null($max)) {
                return $val >= $min ? $val : $default;
            } else {
                return $val >= $min && $val <= $max ? $val : $default;
            }
        } else {
            return null;
        }
    }

    public static function get_int($key, $min = 1, $max = null, $default = null) {
        return self::get_int_type('get', $key, $min, $max, $default);
    }

    public static function post_int($key, $min = 1, $max = null, $default = null) {
        return self::get_int_type('post', $key, $min, $max, $default);
    }

    public static function get_post_int($key, $min = 1, $max = null, $default = null) {
        return self::get_int_type('get_post', $key, $min, $max, $default);
    }

    public static function post_get_int($key, $min = 1, $max = null, $default = null) {
        return self::get_int_type('post_get', $key, $min, $max, $default);
    }

    public static function get_post($key = null, $default = null, $xss_clean = false) {
        $get = self::gpcs('_GET', $key, $default);
        $val = $get === null ? self::gpcs('_POST', $key, $default) : $get;
        return $xss_clean ? self::xss_clean($val) : $val;
    }

    public static function post_get($key = null, $default = null, $xss_clean = false) {
        $get = self::gpcs('_POST', $key, $default);
        $val = $get === null ? self::gpcs('_GET', $key, $default) : $get;
        return $xss_clean ? self::xss_clean($val) : $val;
    }

    public static function get($key = null, $default = null, $xss_clean = false) {
        $val = self::gpcs('_GET', $key, $default);
        return $xss_clean ? self::xss_clean($val) : $val;
    }

    public static function post($key = null, $default = null, $xss_clean = false) {
        $val = self::gpcs('_POST', $key, $default);
        return $xss_clean ? self::xss_clean($val) : $val;
    }

    public static function cookie($key = null, $default = null, $xss_clean = false) {
        $val = self::gpcs('_COOKIE', $key, $default);
        return $xss_clean ? self::xss_clean($val) : $val;
    }

    public static function session($key = null, $default = null) {
        return self::gpcs('_SESSION', $key, $default);
    }

    public static function server($key = null, $default = null) {
        $key = !is_null($key) ? strtoupper($key) : null;
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

    public static function xss_clean($val) {
        // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
        // this prevents some character re-spacing such as <java\0script>
        // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
        $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);

        // straight replacements, the user should never need these since they're normal characters
        // this prevents like <IMG SRC=@avascript:alert('XSS')>
        $search = 'abcdefghijklmnopqrstuvwxyz';
        $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $search .= '1234567890!@#$%^&*()';
        $search .= '~`";:?+/={}[]-_|\'\\';
        for ($i = 0; $i < strlen($search); $i++) {
            // ;? matches the ;, which is optional
            // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
            // @ @ search for the hex values
            $val = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val); // with a ;
            // @ @ 0{0,7} matches '0' zero to seven times
            $val = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val); // with a ;
        }

        // now the only remaining whitespace attacks are \t, \n, and \r
        $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
        $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
        $ra = array_merge($ra1, $ra2);

        $found = true; // keep replacing as long as the previous round replaced something
        while ($found == true) {
            $val_before = $val;
            for ($i = 0; $i < sizeof($ra); $i++) {
                $pattern = '/';
                for ($j = 0; $j < strlen($ra[$i]); $j++) {
                    if ($j > 0) {
                        $pattern .= '(';
                        $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                        $pattern .= '|';
                        $pattern .= '|(&#0{0,8}([9|10|13]);)';
                        $pattern .= ')*';
                    }
                    $pattern .= $ra[$i][$j];
                }
                $pattern .= '/i';
                $replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2); // add in <> to nerf the tag
                $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
                if ($val_before == $val) {
                    // no replacements were made, so exit the loop
                    $found = false;
                }
            }
        }
        return $val;
    }

}

/* End of file WoniuInput.php */