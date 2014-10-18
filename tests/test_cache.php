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
class Test_cache extends UnitTestCase {

    public function testAuto() {
        global $system;
        $ignore=  explode(',', 'memcached,redis,memcache');
        foreach (explode(",", 'auto,apc,sqlite,files,memcached,redis,wincache,xcache,memcache') as $driver) {
            if(in_array($driver, $ignore)){
                continue;
            }
            $system['cache_config']['storage'] = $driver;
            MpRouter::setConfig($system);
            $woniu = WoniuController::instance();
            $this->assertIsA($woniu->cache, 'phpFastCache');
            if ($driver != 'auto') {
                $this->assertTrue(in_array($woniu->cache->option('storage'), array($driver, 'files')));
            }
            if (!defined('IN_ALL_TESTS')) {
                echo $driver . "=>" . $woniu->cache->option('storage') . "<br/>";
            }
            $woniu->cache->set('test', 1, 1);
            $this->assertEqual($woniu->cache->get('test'), 1);
            sleep(2);
            if ($woniu->cache->option('storage') != 'apc') {
                $this->assertFalse($woniu->cache->get('test'));
            }
            $woniu->cache->set('test2', 10, 1);
            $woniu->cache->set('test3', 10, 1);
            $woniu->cache->delete('test2');
            $this->assertFalse($woniu->cache->get('test2'));
            if ($woniu->cache->option('storage') != 'sqlite') {
                $woniu->cache->clean();
                $this->assertFalse($woniu->cache->get('test3'));
            }
        }
    }

    public function tearDown() {
        global $default;
        MpRouter::setConfig($default);
    }

}
