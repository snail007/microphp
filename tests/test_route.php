<?php

require_once 'pluginfortest.php';
require_once('simpletest/web_tester.php');
require_once('simpletest/autorun.php');
/**
 * MicroPHP路由访问测试案例
 * 为了支持?xx.xx/xx路由模式，需要修改：
 * simpletest/encoding.php
 * 39行：asRequest(){...}
 * 修改为：
 * function asRequest() {
  if($this->value){
  return $this->key . '=' . urlencode($this->value);
  }
  return $this->key;
  }
 */
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
 * @createdtime            2013-11-18 21:01:46
 */
class Test_route extends WebTestCase {

    public function testArgs() {
        echo "<div>".WoniuLoader::instance()->page(100,3,10,'?article/list/{page}',array(3,4,5,1,2,6))."</div>";
        $this->get(getReqURL('route.index/hello', 'indexfortest.php?'));
        $this->assertEqual($this->getBrowser()->getContent(), 'hello:hello');
    }

    public function testArgsNull() {
        $this->get(getReqURL('route.index/', 'indexfortest.php?'));
        $this->assertEqual($this->getBrowser()->getContent(), 'hello:');
    }

    public function testArgsGet() {
        $this->get(getReqURL('route.index/&flag=microphp', 'indexfortest.php?'));
        $this->assertEqual($this->getBrowser()->getContent(), 'hello:microphp');
    }

    public function testPathInfoArgs() {
        $this->get(getReqURL('route.index/microphp', 'indexfortest.php/'));
        $this->assertEqual($this->getBrowser()->getContent(), 'hello:microphp');
    }

    public function testPathInfoArgsNull() {
        $this->get(getReqURL('route.index/', 'indexfortest.php/'));
        $this->assertEqual($this->getBrowser()->getContent(), 'hello:');
    }

    public function testPathInfoArgsGet() {
        $this->get(getReqURL('route.index/xxx/ccc?flag=中文', 'indexfortest.php/'));
        $this->assertEqual($this->getBrowser()->getContent(), 'hello:xxxccc中文');
    }

    /**
     * 自定义路由规则下，路由测试
     * 自定义路由为：
     *    "|router\\.([^&]+).*$|u"=>"route.index/$1_rewrite"
     */
    public function testArgsRoute() {
        //pathinfo模式，测试get变量
        $this->get(getReqURL('router.xxx/ccc?flag=中文', 'indexfortest.php/'));
        $this->assertEqual($this->getBrowser()->getContent(), 'hello:xxxccc_rewrite中文');
        //一般查询模式，测试get变量
        $this->get(getReqURL('router.xxx/ccc&flag=中文', 'indexfortest.php?'));
        $this->assertEqual($this->getBrowser()->getContent(), 'hello:xxxccc_rewrite中文');
    }

}
