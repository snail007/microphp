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
 * @createdtime            2013-11-18 20:54:07
 */
class Route extends WoniuController{
    //传参数测试
    public function doIndex($flag=null,$flag2=null){
        echo "hello:".$flag.$flag2.$this->input->get('flag','');
    }
    public function doTestUrl(){
        dump(
            url('#?'),
            url('#?welcome.index'),
            url('?#welcome.index','aa','bb'),
            url('?welcome.index',array('a'=>'bb','b'=>'ccc'),'dd','ee'),
            url('#welcome.index',array('a'=>'bb','b'=>'ccc')),
            url('welcome.index','dd','ee',array('a'=>'bb')),
            url('','aa','bb'),
            url('',array('a'=>'bb','b'=>'ccc'),'dd','ee'),
            url('',array('a'=>'bb','b'=>'ccc')),
            urlPath('../public/test/'), 
            path('/public/test/'),
            WoniuInput::$router
         );
    }
}
