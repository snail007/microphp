<?php

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
class WoniuLoader {

    public $db;
    private $helper_files = array();
    protected $model;
    private $view_vars = array();
    private static $instance;

    public function __construct() {
        date_default_timezone_set($this->config('system', 'default_timezone'));
        $this->model = new WoniuModelLoader();
        if ($this->config('system', "autoload_db")) {
            $this->database();
        }
        stripslashes_all();
    }

    public function config($config_group, $key = '') {
        global $$config_group;
        if ($key) {
            $config_group = $$config_group;
            return isset($config_group[$key]) ? $config_group[$key] : null;
        } else {
            return isset($$config_group) ? $$config_group : null;
        }
    }

    public function database($config = NULL, $is_return = false) {
        if ($is_return) {
            $db = null;
            //没有传递配置，使用默认配置
            if (!is_array($config)) {
                global $woniu_db;
                $db = WoniuDB::getInstance($woniu_db[$woniu_db['active_group']]);
            } else {
                $db = WoniuDB::getInstance($config);
            }
            return $db;
        } else {
            //没有传递配置，使用默认配置
            if (!is_array($config)) {
                if (!is_object($this->db)) {
                    global $woniu_db;
                    $this->db = WoniuDB::getInstance($woniu_db[$woniu_db['active_group']]);
                }
            } else {
                $this->db = WoniuDB::getInstance($config);
            }
        }
    }

    public function helper($file_name) {
        global $system;
        $filename = $system['helper_folder'] . DIRECTORY_SEPARATOR . $file_name . $system['helper_file_subfix'];
        if (in_array($filename, $this->helper_files)) {
            return;
        }
        if (file_exists($filename)) {
            $this->helper_files[] = $filename;
            //包含文件，并把文件里面的变量变为全局变量
            $before_vars = array_keys(get_defined_vars());
            include $filename;
            $vars = get_defined_vars();
            $all_vars = array_keys($vars);
            foreach ($all_vars as $key) {
                if (!in_array($key, $before_vars) && isset($vars[$key])) {
                    $GLOBALS[$key] = $vars[$key];
                }
            }
        } else {
            trigger404($filename . ' not found.');
        }
    }

    public function model($file_name, $alias_name = null) {
        global $system;
        $classname = $file_name;
        if (strstr($file_name, '/') !== false || strstr($file_name, "\\") !== false) {
            $classname = basename($file_name);
        }
        if (!$alias_name) {
            $alias_name = strtolower($classname);
        }
        $filepath = $system['model_folder'] . DIRECTORY_SEPARATOR . $file_name . $system['model_file_subfix'];
        if (in_array($alias_name, array_keys(WoniuModelLoader::$model_files))) {
            return WoniuModelLoader::$model_files[$alias_name];
        }
        if (file_exists($filepath)) {
            include $filepath;
            if (class_exists($classname)) {
                return WoniuModelLoader::$model_files[$alias_name] = new $classname();
            } else {
                trigger404('Model Class:' . $classname . ' not found.');
            }
        } else {
            trigger404($filepath . ' not found.');
        }
    }

    public function view($view_name, $data = null, $return = false) {
        if (is_array($data)) {
            $this->view_vars = array_merge($this->view_vars, $data);
            extract($this->view_vars);
        }
        global $system;
        $view_path = $system['view_folder'] . DIRECTORY_SEPARATOR . $view_name . $system['view_file_subfix'];
        if (file_exists($view_path)) {
            if ($return) {
                @ob_end_clean();
                ob_start();
                include $view_path;
                $html = ob_get_contents();
                @ob_end_clean();
                return $html;
            } else {
                include $view_path;
            }
        } else {
            trigger404('View:' . $view_path . ' not found');
        }
    }

    public static function classAutoloadRegister() {
        //在plugin模式下，路由器不再使用，那么自动注册不会被执行，自动加载功能会失效，所以在这里再尝试加载一次，
        //如此一来就能满足两种模式
        $found = false;
        $__autoload_found = false;
        $auto_functions = spl_autoload_functions();
        if (is_array($auto_functions)) {
            foreach ($auto_functions as $func) {
                if (is_array($func) && $func[0] == 'WoniuLoader' && $func[1] == 'classAutoloader') {
                    $found = TRUE;
                    break;
                }
            }
            foreach ($auto_functions as $func) {
                if (!is_array($func) && $func == '__autoload') {
                    $__autoload_found = TRUE;
                    break;
                }
            }
        }
        if (function_exists('__autoload') && !$__autoload_found) {
            //如果存在__autoload而且没有被注册过,就显示的注册它，不然它会因为spl_autoload_register的调用而失效
            spl_autoload_register('__autoload');
        }
        if (!$found) {
            //最后注册我们的自动加载器
            spl_autoload_register(array('WoniuLoader', 'classAutoloader'));
        }
    }

    public static function classAutoloader($clazzName) {
        global $system;
        $library = $system['library_folder'] . DIRECTORY_SEPARATOR . $clazzName . $system['library_file_subfix'];
        if (file_exists($library)) {
            include($library);
        }
    }

    public static function instance() {
        //在plugin模式下，路由器不再使用，那么自动注册不会被执行，自动加载功能会失效，所以在这里再尝试加载一次，
        //如此一来就能满足两种模式
        self::classAutoloadRegister();
        return empty(self::$instance) ? self::$instance = new self() : self::$instance;
    }

    public static function get($key = null, $default = null) {
        return self::gpcs('_GET', $key, $default);
    }

    public static function post($key = null, $default = null) {
        return self::gpcs('_POST', $key, $default);
    }

    public static function cookie($key = null, $default = null) {
        return self::gpcs('_COOKIE', $key, $default);
    }

    public static function session($key = null, $default = null) {
        return self::gpcs('_SESSION', $key, $default);
    }

    private static function gpcs($range, $key, $default) {
        if ($key === null) {
            return $$range;
        } else {
            $range = $$range;
            return isset($range[$key]) ? $range[$key] : $default !== null ? $default : null;
        }
    }

}

class WoniuModelLoader {

    public static $model_files = array();

    function __get($classname) {
        return isset(self::$model_files[strtolower($classname)]) ? self::$model_files[strtolower($classname)] : null;
    }

}

/* End of file Loader.php */