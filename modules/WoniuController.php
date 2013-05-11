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
class WoniuController extends WoniuLoader {

    private static $woniu;
    private static $instance;

    public function __construct() {
        parent::__construct();
        self::$woniu = &$this;
    }

    public static function &getInstance() {
        return self::$woniu;
    }
    public static function instance($classname_path) {
        if (empty($classname_path)) {
            return empty(self::$instance) ? self::$instance = new self() : self::$instance;
        }
        global $system;
        $classname_path = str_replace('.', DIRECTORY_SEPARATOR, $classname_path);
        $classname = basename($classname_path);
        $filepath = $system['controller_folder'] . DIRECTORY_SEPARATOR . strtolower($classname_path) . $system['controller_file_subfix'];
        $alias_name = strtolower($filepath);

        if (in_array($alias_name, array_keys(WoniuModelLoader::$model_files))) {
            return WoniuModelLoader::$model_files[$alias_name];
        }
        if (file_exists($filepath)) {
            WoniuLoader::classAutoloadRegister();
            include $filepath;
            if (class_exists($classname)) {
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