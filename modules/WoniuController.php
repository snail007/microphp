<?php

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
class WoniuController extends WoniuLoader {

    private static $woniu;
    private static $instance;

    public function __construct() {
        $this->autoload();
        parent::__construct();
        self::$woniu = &$this;
    }

    private function autoload() {
        $system = WoniuLoader::$system;
        $autoload_helper = $system['helper_file_autoload'];
        $autoload_library = $system['library_file_autoload'];
        $autoload_models = $system['models_file_autoload'];
        foreach ($autoload_helper as $file_name) {
            $this->helper($file_name);
        }
        foreach ($autoload_library as $key => $val) {
            if (is_array($val)) {
                $key = key($val);
                $val = $val[$key];
                $this->lib($key, $val);
            } else {
                $this->lib($val);
            }
        }
        foreach ($autoload_models as $key => $val) {
            if (is_array($val)) {
                $key = key($val);
                $val = $val[$key];
                $this->model($key, $val);
            } else {
                $this->model($val);
            }
        }
        /**
         * 如果使用了自定义缓存驱动，加载相应的文件
         */
        static $included = array();
        foreach ($system['cache_drivers'] as $filepath) {
            $file = pathinfo($filepath, PATHINFO_BASENAME);
            $namex = str_replace(".php", "", $file);
            //只include选择的缓存驱动文件
            if ($namex == $system['cache_config']['storage']) {
                if (!isset($included[realpath($filepath)])) {
                    WoniuLoader::includeOnce($filepath);
                } else {
                    $included[realpath($filepath)] = 1;
                }
            }
        }
    }

    public static function &getInstance() {
        return self::$woniu;
    }

    public static function instance($classname_path = null) {
        if (empty($classname_path)) {
            return self::$instance=new self();
        }
        $system = WoniuLoader::$system;
        $classname_path = str_replace('.', DIRECTORY_SEPARATOR, $classname_path);
        $classname = basename($classname_path);
        $filepath = $system['controller_folder'] . DIRECTORY_SEPARATOR . $classname_path . $system['controller_file_subfix'];
        $alias_name = strtolower($classname);

        if (in_array($alias_name, array_keys(WoniuModelLoader::$model_files))) {
            return WoniuModelLoader::$model_files[$alias_name];
        }
        if (file_exists($filepath)) {
            WoniuLoader::classAutoloadRegister();
            WoniuLoader::includeOnce($filepath);
            if (class_exists($classname,FALSE)) {
                return WoniuModelLoader::$model_files[$alias_name] = new $classname();
            } else {
                trigger404('Ccontroller Class:' . $classname . ' not found.');
            }
        } else {
            trigger404($filepath . ' not found.');
        }
    }

}

/* End of file Controller.php */