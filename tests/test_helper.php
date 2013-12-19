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
 * An open source application development framework for PHP 5.1.6 or newer
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
class Test_helper extends UnitTestCase {
    public function testHelper(){
        $woniu=  WoniuLoader::instance();
        $woniu->helper('configxxx');
        $woniu->helper('config');
        $woniu->helper('config2');
        $this->assertEqual($woniu->config('product'), 'microphp');
        $this->assertEqual($woniu->config('host'), '127.0.0.1');
        $this->assertEqual($woniu->config('host2'), '127.0.0.2');
        $woniu->setConfig('host','localhost');
        $this->assertEqual($woniu->config('host'), 'localhost');
        $this->assertEqual($woniu->config('db','user'), 'root');
        $this->assertEqual($woniu->config('db','pass'), 'admin');
        $db=$woniu->config('db');
        $db['user']='admin_user';
        $this->assertIsA($db, 'Array');
        $woniu->setConfig('db',$db);
        $this->assertEqual($woniu->config('db','user'), 'admin_user');
        $this->assertTrue($woniu::$system['debug']);
    }
}
