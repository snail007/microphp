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

/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.1.6 or newer
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

    public function doIndex() {
        $controllers=$this->getControllers();
        $models=$this->getModels();
        $c=array();
        foreach ($controllers as $cl) {
            $c[$cl]=  $this->getControllerMethods($cl);
        }
        $m=array();
        foreach ($models as $md) {
            $m[$md]=  $this->getModelMethods($md);
        }
        ksort($c);
        ksort($m);
        $data['c']=$c;
        $data['m']=$m;
        $this->view('woniu_debuger',$data);
    }
    public function doModelLoader(){
        $args=  func_get_args();
        $model= $this->input->get('debuger_model');
        $method=$this->input->get('debuger_method');
        unset($_GET['debuger_model']);
        unset($_GET['debuger_method']);
        $obj=$this->model($model);
        $m=new ReflectionMethod(basename($model),$method);
        $output=$m->invokeArgs($obj, $args);
        if(!is_null($output)){
            echo $model.'->'.$method." 返回:\n";
            var_dump($output);
        }else{
            echo $model.'->'.$method." 返回:\nNULL";
        }
        
    }

    public function getControllers() {
        $path = self::$system['controller_folder'];
        $sub_fix = self::$system['controller_file_subfix'];
        $res = $this->scan($path);
        foreach ($res as &$p) {
            $p = str_replace(array($path . '/', $sub_fix), '', $p);
        }
        return array_diff($res,array('woniu_debuger'));
    }

    public function getModels() {
        $path = self::$system['model_folder'];
        $sub_fix = self::$system['model_file_subfix'];
        $res = $this->scan($path);
        foreach ($res as &$p) {
            $p = str_replace(array($path . '/', $sub_fix), '', $p);
        }
        return $res;
    }

    public function scan($path) {
        $controllers = array();
        $files = array_diff(scandir($path), array('.', '..'));
        $reach_last = false;
        foreach ($files as $p) {
            if ($reach_last == $p) {
                $reach_last = true;
            }
            $p = $path . '/' . $p;
            if (is_file($p)) {
                $controllers[] = $p;
            } else {
                $controllers = array_merge($controllers, $this->scan($p));
            }
        }
        return $controllers;
    }

    public function getControllerMethods($clazz) {
        if (!class_exists(basename($clazz))) {
            WoniuController::instance($clazz);
        }
        $clazz = basename($clazz);
        $class = new ReflectionClass($clazz);
        $all_methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        $methods = array();
        $prefix = self::$system['controller_method_prefix'];
        foreach ($all_methods as $method) {
            if (strtolower($method->class) == strtolower($clazz) && $method->name != '__construct' && stripos($method->name, $prefix) === 0) {
                $_m['name']=$method->name;
                $m=new ReflectionMethod($clazz, $method->name);
                $args=$m->getParameters();
                $_args=array();
                foreach ($args as $a) {
                    $_args[]='$'.$a->name;
                }
                $_m['args']=  '('.implode(',', $_args).')';
                $methods[]=$_m;
            }
        }
        return sortRs($methods, 'name');
    }

    public function getModelMethods($clazz) {
        if (!class_exists(basename($clazz))) {
            WoniuModel::instance($clazz);
        }
        $clazz = basename($clazz);
        $class = new ReflectionClass($clazz);
        $all_methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        $methods = array();
        foreach ($all_methods as $method) {
            if (strtolower($method->class) == strtolower($clazz) && $method->name != '__construct') {
                $_m['name'] = $method->name;
                $m=new ReflectionMethod($clazz, $method->name);
                $args=$m->getParameters();
                $_args=array();
                foreach ($args as $a) {
                    $_args[]='$'.$a->name;
                }
                $_m['args']=  '('.implode(',', $_args).')';
                $methods[] = $_m;
            }
        }
        return sortRs($methods, 'name');
    }

}
