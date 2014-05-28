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
 * An open source application development framework for PHP 5.2.0 or newer
 *
 * @package                MicroPHP
 * @author                 狂奔的蜗牛
 * @email                  672308444@163.com
 * @copyright              Copyright (c) 2013 - 2013, 狂奔的蜗牛, Inc.
 * @link                   http://git.oschina.net/snail/microphp
 * @since                  Version 1.0
 * @createdtime            2013-12-12 21:42:39
 */
class Validator extends WoniuController {

    public function doIndex() {
        $rules = eval($this->input->post('rule'));
        $rules = !is_array($rules) ? array() : $rules;
        $data = $this->input->post();
        if (is_null($msg = $this->checkData($rules, $data, $return))) {
            echo 'validator okay.';
        } else {
            echo $msg;
        }
    }

    public function doForm() {
        $_POST['token'] = '09adfu09adfmca09dma0';
        $_POST['title'] = ' 文章<a>xxx</a>标题 ';
        $_POST['content'] = '内容<a>xxx</a>内容，<a onclick="alert();">xxx</a>脚本<script>xxxxx</script>';
        $map = array('title' => 'art_title', 'content' => 'art_content');
        $data = $this->readData($map, $_POST);
        $rule = array(
            'art_title' => array(
                'set[strip_tags,trim]' => '',
                'required' => '标题不能为空',
                'max_len[50]' => '标题最多50字符'
            ),
            'art_content' => array(
                'required' => '内容不能为空',
                'set_post[WoniuInput::xss_clean]' => '',
            )
        );
        $ret_data = array();
        $error_msg = $this->checkData($rule, $data, $ret_data);
        if (is_null($error_msg)) {
            //验证成功，$ret_data是验证处理过的数据。
            //do sth else
            dump($ret_data);
        } else {
            //验证失败，$error_msg是提示信息
            echo $error_msg;
        }
    }

    public function doCall() {
        //$this->callFunc(array($this,'callTest'),array('self obj calltest1','2'));
        $this->callFunc(array($this, 'callTest2'), array('self obj calltest2', '2'));
        $this->callFunc('Validator::callTest', array('self obj calltest3', '2'));
        $this->callFunc('callTest', array('self obj calltest4', '2'));
        $this->callFunc('var_dump', array('self obj calltest5', '2'));
    }

    private static function callTest() {
        var_dump(func_get_args());
    }

    private function callTest2() {
        var_dump(func_get_args());
    }

}

function callTest() {
    var_dump(func_get_args());
}
