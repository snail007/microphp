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
        $clazz = 'test/SubUserModel';
        var_dump($this->getModelMethods($clazz));
        $clazz = 'home/testHook';
        var_dump($this->getControllerMethods($clazz));
        $clazz = 'woniu_debuger';
        var_dump($this->getControllerMethods($clazz));
        $this->view('woniu_debuger');
    }

    public function getControllerMethods($clazz) {
        if(!class_exists(basename($clazz))){
            WoniuController::instance($clazz);
        }
        $clazz=  basename($clazz);
        $class = new ReflectionClass($clazz);
        $all_methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        $methods = array();
        $prefix=self::$system['controller_method_prefix'];
        foreach ($all_methods as $method) {
            if (strtolower($method->class) == strtolower($clazz) && $method->name != '__construct' && stripos($method->name, $prefix) === 0) {
                $methods[] = $method->name;
            }
        }
        return $methods;
    }
    public function getModelMethods($clazz) {
        if(!class_exists(basename($clazz))){
            WoniuModel::instance($clazz);
        }
        $clazz=  basename($clazz);
        $class = new ReflectionClass($clazz);
        $all_methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        $methods = array();
        foreach ($all_methods as $method) {
            if (strtolower($method->class) == strtolower($clazz) && $method->name != '__construct') {
                $methods[] = $method->name;
            }
        }
        return $methods;
    }

}
