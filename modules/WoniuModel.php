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
class WoniuModel extends WoniuLoaderPlus {

    private static $instance;

    public static function instance($classname_path=null) {
        //这里调用控制器instance是为了触发自动加载，从而避免了插件模式下，直接instance模型，自动加载失效的问题
        WoniuController::instance();
        if (empty($classname_path)) {
            return empty(self::$instance) ? self::$instance = new self() : self::$instance;
        }
        $system=  WoniuLoader::$system;
        $classname_path = str_replace('.', DIRECTORY_SEPARATOR, $classname_path);
        $classname = basename($classname_path);
        $filepath = $system['model_folder'] . DIRECTORY_SEPARATOR . $classname_path . $system['model_file_subfix'];
        $alias_name = strtolower($classname);
        if (in_array($alias_name, array_keys(WoniuModelLoader::$model_files))) {
            return WoniuModelLoader::$model_files[$alias_name];
        }
        
        if (file_exists($filepath)) {
            //在plugin模式下，路由器不再使用，那么自动注册不会被执行，自动加载功能会失效，所以在这里再尝试加载一次，
            //如此一来就能满足两种模式
            WoniuLoader::classAutoloadRegister();
            if(!class_exists($filepath, FALSE)){
                WoniuLoader::includeOnce($filepath);
            }
            if (class_exists($classname,FALSE)) {
                return WoniuModelLoader::$model_files[$alias_name] = new $classname();
            } else {
                trigger404('Model Class:' . $classname . ' not found.');
            }
        } else {
            trigger404($filepath . ' not found.');
        }
    }

}

/* End of file Model.php */