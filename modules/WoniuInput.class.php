<?php

/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.2.0 or newer
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
     * 系统最终使用的路由字符串
     * @return type
     */
    public static function route_query() {
        return self::$router['query'];
    }

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
     * 传递给控制器方法的所有参数的数组，参数为空时返回参数数组<br/>
     * 比如：<br/>
     * 1.home.index/username/1234，那么返回的参数数组就是：array('username','1234')<br/>
     * 2.如果传递了$key,比如$key是1， 那么将返回1234。如果$key是2那么将返回null。<br/>
     * @param type $key 参数的索引从0开始，如果传递了索引那么将返回索引对应的参数,不存在的索引将返回null<br/>
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

    private static function get_x_type($rule, $method, $key) {
        $val = null;
        switch ($method) {
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
        if (is_null(WoniuLoader::checkData($rule, array('check' => $val)))) {
            return $val;
        } else {
            return null;
        }
    }

    private static function get_rule_type($rule, $method, $key, $default = null) {
        if (!is_array($rule)) {
            $_rule = array($rule => 'err');
        } else {
            $_rule = array();
            foreach ($rule as $r) {
                $_rule[$r] = 'err';
            }
        }
        $rule = array('check' => $_rule);
        $val = self::get_x_type($rule, $method, $key);
        return is_null($val) ? $default : $val;
    }

    /**
     * 根据验证规则和键获取一个值
     * @param type $rule    表单验证规则.示例：1.int 2. array('int','range[1,10]')
     * @param type $key     键
     * @param type $default 默认值。格式错误或者验证不通过，返回默认值。
     * @return type
     */
    public static function get_rule($rule, $key, $default = null) {
        return self::get_rule_type($rule, 'get', $key, $default);
    }

    /**
     * 根据验证规则和键获取一个值
     * @param type $rule    表单验证规则.示例：1.int 2. array('int','range[1,10]')
     * @param type $key     键
     * @param type $default 默认值。格式错误或者验证不通过，返回默认值。
     * @return type
     */
    public static function post_rule($rule, $key, $default = null) {
        return self::get_rule_type($rule, 'post', $key, $default);
    }

    /**
     * 根据验证规则和键获取一个值
     * @param type $rule    表单验证规则.示例：1.int 2. array('int','range[1,10]')
     * @param type $key     键
     * @param type $default 默认值。格式错误或者验证不通过，返回默认值。
     * @return type
     */
    public static function get_post_rule($rule, $key, $default = null) {
        return self::get_rule_type($rule, 'get_post', $key, $default);
    }

    /**
     * 根据验证规则和键获取一个值
     * @param type $rule    表单验证规则.示例：1.int 2. array('int','range[1,10]')
     * @param type $key     键
     * @param type $default 默认值。格式错误或者验证不通过，返回默认值。
     * @return type
     */
    public static function post_get_rule($rule, $key, $default = null) {
        return self::get_rule_type($rule, 'post_get', $key, $default);
    }

    private static function get_int_type($method, $key, $min = null, $max = null, $default = null) {
        $val = self::get_rule_type('int', $method, $key);
        $min_okay = is_null($min) || (!is_null($min) && $val >= $min);
        $max_okay = is_null($max) || (!is_null($max) && $val <= $max);
        return $min_okay && $max_okay && !is_null($val) ? $val : $default;
    }

    /**
     * 获取一个整数
     * @param type $key     键
     * @param type $min     最小值，为null不限制
     * @param type $max     最大值，为null不限制
     * @param type $default 默认值。格式错误或者不在范围，返回默认值
     * @return type
     */
    public static function get_int($key, $min = null, $max = null, $default = null) {
        return self::get_int_type('get', $key, $min, $max, $default);
    }

    /**
     * 获取一个整数
     * @param type $key     键
     * @param type $min     最小值，为null不限制
     * @param type $max     最大值，为null不限制
     * @param type $default 默认值。格式错误或者不在范围，返回默认值
     * @return type
     */
    public static function post_int($key, $min = null, $max = null, $default = null) {
        return self::get_int_type('post', $key, $min, $max, $default);
    }

    /**
     * 获取一个整数
     * @param type $key     键
     * @param type $min     最小值，为null不限制
     * @param type $max     最大值，为null不限制
     * @param type $default 默认值。格式错误或者不在范围，返回默认值
     * @return type
     */
    public static function get_post_int($key, $min = null, $max = null, $default = null) {
        return self::get_int_type('get_post', $key, $min, $max, $default);
    }

    /**
     * 获取一个整数
     * @param type $key     键
     * @param type $min     最小值，为null不限制
     * @param type $max     最大值，为null不限制
     * @param type $default 默认值。格式错误或者不在范围，返回默认值
     * @return type
     */
    public static function post_get_int($key, $min = null, $max = null, $default = null) {
        return self::get_int_type('post_get', $key, $min, $max, $default);
    }

    private static function get_date_type($method, $key, $min = null, $max = null, $default = null) {
        $val = self::get_rule_type('date', $method, $key);
        $min_okay = is_null($min) || (!is_null($min) && strtotime($val) >= strtotime($min));
        $max_okay = is_null($max) || (!is_null($max) && strtotime($val) <= strtotime($max));
        return $min_okay && $max_okay && !is_null($val) ? $val : $default;
    }

    /**
     * 获取日期，格式:2012-12-12
     * @param type $key  键
     * @param type $min  最小日期，格式:2012-12-12。为null不限制
     * @param type $max  最大日期，格式:2012-12-12。为null不限制
     * @param type $default 默认日期。格式错误或者不在范围，返回默认日期
     * @return type
     */
    public static function get_date($key, $min = null, $max = null, $default = null) {
        return self::get_date_type('get', $key, $min, $max, $default);
    }

    /**
     * 获取日期，格式:2012-12-12
     * @param type $key  键
     * @param type $min  最小日期，格式:2012-12-12。为null不限制
     * @param type $max  最大日期，格式:2012-12-12。为null不限制
     * @param type $default 默认日期。格式错误或者不在范围，返回默认日期
     * @return type
     */
    public static function post_date($key, $min = null, $max = null, $default = null) {
        return self::get_date_type('post', $key, $min, $max, $default);
    }

    /**
     * 获取日期，格式:2012-12-12
     * @param type $key  键
     * @param type $min  最小日期，格式:2012-12-12。为null不限制
     * @param type $max  最大日期，格式:2012-12-12。为null不限制
     * @param type $default 默认日期。格式错误或者不在范围，返回默认日期
     * @return type
     */
    public static function get_post_date($key, $min = null, $max = null, $default = null) {
        return self::get_date_type('get_post', $key, $min, $max, $default);
    }

    /**
     * 获取日期，格式:2012-12-12
     * @param type $key  键
     * @param type $min  最小日期，格式:2012-12-12。为null不限制
     * @param type $max  最大日期，格式:2012-12-12。为null不限制
     * @param type $default 默认日期。格式错误或者不在范围，返回默认日期
     * @return type
     */
    public static function post_get_date($key, $min = null, $max = null, $default = null) {
        return self::get_date_type('post_get', $key, $min, $max, $default);
    }

    private static function get_time_type($method, $key, $min = null, $max = null, $default = null) {
        $val = self::get_rule_type('time', $method, $key);
        $pre_fix = '2014-01-01 ';
        $min_okay = is_null($min) || (!is_null($min) && strtotime($pre_fix . $val) >= strtotime($pre_fix . $min));
        $max_okay = is_null($max) || (!is_null($max) && strtotime($pre_fix . $val) <= strtotime($pre_fix . $max));
        return $min_okay && $max_okay && !is_null($val) ? $val : $default;
    }

    /**
     * 获取时间，格式:15:01:55
     * @param type $key  键
     * @param type $min  最小时间，格式:15:01:55。为null不限制
     * @param type $max  最大时间，格式:15:01:55。为null不限制
     * @param type $default 默认值。格式错误或者不在范围，返回默认值
     * @return type
     */
    public static function get_time($key, $min = null, $max = null, $default = null) {
        return self::get_time_type('get', $key, $min, $max, $default);
    }

    /**
     * 获取时间，格式:15:01:55
     * @param type $key  键
     * @param type $min  最小时间，格式:15:01:55。为null不限制
     * @param type $max  最大时间，格式:15:01:55。为null不限制
     * @param type $default 默认值。格式错误或者不在范围，返回默认值
     * @return type
     */
    public static function post_time($key, $min = null, $max = null, $default = null) {
        return self::get_time_type('post', $key, $min, $max, $default);
    }

    /**
     * 获取时间，格式:15:01:55
     * @param type $key  键
     * @param type $min  最小时间，格式:15:01:55。为null不限制
     * @param type $max  最大时间，格式:15:01:55。为null不限制
     * @param type $default 默认值。格式错误或者不在范围，返回默认值
     * @return type
     */
    public static function get_post_time($key, $min = null, $max = null, $default = null) {
        return self::get_time_type('get_post', $key, $min, $max, $default);
    }

    /**
     * 获取时间，格式:15:01:55
     * @param type $key  键
     * @param type $min  最小时间，格式:15:01:55。为null不限制
     * @param type $max  最大时间，格式:15:01:55。为null不限制
     * @param type $default 默认值。格式错误或者不在范围，返回默认值
     * @return type
     */
    public static function post_get_time($key, $min = null, $max = null, $default = null) {
        return self::get_time_type('post_get', $key, $min, $max, $default);
    }

    private static function get_datetime_type($method, $key, $min = null, $max = null, $default = null) {
        $val = self::get_rule_type('datetime', $method, $key);
        $min_okay = is_null($min) || (!is_null($min) && strtotime($val) >= strtotime($min));
        $max_okay = is_null($max) || (!is_null($max) && strtotime($val) <= strtotime($max));
        return $min_okay && $max_okay && !is_null($val) ? $val : $default;
    }

    /**
     * 获取日期时间，格式:2012-12-12 15:01:55
     * @param type $key  键
     * @param type $min  最小日期时间，格式:2012-12-12 15:01:55。为null不限制
     * @param type $max  最大日期时间，格式:2012-12-12 15:01:55。为null不限制
     * @param type $default 默认值。格式错误或者不在范围，返回默认值
     * @return type
     */
    public static function get_datetime($key, $min = null, $max = null, $default = null) {
        return self::get_datetime_type('get', $key, $min, $max, $default);
    }

    /**
     * 获取日期时间，格式:2012-12-12 15:01:55
     * @param type $key  键
     * @param type $min  最小日期时间，格式:2012-12-12 15:01:55。为null不限制
     * @param type $max  最大日期时间，格式:2012-12-12 15:01:55。为null不限制
     * @param type $default 默认值。格式错误或者不在范围，返回默认值
     * @return type
     */
    public static function post_datetime($key, $min = null, $max = null, $default = null) {
        return self::get_datetime_type('post', $key, $min, $max, $default);
    }

    /**
     * 获取日期时间，格式:2012-12-12 15:01:55
     * @param type $key  键
     * @param type $min  最小日期时间，格式:2012-12-12 15:01:55。为null不限制
     * @param type $max  最大日期时间，格式:2012-12-12 15:01:55。为null不限制
     * @param type $default 默认值。格式错误或者不在范围，返回默认值
     * @return type
     */
    public static function get_post_datetime($key, $min = null, $max = null, $default = null) {
        return self::get_datetime_type('get_post', $key, $min, $max, $default);
    }

    /**
     * 获取日期时间，格式:2012-12-12 15:01:55
     * @param type $key  键
     * @param type $min  最小日期时间，格式:2012-12-12 15:01:55。为null不限制
     * @param type $max  最大日期时间，格式:2012-12-12 15:01:55。为null不限制
     * @param type $default 默认值。格式错误或者不在范围，返回默认值
     * @return type
     */
    public static function post_get_datetime($key, $min = null, $max = null, $default = null) {
        return self::get_datetime_type('post_get', $key, $min, $max, $default);
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

    /**
     * 获取一个cookie
     * 提醒:
     * 该方法会在key前面加上系统配置里面的$system['cookie_key_prefix']
     * 如果想不加前缀，获取原始key的cookie，可以使用方法：$this->input->cookie_raw();
     * @param string $key      cookie键
     * @param type $default    默认值
     * @param type $xss_clean  xss过滤
     * @return type
     */
    public static function cookie($key = null, $default = null, $xss_clean = false) {
        $key = systemInfo('cookie_key_prefix') . $key;
        return self::cookieRaw($key, $default, $xss_clean);
    }

    /**
     * 获取一个cookie
     * @param string $key      cookie键
     * @param type $default    默认值
     * @param type $xss_clean  xss过滤
     * @return type
     */
    public static function cookieRaw($key = null, $default = null, $xss_clean = false) {
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

    public static function is_cli() {
        return self::isCli();
    }

    public static function is_ajax() {
        return (self::server('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest');
    }

    public static function xss_clean($var) {
        if (is_array($var)) {
            foreach ($var as $key => $val) {
                if (is_array($val)) {
                    $var[$key] = self::xss_clean($val);
                } else {
                    $var[$key] = self::xss_clean0($val);
                }
            }
        } elseif (is_string($var)) {
            $var = self::xss_clean0($var);
        }
        return $var;
    }

    private static function xss_clean0($data) {
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
            $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|iframe|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        } while ($old_data !== $data);

        // we are done...
        return $data;
    }

}

/* End of file WoniuInput.php */