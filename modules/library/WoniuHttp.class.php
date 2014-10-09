<?php

/*
 * Copyright 2014 pm.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * MicroPHP
 * 
 * An open source application development framework for PHP 5.2.26 or newer
 *
 * @package		MicroPHP
 * @author		狂奔的蜗牛
 * @email		672308444@163.com
 * @copyright	        Copyright (c) 2013 - 2014, 狂奔的蜗牛, Inc.
 * @link		http://git.oschina.net/snail/microphp
 * @createdtime        2014-10-09 10:05:45
 */
class WoniuHttp {

    private $ch, $last_url,
            $response_header, $response_body, $response_info,
            $default_agent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.117 Safari/537.36',
            $referer, $error = array('code' => 0, 'msg' => ''), $cookie_path

    ;

    public function __construct() {
        $this->ch = curl_init();
        $tmp = $this->getTempDir() . '/';
        $cookie_file_name = 'woniu_http_cookie' . md5(uniqid('', TRUE));
        $this->cookie_path = $cookie_path = $tmp . $cookie_file_name;
        curl_setopt($this->ch, CURLOPT_HEADER, 1);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->default_agent);
        curl_setopt($this->ch, CURLOPT_COOKIEJAR, $cookie_path);
        curl_setopt($this->ch, CURLOPT_COOKIEFILE, $cookie_path);
        eval('function ' . $cookie_file_name . '() {
            $path= "' . $cookie_path . '";
            if (file_exists($path)) {
                unlink($path);
            }
        }');
        register_shutdown_function($cookie_file_name);
    }

    /**
     * 使用GET方式请求一个页面
     * @param String  $url           页面地址
     * @param Array   $data          要发送的数据数组或者原始数据，比如：array('user'=>'test','pass'=>'354534'),键是表单字段名称，值是表单字段的值，默认 null
     * @param Array   $header        附加的HTTP头，比如：array('Connection:keep-alive','Cache-Control:max-age=0')，注意冒号前后不能有空格，默认 null
     * @param int     $max_redirect  遇到301或302时跳转的最大次数 ，默认 0 不跳转
     * @return String 页面数据
     */
    public function get($url, $data = null, Array $header = null, $max_redirect = 0) {
        return $this->request('get', $url, $data, $header, $max_redirect);
    }

    /**
     * 使用POST方式请求一个页面
     * @param String  $url           页面地址
     * @param Array   $data          要发送的数据数组，比如：array('user'=>'test','pass'=>'354534'),键是表单字段名称，值是表单字段的值，默认 null
     * @param Array   $header        附加的HTTP头，比如：array('Connection:keep-alive','Cache-Control:max-age=0')，注意冒号前后不能有空格，默认 null
     * @param int     $max_redirect  遇到301或302时跳转的最大次数 ，默认 0 不跳转
     * @return String 页面数据
     */
    public function post($url, $data = null, Array $header = null, $max_redirect = 0) {
        return $this->request('post', $url, $data, $header, $max_redirect);
    }

    /**
     * 发送一个http请求
     * @param type $type
     * @param type $url
     * @param type $data
     * @param type $header
     * @param type $redirect
     * @param type $max_redirect
     * @return int
     */
    private function request($type, $url, $data, $header = null, $max_redirect = 0) {
        $type = strtolower($type);
        $this->curl_max_loops = $max_redirect;
        if (!empty($header)) {
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $header);
        }
        if ($type == 'post') {
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array('Expect:'));
            curl_setopt($this->ch, CURLOPT_POST, 1);
            if (!empty($data)) {
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
            }
        } else {
            $imchar = '';
            if (!is_array($data)) {
                $data = array();
            }
            $_data = array();
            foreach ($data as $key => $value) {
                $_data[] = $key . '=' . urlencode($value);
            }
            if (!empty($_data)) {
                $imchar = stripos($url, '?') !== false ? '&' : '?';
                $imchar.=implode('&', $_data);
                $url.=$imchar;
            }
            curl_setopt($this->ch, CURLOPT_POST, 0);
        }
        curl_setopt($this->ch, CURLOPT_HEADER, 1);
        curl_setopt($this->ch, CURLOPT_URL, $url);
        $this->last_url = $url;
        $data = $this->curl_exec_follow($max_redirect);
        if (!$this->errorCode()) {
            list($this->response_header, $this->response_body) = explode("\r\n\r\n", $data, 2);
            $this->response_info = curl_getinfo($this->ch);
            $this->reset();
            $this->setError(0, '');
            return $this->response_body;
        } else {
            return 0;
        }
    }

    /**
     * 带有重定向功能的exec
     * @param type $redirect
     * @return boolean
     */
    private function curl_exec_follow($max_redirect) {
        $max_redirect = $max_redirect < 0 ? 0 : $max_redirect;
        if ($max_redirect == 0) {
            $this->_autoReferer();
            return curl_exec($this->ch);
        }
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, false);
        $loops = 0;
        do {
            $this->_autoReferer();
            $data = curl_exec($this->ch);
            $this->reset();
            if (!curl_errno($this->ch)) {
                list($this->response_header, $this->response_body) = explode("\r\n\r\n", $data, 2);
                $this->response_info = curl_getinfo($this->ch);
                if (!$this->isRedirect()) {
                    $this->setError(0, '');
                    return $data;
                } else {
                    preg_match('/Location:(.*?)$/mi', $this->response_header, $matches);
                    $this->last_url = $url = $this->parseLocation(trim(array_pop($matches)));
                    curl_setopt($this->ch, CURLOPT_URL, $url);
                }
            } else {
                $this->setError(curl_errno($this->ch), curl_error($this->ch));
                return 0;
            }
        } while (++$loops <= $max_redirect);
        $this->setError(1000, 'MAXREDIRS reached');
        return FALSE;
    }

    private function parseLocation($url) {
        $last_url = parse_url(curl_getinfo($this->ch, CURLINFO_EFFECTIVE_URL));
        $last_url = array_merge(array('scheme' => '', 'host' => '', 'path' => '', 'query' => ''), $last_url);
        if (preg_match('|^http(s)?://|i', $url)) {
            //http网址
            return $url;
        } else {
            //本站绝对路径网址
            if ($url{0} == '/') {
                return $last_url['scheme'] . '://' . $last_url['host'] . $url;
            } else {
                //本站相对路径网址
                return $last_url['scheme'] . '://' . $last_url['host'] . '/' . trim(dirname($last_url['path']), '/') . '/' . $url;
            }
        }
    }

    private function getTempDir() {
        if (!function_exists('sys_get_temp_dir')) {
            if (!empty($_ENV['TMP'])) {
                return realpath($_ENV['TMP']);
            }
            if (!empty($_ENV['TMPDIR'])) {
                return realpath($_ENV['TMPDIR']);
            }
            if (!empty($_ENV['TEMP'])) {
                return realpath($_ENV['TEMP']);
            }
            $tempfile = tempnam(uniqid(rand(), TRUE), '');
            if (file_exists($tempfile)) {
                unlink($tempfile);
                return realpath(dirname($tempfile));
            }
        } else {
            return sys_get_temp_dir();
        }
    }

    /**
     * 每次请求完成后，进行一些清理
     */
    private function reset() {
        $this->referer = null;
        curl_setopt($this->ch, CURLOPT_COOKIE, NULL);
    }

    /**
     * referer自动设置
     */
    private function _autoReferer() {
        if (empty($this->referer)) {
            if (empty($this->last_url)) {
                curl_setopt($this->ch, CURLOPT_AUTOREFERER, 1);
            } else {
                curl_setopt($this->ch, CURLOPT_REFERER, $this->last_url);
            }
        } else {
            curl_setopt($this->ch, CURLOPT_REFERER, $this->referer);
        }
    }

    /**
     * 设置当次请求使用的referer，当get或者post请求完毕后，referer会被重置为null
     * @param type $referer
     * @return WoniuHttp
     */
    public function setReferer($referer) {
        $this->referer = $referer;
        return $this;
    }

    /**
     * 获取curl出错信息，返回数组：形如array('code'=>1000,'msg'=>'xxx'),如果没有错误，code是0
     * @return array
     */
    public function error() {
        return $this->error;
    }

    /**
     * 获取curl出错代码（大于零的数），如果没有错误，返回0
     * @return int
     */
    public function errorCode() {
        return $this->error['code'];
    }

    /**
     * 获取curl出错字符串信息，如果没有错误，返回空
     * @return string
     */
    public function errorMsg() {
        return $this->error['msg'];
    }

    private function setError($error_code, $error_msg) {
        $this->error['code'] = $error_code;
        $this->error['msg'] = $error_msg;
        return $this;
    }

    public function setUserAgent($user_agent) {
        curl_setopt($this->ch, CURLOPT_USERAGENT, $user_agent);
        return $this;
    }

    /**
     * 获取请求返回的HTTP头部字符串
     * @return string
     */
    public function header() {
        return $this->response_header;
    }

    /**
     * 获取请求返回的页面内容
     * @param type $is_json  是否使用json_decode()解码一下,当返回数据是json的时候这个比较有用。默认false
     * @return string|array 
     */
    public function data($is_json = false) {
        return $is_json ? @json_decode($this->response_body, TRUE) : $this->response_body;
    }

    /**
     * 请求完成后，获取请求相关信息，就是curl_getinfo()返回的信息数组
     * @return array
     */
    public function info() {
        return $this->response_info;
    }

    /**
     * 请求完成后，获取返回的HTTP状态码
     * @return int
     */
    public function code() {
        return isset($this->response_info['http_code']) ? $this->response_info['http_code'] : 0;
    }

    /**
     * 请求完成后，响应是否是重定向
     * @return boolean
     */
    public function isRedirect() {
        return in_array($this->code(), array(301, 302));
    }

    /**
     * 请求完成后，响应是重定向的时候，这里会返回重定向的链接，如果不是重定向返回null
     * @return string
     */
    public function location() {
        if ($this->isRedirect()) {
            preg_match('/Location:(.*?)$/mi', $this->response_header, $matches);
            return $this->parseLocation(trim(array_pop($matches)));
        } else {
            return null;
        }
    }

    /**
     * 请求完成后，获取最后一次请求的地址，这个往往是有重定向的时候使用的。
     * @return string
     */
    public function lastUrl() {
        return $this->last_url;
    }

    /**
     * 设置curl句柄参数
     * @param type $opt     curl_setopt()的第二个参数
     * @param type $val     curl_setopt()的第三个参数
     * @return \WoniuHttp
     */
    public function setOpt($opt, $val) {
        curl_setopt($this->ch, $opt, $val);
        return $this;
    }

    /**
     * 设置附加的cookie，这个不会影响自动管理的cookie<br>
     * 提醒：<br>
     * 1.每次请求完成后附加的cookie会被清空，自动管理的cookie不会受到影响。<br>
     * 2.如果cookie键名和自动管理的cookie中键名相同，那么请求的时候使用的是这里设置的值。<br>
     * 3.如果cookie键名和自动管理的cookie中键名相同，当请求完成后自动管理的cookie中键的值保持之前的不变，这里设置的值会被清除。<br>
     * 比如：<br>
     * 自动管理cookie里面有：name=snail，请求之前用setCookie设置了name=123<br>
     * 那么请求的时候发送的cookie是name=123,请求完成后恢复name=snail，如果再次请求那么发送的cookie中name=snail<br>
     * 
     * @param type $key   cookie的key，也可以是一个键值对数组一次设置多个cookie，此时不需要第二个参数。
     * @param type $val   cookie的value
     * @return WoniuHttp
     */
    public function setCookie($key, $val = NULL) {
        $cookies = array();
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $cookies[] = $k . '=' . urlencode($v);
            }
        } else {
            $cookies[] = ' ' . $key . '=' . urlencode($val);
        }
        if (!empty($cookies)) {
            curl_setopt($this->ch, CURLOPT_COOKIE, implode(';', $cookies));
        }
        return $this;
    }

}
