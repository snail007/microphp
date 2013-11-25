<?php
require_once 'pluginfortest.php';
require_once('simpletest/autorun.php');
/*
 * Copyright 2013 pm.
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
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		MicroPHP
 * @author		狂奔的蜗牛
 * @email		672308444@163.com
 * @copyright	        Copyright (c) 2013 - 2013, 狂奔的蜗牛, Inc.
 * @link		http://git.oschina.net/snail/microphp
 * @createdtime         2013-11-21 11:22:04
 */

/**
 * Description of test_instance
 *
 * @author pm
 */
class Test_instance extends UnitTestCase {

    public function testInstance() {
        $this->assertEqual(WoniuLoader::instance(), WoniuLoader::instance());
        $this->assertEqual(WoniuController::instance(), WoniuController::instance());
        $this->assertEqual(WoniuModel::instance(), WoniuModel::instance());
        $this->assertReference(WoniuController::instance('route'), WoniuController::instance('route'));
        $this->assertReference(WoniuModel::instance('UserModel'), WoniuModel::instance('UserModel'));
        
    }

}
