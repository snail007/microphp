<?php

// main class
class phpFastCache {

    var $drivers = array('apc', 'files', 'sqlite', 'memcached', 'redis', 'wincache', 'xcache', 'memcache');
    private static $intances = array();
    public static $storage = "auto";
    public static $config = array(
        "storage" => "auto",
        "fallback" => array(
        ),
        "securityKey" => "",
        "htaccess" => false,
        "path" => "",
        "server" => array(
            array("127.0.0.1", 11211, 1),
        ),
        "extensions" => array(),
    );
    var $tmp = array();
    var $checked = array(
        "path" => false,
        "fallback" => false,
        "hook" => false,
    );
    var $is_driver = false;
    var $driver = NULL;
    // default options, this will be merge to Driver's Options
    var $option = array(
        "path" => "", // path for cache folder
        "htaccess" => null, // auto create htaccess
        "securityKey" => '', // Key Folder, Setup Per Domain will good.
        "system" => array(),
        "storage" => "",
        "cachePath" => "",
    );

    function __construct($storage = "", $option = array()) {
        self::setup($option);
        if (!$this->isExistingDriver($storage) && isset(self::$config['fallback'][$storage])) {
            $storage = self::$config['fallback'][$storage];
        }

        if ($storage == "") {
            $storage = self::$storage;
            self::option("storage", $storage);
        } else {
            self::$storage = $storage;
        }

        $this->tmp['storage'] = $storage;

        if ($storage != "auto" && $storage != "" && $this->isExistingDriver($storage)) {
            $driver = "phpfastcache_" . $storage;
        } else {
            $storage = $this->autoDriver();
            self::$storage = $storage;
            $driver = "phpfastcache_" . $storage;
        }

        $this->option("storage", $storage);

        if (class_exists($driver, false)) {
            $this->driver = new $driver($this->option);
            $this->driver->is_driver = true;
        }
    }

    public static function getInstance($type, $config) {
        if (!isset(self::$intances[$type])) {
            self::$intances[$type] = new phpFastCache($type, $config);
        }
        return self::$intances[$type];
    }

    /*
     * Basic Method
     */

    function set($keyword, $value = "", $time = 300, $option = array()) {
        $object = array(
            "value" => $value,
            "write_time" => @date("U"),
            "expired_in" => $time,
            "expired_time" => @date("U") + (Int) $time,
        );
        if ($this->is_driver == true) {
            return $this->driver_set($keyword, $object, $time, $option);
        } else {
            return $this->driver->driver_set($keyword, $object, $time, $option);
        }
    }

    function get($keyword, $option = array()) {
        if ($this->is_driver == true) {
            $object = $this->driver_get($keyword, $option);
        } else {
            $object = $this->driver->driver_get($keyword, $option);
        }

        if ($object == null) {
            return null;
        }
        return $object['value'];
    }

    function delete($keyword, $option = array()) {
        if ($this->is_driver == true) {
            return $this->driver_delete($keyword, $option);
        } else {
            return $this->driver->driver_delete($keyword, $option);
        }
    }

    function clean($option = array()) {
        if ($this->is_driver == true) {
            return $this->driver_clean($option);
        } else {
            return $this->driver->driver_clean($option);
        }
    }

    /*
     * Begin Parent Classes;
     */

    public static function setup($name, $value = "") {
        if (!is_array($name)) {
            if ($name == "storage") {
                self::$storage = $value;
            }

            self::$config[$name] = $value;
        } else {
            foreach ($name as $n => $value) {
                self::setup($n, $value);
            }
        }
    }

    /*
     * For Auto Driver
     *
     */

    function autoDriver() {
        foreach ($this->drivers as $namex) {
            $class = "phpfastcache_" . $namex;
            $option = $this->option;
            $option['skipError'] = true;
            $_driver = new $class($option);
            $_driver->option = $option;
            if ($_driver->checkdriver()) {
                return $namex;
            }
        }
        $system = systemInfo();
        foreach ($system['cache_drivers'] as $filepath) {
            $file = pathinfo($filepath, PATHINFO_BASENAME);
            $namex = str_replace(".php", "", $file);
            $clazz = "phpfastcache_" . $namex;
            $option = $this->option;
            $option['skipError'] = true;
            $_driver = new $clazz($option);
            $_driver->option = $option;
            if ($_driver->checkdriver()) {
                return $namex;
            }
        }
        return "";
    }

