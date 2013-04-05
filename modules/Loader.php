<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of loader
 *
 * @author Administrator
 */
class Loader {

    protected $db;
    private $helper_files = array();
    protected $model;
    private $view_vars = array();

    public function __construct() {
        date_default_timezone_set($this->config('system', 'default_timezone'));
        self::classAutoloadRegister();
        $this->model = new ModelLoader();
        if ($this->config('system', "autoload_db")) {
            $this->db = MySQL::getInstance();
        }
        stripslashes_all();
    }

    public function config($config_group, $key) {
        global $$config_group;
        $config_group = $$config_group;
        return isset($config_group[$key]) ? $config_group[$key] : null;
    }

    public function database($config = NULL) {
        //没有传递配置，使用默认配置
        if (!is_array($config)) {
            if (!is_object($this->db)) {
                $this->db = MySQL::getInstance($config);
            }
            return $this->db;
        } else {
            $db = MySQL::getInstance($config);
            return $db;
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
            include $filename;
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
        if (in_array($alias_name, array_keys(ModelLoader::$model_files))) {
            return ModelLoader::$model_files[$alias_name];
        }
        if (file_exists($filepath)) {
            include $filepath;
            if (class_exists($classname)) {
                return ModelLoader::$model_files[$alias_name] = new $classname();
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
        spl_autoload_register(array('Loader', 'classAutoloader'));
    }

    public static function classAutoloader($clazzName) {
        global $system;
        $library = $system['library_folder'] . DIRECTORY_SEPARATOR . $clazzName . $system['library_file_subfix'];
        if (file_exists($library)) {
            include($library);
        } else {
            #有大于1个的autoload吗？这里判断一下，避免干扰其它autoload
            if (count(spl_autoload_functions()) > 1) {
                return; #有大于一个的autoload直接返回，让其它的autoload继续查找。
            } else {
                #只有一个autoload即本MrPmvc的appAutoload，那么就做404提示处理
                echo trigger404('Class : ' . $clazzName . ' not found.');
            }
        }
    }

}

class ModelLoader {

    public static $model_files = array();

    function __get($classname) {
        return isset(self::$model_files[strtolower($classname)]) ? self::$model_files[strtolower($classname)] : null;
    }

}