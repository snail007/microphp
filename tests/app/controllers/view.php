<?php

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
 * @createdtime         2013-11-21 10:46:02
 */

/**
 * Description of view
 *
 * @author pm
 */
class View extends WoniuController{
    public function doReturn(){
        $data['msg']='lude';
        echo 'test_'.$this->view('view',$data,TRUE);
    }
    public function doView(){
        $this->view('view');
    }
    public function doData(){
        $data['msg']='lude';
        $this->view('view',$data);
    }
    public function doTest(){
        var_dump($this->router);
    }
}