    function option($name, $value = null) {
        if ($value == null) {
            if (isset($this->option[$name])) {
                return $this->option[$name];
            } else {
                return null;
            }
        } else {

            if ($name == "path") {
                $this->checked['path'] = false;
                $this->driver->checked['path'] = false;
            }
            self::$config[$name] = $value;
            $this->option[$name] = $value;
            $this->driver->option[$name] = $this->option[$name];

            return $this;
        }
    }

    public function setOption($option = array()) {
        $this->option = array_merge($this->option, self::$config, $option);
        $this->checked['path'] = false;
    }

    function __get($name) {
        $this->driver->option = $this->option;
        return $this->driver->get($name);
    }

    function __set($name, $v) {
        $this->driver->option = $this->option;
        if (isset($v[1]) && is_numeric($v[1])) {
            return $this->driver->set($name, $v[0], $v[1], isset($v[2]) ? $v[2] : array() );
        } else {
            throw new Exception("Example ->$name = array('VALUE', 300);", 98);
        }
    }
    private function isExistingDriver($class) {
        $class = strtolower($class);
        if (!class_exists("phpfastcache_" . $class, false)) {
            return false;
        }
        foreach ($this->drivers as $namex) {
            $clazz = "phpfastcache_" . $namex;
            if (class_exists($clazz, false)) {
                $option = $this->option;
                $option['skipError'] = true;
                $_driver = new $clazz($option);
                $_driver->option = $option;
                if ($_driver->checkdriver() && $class == $namex) {
                    return true;
                }
            }
        }
        $system = systemInfo();
        foreach ($system['cache_drivers'] as $filepath) {
            $file = pathinfo($filepath, PATHINFO_BASENAME);
            $namex = str_replace(".php", "", $file);
            $clazz = "phpfastcache_" . $namex;
            if (class_exists($clazz, false)) {
                $option = $this->option;
                $option['skipError'] = true;
                $_driver = new $clazz($option);
                $_driver->option = $option;
                if ($_driver->checkdriver() && $class == $namex) {
                    return true;
                }
            }
        }
        return false;
    }
    public function encode($data) {
        return serialize($data);
    }
    public function decode($value) {
        $x = @unserialize($value);
        if ($x == false) {
            return $value;
        } else {
            return $x;
        }
    }
    public function isPHPModule() {
        if (PHP_SAPI == "apache2handler") {
            return true;
        } else {
            if (strpos(PHP_SAPI, "handler") !== false) {
                return true;
            }
        }
        return false;
    }

    /*
     * return PATH for Files & PDO only
     */
    public function getPath($create_path = false) {

        if ($this->option['path'] == "" && self::$config['path'] != "") {
            $this->option("path", self::$config['path']);
        }


        if ($this->option['path'] == '') {
            // revision 618
            if ($this->isPHPModule()) {
                $tmp_dir = ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir();
                $this->option("path", $tmp_dir);
            } else {
                $this->option("path", dirname(__FILE__));
            }

            if (self::$config['path'] == "") {
                self::$config['path'] = $this->option("path");
            }
        }
        $full_path = $this->option("path") . "/"; //. $this->option("securityKey") . "/";

        if ($create_path == false && $this->checked['path'] == false) {

            if (!file_exists($full_path) || !is_writable($full_path)) {
                if (!file_exists($full_path)) {
                    @mkdir($full_path, 0777);
                }
                if (!is_writable($full_path)) {
                    @chmod($full_path, 0777);
                }
            }
            $this->checked['path'] = true;
        }

        $this->option['cachePath'] = $full_path;
        return $this->option['cachePath'];
    }
    /*
     * Read File
     * Use file_get_contents OR ALT read
     */
    function readfile($file) {
        if (function_exists("file_get_contents")) {
            return file_get_contents($file);
        } else {
            $string = "";

            $file_handle = @fopen($file, "r");
            if (!$file_handle) {
                throw new Exception("Can't Read File", 96);
            }
            while (!feof($file_handle)) {
                $line = fgets($file_handle);
                $string .= $line;
            }
            fclose($file_handle);

            return $string;
        }
    }
}