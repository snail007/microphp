<?php

/*
 * Copyright 2013 Snail.
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
if (!function_exists('lcfirst')) {

    function lcfirst($str) {
        if (strlen($str)) {
            return strtolower($str{0}) . substr($str, 1);
        }
        return '';
    }

}

/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.2.0 or newer
 *
 * @package                MicroPHP
 * @author                 狂奔的蜗牛
 * @email                  672308444@163.com
 * @copyright              Copyright (c) 2013 - 2013, 狂奔的蜗牛, Inc.
 * @link                   http://git.oschina.net/snail/microphp
 * @since                  Version 1.0
 * @createdtime            2013-12-6 19:43:33
 */
class Woniu_debuger extends WoniuController {

    private $view_path = 'woniu_debuger';

    /**
     * 访问密码
     * @var type 
     */
    private $password = 'snail';

    /**
     * IP白名单，只有在数组中的IP才能访问，如果白名单为空则允许所有IP访问
     * @var type 
     */
    private $ip_white_list = array(
        '127.0.0.1',
    );

    /**
     * 控制器文件夹和模型文件夹里面忽略的文件
     * 点开头的文件会自动忽略，比如：.htaccess
     * @var type 
     */
    private $ingore_files = array(
        'index.html'
    );

    public function __construct() {
        parent::__construct();
        if (!isset($_SESSION)) {
            session_start();
        }
        if (!empty($this->ip_white_list) && !in_array($this->input->server('remote_addr'), $this->ip_white_list)) {
            exit();
        }
        if (empty($_SESSION['debuger']) && $this->input->post('p') == $this->password) {
            $_SESSION['debuger'] = true;
        } elseif (empty($_SESSION['debuger'])) {
            $html = '<form action="?' . $this->router['cpath'] . '.index" method="post">'
                    . 'Password:<input name="p" style="width:80px;" type="password"/>'
                    . '</form>';
            exit($html);
        }
    }

    public function doLogout() {
        unset($_SESSION['debuger']);
        $this->redirect('?' . $this->router['cpath'] . '.index');
    }

    public function doIndex() {
        $controllers = $this->getControllers();
        $models = $this->getModels();
        sort($controllers);
        sort($models);
        $data['c'] = $controllers;
        $data['m'] = $models;
        $this->view($this->view_path, $data);
    }

    public function doGetMethods($type = NULL) {
        if ($type == 'controller') {
            $this->ajax_echo(200, null, $this->getControllerMethods($this->input->post('clazz')));
        } else {
            $this->ajax_echo(200, null, $this->getModelMethods($this->input->post('clazz')));
        }
    }

    public function doModelLoader() {
        $args = func_get_args();
        $model = $this->input->get('debuger_model');
        $method = $this->input->get('debuger_method');
        unset($_GET['debuger_model']);
        unset($_GET['debuger_method']);
        $obj = $this->model($model);
        $m = new ReflectionMethod(basename($model), $method);
        $output = $m->invokeArgs($obj, $args);
        if (!is_null($output)) {
            var_dump($output);
        }
    }

    public function getControllers() {
        $path = is_array(self::$system['controller_folder']) ? current(self::$system['controller_folder']) : self::$system['controller_folder'];
        $path = realpath($path);
        $sub_fix = self::$system['controller_file_subfix'];
        $res = $this->scan($path);
        $ret = array();
        foreach ($res as &$p) {
            if (stripos(basename($p), '.') !== 0 && !in_array(basename($p), $this->ingore_files)) {
                $ret[] = str_replace(array($path . DIRECTORY_SEPARATOR, $sub_fix), '', $p);
            }
        }
        return array_diff($ret, array(lcfirst(get_class($this))));
    }

    public function getModels() {
        $path = is_array(self::$system['model_folder']) ? current(self::$system['model_folder']) : self::$system['model_folder'];
        $path = realpath($path);
        $sub_fix = self::$system['model_file_subfix'];
        $res = $this->scan($path);
        $ret = array();
        foreach ($res as &$p) {
            if (stripos(basename($p), '.') !== 0 && !in_array(basename($p), $this->ingore_files)) {
                $ret[] = str_replace(array($path . DIRECTORY_SEPARATOR, $sub_fix), '', $p);
            }
        }
        return $ret;
    }

    public function scan($path) {
        $controllers = array();
        $files = array_diff(scandir($path), array('.', '..'));
        $reach_last = false;
        foreach ($files as $p) {
            if ($reach_last == $p) {
                $reach_last = true;
            }
            $p = $path . DIRECTORY_SEPARATOR . $p;
            if (is_file($p)) {
                $controllers[] = realpath($p);
            } else {
                $controllers = array_merge($controllers, $this->scan($p));
            }
        }
        return $controllers;
    }

    public function getControllerMethods($clazz) {
        if (!class_exists(basename($clazz))) {
            $system = WoniuLoader::$system;
            $filepath = $system['controller_folder'] . DIRECTORY_SEPARATOR . $clazz . $system['controller_file_subfix'];
            WoniuLoader::includeOnce($filepath);
        }
        $clazz = basename($clazz);
        $class = new ReflectionClass($clazz);
        $all_methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        $methods = array();
        $prefix = self::$system['controller_method_prefix'];
        foreach ($all_methods as $method) {
            if (strtolower($method->class) == strtolower($clazz) && $method->name != '__construct' && stripos($method->name, $prefix) === 0) {
                $_m['name'] = $method->name;
                $m = new ReflectionMethod($clazz, $method->name);
                $_m['min_count'] = $m->getNumberOfRequiredParameters();
                $args = $m->getParameters();
                $_args = array();
                foreach ($args as $a) {
                    $_args[] = '$' . $a->name;
                }
                $_m['args'] = '(' . implode(',', $_args) . ')';
                $methods[] = $_m;
            }
        }
        return sortRs($methods, 'name');
    }

    public function getModelMethods($clazz) {
        if (!class_exists(basename($clazz))) {
            $system = WoniuLoader::$system;
            $filepath = $system['model_folder'] . DIRECTORY_SEPARATOR . $clazz . $system['model_file_subfix'];
            WoniuLoader::includeOnce($filepath);
        }
        $clazz = basename($clazz);
        $class = new ReflectionClass($clazz);
        $all_methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        $methods = array();
        foreach ($all_methods as $method) {
            if (strtolower($method->class) == strtolower($clazz) && $method->name != '__construct') {
                $_m['name'] = $method->name;
                $m = new ReflectionMethod($clazz, $method->name);
                $_m['min_count'] = $m->getNumberOfRequiredParameters();
                $args = $m->getParameters();
                $_args = array();
                foreach ($args as $a) {
                    $_args[] = '$' . $a->name;
                }
                $_m['args'] = '(' . implode(',', $_args) . ')';
                $methods[] = $_m;
            }
        }
        return sortRs($methods, 'name');
    }

}
