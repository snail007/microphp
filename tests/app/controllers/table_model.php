<?php

/*
 * Copyright 2014 pm.
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
 * @createdtime         2014-5-15 10:24:40
 */

/**
 * Description of table_model
 *
 * @author pm
 */
class table_model extends WoniuController {

    public function doTest() {
        $cfg = dbInfo();
        $cfg['database'] = 'moive';
        $cfg['dbprefix'] = 'mv_';
        $db = $this->database($cfg, true);
        $article = table('admin', $db);
        $_POST['user'] = 'testsa';
        $_POST['test'] = 'testsa';
        $_POST['pass'] = 'testtesttesttest';
        $rule = array(
            'username' => array($this->rule->range_len(5, 16) => '用户名5-16字符'),
            'password' => array($this->rule->len(16) => '密码16字符'),
        );
        $map=array('user'=>'username','pass'=>'password','test'=>'test');
        $data = array();
        if (is_null($msg = $article->check($_POST, $data, $rule,$map))) {
            //dump($data);
            dump($article->update($data,3));
        } else {
            echo $msg;
        }
    }

}
