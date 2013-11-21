<?php

require_once 'pluginfortest.php';
require_once('simpletest/autorun.php');
require_once('simpletest/browser.php');
/**
 * MicroPHP模型测试案例
 */
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
 * @createdtime         2013-11-19 13:47:34
 */

/**
 * Description of test_model
 *
 * @author pm
 */
class Test_model extends UnitTestCase {

    public function setUp() {
        global $system;
        $system['helper_file_autoload'] = array('function');
        $system['library_file_autoload'] = array('TestLibrary');
        $system['models_file_autoload'] = array('test/SubUserModel', 'UserModel', array('UserModel' => 'user2'));
        WoniuRouter::setConfig($system);
    }

    public function tearDown() {
        global $default;
        WoniuRouter::setConfig($default);
    }

    public function testModelLoader() {
        $this->assertIsA(WoniuModel::instance('UserModel'), 'UserModel');
        $this->assertIsA(WoniuModel::instance('UserModel'), 'UserModel');
        $this->assertIsA(WoniuModel::instance('UserModel')->test(), 'SubUserModel');
        $this->assertIsA(WoniuModel::instance('test/SubUserModel'), 'SubUserModel');
        $this->assertIsA(WoniuModel::instance('test/SubUserModel'), 'SubUserModel');
        $this->assertIsA(WoniuModel::instance('test/SubUserModel')->test(), 'UserModel');
        WoniuLoader::instance()->model('UserModel', 'user');
        $this->assertReference(WoniuLoader::instance()->model->user, WoniuLoader::instance()->model->UserModel);
        $this->assertReference(WoniuLoader::instance()->model->user, WoniuModel::instance('UserModel'));
        WoniuLoader::instance()->model('test/SubUserModel', 'subuser');
        $this->assertReference(WoniuLoader::instance()->model->subuser, WoniuLoader::instance()->model->SubUserModel);
        $this->assertReference(WoniuLoader::instance()->model->subuser, WoniuModel::instance('SubUserModel'));
        $this->assertReference(WoniuLoader::instance()->model->user, WoniuLoader::instance()->model->user2);
        $browser = new SimpleBrowser();
        $browser->get(getReqURL('?model.mixLoader'));
        $this->assertEqual($browser->getContent(), 'okay');
    }

}
