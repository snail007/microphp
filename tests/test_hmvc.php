<?php

require_once 'pluginfortest.php';
//require_once('simpletest/web_tester.php');
require_once('simpletest/autorun.php');
/*
 * Copyright 2013 snail.
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
 * Description of test
 * An open source application development framework for PHP 5.2.0 or newer
 *
 * @package		MicroPHP
 * @author		狂奔的蜗牛
 * @email		672308444@163.com
 * @copyright	        Copyright (c) 2013 - 2013, 狂奔的蜗牛, Inc.
 * @link		http://git.oschina.net/snail/microphp
 * @createdtime         2013-11-22 10:05:38
 */

/**
 * Description of empty
 *
 * @author pm
 */
class test_hmvc extends UnitTestCase {

    public function test_instance() {
        $default = MpLoader::$system;
        $db1 = MpLoader::instance()->database();
        $controller = WoniuController::instance('home', 'hmvc_demo');
        $this->assertEqual($controller->doHmvc(), 'okay');
        $model = WoniuModel::instance('HmvcModel', 'hmvc_demo');
        $model->test();
        $this->assertEqual($model->plus(2, 3), 5);
        $db2 = MpLoader::instance(true, 'hmvc_demo')->database();
        $db3 = MpLoader::instance(null, 'hmvc_demo')->database();
        $db4 = MpLoader::instance(true, 'hmvc_demo')->database();
        $this->assertNotEqual($db2, $db1);
        $this->assertEqual($db2, $db4);
        $this->assertEqual($db2, $db3);
        MpLoader::$system = $default;
    }

}
