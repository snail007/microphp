<?php

/*
 * khoaofgod@yahoo.com
 * Website: http://www.phpfastcache.com
 * Example at our website, any bugs, problems, please visit http://www.codehelper.io
 */

class phpfastcache_redis extends phpFastCache implements phpfastcache_driver {

    var $instant;

    function checkdriver() {
        // Check memcache
        if (class_exists("redis")) {
            return true;
        }
        return false;
    }

    function __construct($option = array()) {
        $this->setOption($option);
        if (!$this->checkdriver() && !isset($option['skipError'])) {
            throw new Exception("Can't use this driver for your website!");
        }
        if ($this->checkdriver() && !is_object($this->instant)) {
            $this->instant = new Redis();
        }
    }

    function connectServer() {
        $config = $this->option['redis'];
        $this->instant = new Redis();
        if ($config['type'] == 'sock') {
            $this->instant->connect($config['sock']);
        } else {
            $this->instant->connect($config['host'], $config['port'], $config['timeout'], NULL, $config['retry']);
        }
        if (!is_null($config['password'])) {
            $this->instant->auth($config['password']);
        }
        if (!is_null($config['prefix'])) {
            if ($config['prefix']{strlen($config['prefix']) - 1} != ':') {
                $config['prefix'].=':';
            }
            $this->instant->setOption(Redis::OPT_PREFIX, $config['prefix']);
        }
    }

    function driver_set($keyword, $value = "", $time = 300, $option = array()) {
        $this->connectServer();
        $value = serialize($value);
        return ($time) ? $this->instant->setex($keyword, $time, $value) : $this->instant->set($keyword, $value);
    }

    function driver_get($keyword, $option = array()) {
        $this->connectServer();
        // return null if no caching
        // return value if in caching
        if (($data = $this->instant->get($keyword))) {
            return @unserialize($data);
        } else {
            return null;
        }
    }

    function driver_delete($keyword, $option = array()) {
        $this->connectServer();
        $this->instant->delete($keyword);
    }

    function driver_stats($option = array()) {
        $this->connectServer();
        $res = array(
            "info" => "",
            "size" => "",
            "data" => $this->instant->info(),
        );

        return $res;
    }

    function driver_clean($option = array()) {
        $this->connectServer();
        $this->instant->flushDB();
    }

    function driver_isExisting($keyword) {
        $this->connectServer();
        return $this->instant->exists($keyword);
    }

}
