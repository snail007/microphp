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
 * @createdtime         2013-11-22 9:48:33
 */

/**
 * Description of test_library
 *
 * @author pm
 */
class Test_library extends UnitTestCase{
    public function testLibLoader() {
        $this->assertFalse(class_exists('TestLibrary',FALSE));
        $woniu=  WoniuLoader::instance();
        $this->assertIsA(new TestLibrary(), 'TestLibrary');
        $lib=$woniu->lib('sub/SubLib');
        $lib2=$woniu->lib('sub/SubLib','SubLib2');
        $lib3=$woniu->lib->SubLib2;
        $lib4=$woniu->lib->SubLib;
        $this->assertIsA($lib, 'SubLib');
        $this->assertIsA($lib2, 'SubLib');
        $this->assertIsA($lib3, 'SubLib');
        $this->assertIsA($lib4, 'SubLib');
        $this->assertReference($lib2, $lib);
        $this->assertReference($lib3, $lib2);
        $this->assertReference($lib4, $lib3);
        $this->assertTrue($lib->test());
        $woniu->lib('TestLibrary','tl');
        $this->assertIsA($woniu->lib->tl, 'TestLibrary');
        $this->assertReference($woniu->lib('TestLibrary','tl'), $woniu->lib->tl);
    }
}
