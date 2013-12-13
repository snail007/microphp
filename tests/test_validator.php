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
class Test_validator extends UnitTestCase {

    public function testForm() {
        $WN = WoniuLoader::instance();
        $_POST['user'] = 'snail';
        $_POST['password'] = '123456';
        $rule = array(
            'user' => array(
                'alpha_start' => '用户名必须是字母开头',
                'alpha_dash' => '用户名必须是数字、字母、下划线和-组成',
                'len[5,16]' => '用户名长度5-16',
            ),
            'password' => array(
                'len[6,16]' => '密码长度6-16',
                'set_post[sha1,md5]' => '',
            )
        );
        $data = array();
        $this->assertNull($WN->checkData($rule, $_POST, $data));
        $this->assertEqual($data['password'], md5(sha1('123456')));

        $_POST['user'] = '0snai';
        $this->assertEqual($WN->checkData($rule, $_POST, $data), '用户名必须是字母开头');

        $_POST['user'] = 'asnai=';
        $this->assertEqual($WN->checkData($rule, $_POST, $data), '用户名必须是数字、字母、下划线和-组成');

        $_POST['user'] = 'snai';
        $this->assertEqual($WN->checkData($rule, $_POST, $data), '用户名长度5-16');

        $_POST['user'] = 'snai    ';
        $rule = array(
            'user' => array(
                'alpha_start' => '用户名必须是字母开头',
                'alpha_dash' => '用户名必须是数字、字母、下划线和-组成',
                'len[5,16]' => '用户名长度5-16',
                'set[trim]' => '',
            ),
        );
        $data = array();
        $this->assertEqual($WN->checkData($rule, $_POST, $data), '用户名长度5-16');
        $this->assertEqual('snai', $data['user']);


        $_POST['user'] = '123456    ';
        $rule = array(
            'password' => array(
                'len[6,16]' => '密码长度6-16',
                'set[trim]' => '',
                'set_post[sha1,md5]' => '',
            )
        );
        $data = array();
        $this->assertNull($WN->checkData($rule, $_POST, $data));
        $this->assertEqual($data['password'], md5(sha1('123456')));
    }

    public function testValidator() {
        $WN = WoniuLoader::instance();
        $this->assertNull($WN->checkData(array('check' => array('required' => 'check不能为空')), array('check' => 'x')));
        $this->assertNotNull($WN->checkData(array('check' => array('required' => 'check不能为空')), array('check' => '')));
        $this->assertNull($WN->checkData(array('check' => array('mathch[check2]' => 'check值不匹配check2值')), array('check' => 'check', 'check2' => 'check')));
        $this->assertNotNull($WN->checkData(array('check' => array('mathch[check2]' => 'check值不匹配check2值')), array('check' => '', 'check2' => '')));
        $this->assertNotNull($WN->checkData(array('check' => array('mathch[check2]' => 'check值不匹配check2值')), array('check' => 'x', 'check2' => 'check')));
        $this->assertNotNull($WN->checkData(array('check' => array('mathch[check2]' => 'check值不匹配check2值')), array('check' => 'x')));
        $this->assertNull($WN->checkData(array('check' => array('equal[xxx]' => 'check不等于xxx')), array('check' => 'xxx')));
        $this->assertNotNull($WN->checkData(array('check' => array('equal[xxx]' => 'check不等于xxx')), array('check' => '')));
        $this->assertNull($WN->checkData(array('check' => array('min_len[3]' => 'check长度不小于3')), array('check' => 'xxx')));
        $this->assertNotNull($WN->checkData(array('check' => array('min_len[3]' => 'check长度不小于3')), array('check' => '')));
        $this->assertNull($WN->checkData(array('check' => array('max_len[3]' => 'check长度不大于3')), array('check' => 'xxx')));
        $this->assertNotNull($WN->checkData(array('check' => array('max_len[3]' => 'check长度不大于3')), array('check' => 'xxxx')));
        $this->assertNull($WN->checkData(array('check' => array('len[3]' => 'check长度不等于3')), array('check' => 'xxx')));
        $this->assertNotNull($WN->checkData(array('check' => array('len[3]' => 'check长度不等于3')), array('check' => 'xxxx')));
        $this->assertNull($WN->checkData(array('check' => array('range_len[3,5]' => 'check长度3-5')), array('check' => 'xxx')));
        $this->assertNull($WN->checkData(array('check' => array('range_len[3,5]' => 'check长度3-5')), array('check' => 'xxxs')));
        $this->assertNull($WN->checkData(array('check' => array('range_len[3,5]' => 'check长度3-5')), array('check' => 'xxxss')));
        $this->assertNotNull($WN->checkData(array('check' => array('range_len[3]' => 'check长度3-5')), array('check' => 'xx')));
        $this->assertNotNull($WN->checkData(array('check' => array('range_len[3]' => 'check长度3-5')), array('check' => 'xxssss')));
        $this->assertNull($WN->checkData(array('check' => array('min[3]' => 'check最小值为3')), array('check' => '3')));
        $this->assertNotNull($WN->checkData(array('check' => array('min[3]' => 'check最小值为3')), array('check' => '2')));
        $this->assertNotNull($WN->checkData(array('check' => array('min[3]' => 'check最小值为3')), array('check' => 'x')));
        $this->assertNull($WN->checkData(array('check' => array('max[3]' => 'check最小值为3')), array('check' => '3')));
        $this->assertNotNull($WN->checkData(array('check' => array('max[3]' => 'check最小值为3')), array('check' => '4')));
        $this->assertNotNull($WN->checkData(array('check' => array('max[3]' => 'check最小值为3')), array('check' => 'x')));
        $this->assertNull($WN->checkData(array('check' => array('range[3,5]' => 'check值范围为3-5')), array('check' => '3')));
        $this->assertNull($WN->checkData(array('check' => array('range[3,5]' => 'check值范围为3-5')), array('check' => '4')));
        $this->assertNull($WN->checkData(array('check' => array('range[3,5]' => 'check值范围为3-5')), array('check' => '5')));
        $this->assertNotNull($WN->checkData(array('check' => array('range[3,5]' => 'check值范围为3-5')), array('check' => '6')));
        $this->assertNotNull($WN->checkData(array('check' => array('range[3,5]' => 'check值范围为3-5')), array('check' => '2')));
        $this->assertNotNull($WN->checkData(array('check' => array('range[3,5]' => 'check值范围为3-5')), array('check' => 'x')));
        $this->assertNull($WN->checkData(array('check' => array('alpha' => 'check必须是纯字母')), array('check' => 'a')));
        $this->assertNotNull($WN->checkData(array('check' => array('alpha' => 'check必须是纯数字')), array('check' => '4x')));
        $this->assertNull($WN->checkData(array('check' => array('alpha_num' => 'check必须是纯字母和数字')), array('check' => 'a')));
        $this->assertNull($WN->checkData(array('check' => array('alpha_num' => 'check必须是纯字母和数字')), array('check' => 'a1')));
        $this->assertNotNull($WN->checkData(array('check' => array('alpha_num' => 'check必须是纯数字')), array('check' => '4x#')));
        $this->assertNull($WN->checkData(array('check' => array('alpha_dash' => 'check必须纯字母和数字和下划线和-')), array('check' => 'a')));
        $this->assertNull($WN->checkData(array('check' => array('alpha_dash' => 'check必须纯字母和数字和下划线和-')), array('check' => 'a1')));
        $this->assertNull($WN->checkData(array('check' => array('alpha_dash' => 'check必须纯字母和数字和下划线和-')), array('check' => 'a1_')));
        $this->assertNull($WN->checkData(array('check' => array('alpha_dash' => 'check必须纯字母和数字和下划线和-')), array('check' => 'a1_-')));
        $this->assertNotNull($WN->checkData(array('check' => array('alpha_dash' => 'check必须纯字母和数字和下划线和-')), array('check' => '4x#')));
        $this->assertNull($WN->checkData(array('check' => array('int' => 'check必须是整数')), array('check' => '+1')));
        $this->assertNull($WN->checkData(array('check' => array('int' => 'check必须是整数')), array('check' => '-1')));
        $this->assertNull($WN->checkData(array('check' => array('int' => 'check必须是整数')), array('check' => '0')));
        $this->assertNotNull($WN->checkData(array('check' => array('int' => 'check必须是整数')), array('check' => '4x#')));
        $this->assertNotNull($WN->checkData(array('check' => array('int' => 'check必须是整数')), array('check' => '1.0')));
        $this->assertNull($WN->checkData(array('check' => array('float' => 'check必须是小数')), array('check' => '0.1')));
        $this->assertNull($WN->checkData(array('check' => array('float' => 'check必须是小数')), array('check' => '1.1')));
        $this->assertNull($WN->checkData(array('check' => array('float' => 'check必须是小数')), array('check' => '1.000')));
        $this->assertNotNull($WN->checkData(array('check' => array('float' => 'check必须是小数')), array('check' => '4x#')));
        $this->assertNotNull($WN->checkData(array('check' => array('float' => 'check必须是小数')), array('check' => '1')));
        $this->assertNull($WN->checkData(array('check' => array('numeric' => 'check必须是一个数')), array('check' => '-1')));
        $this->assertNull($WN->checkData(array('check' => array('numeric' => 'check必须是一个数')), array('check' => '1')));
        $this->assertNull($WN->checkData(array('check' => array('numeric' => 'check必须是一个数')), array('check' => '1.000')));
        $this->assertNull($WN->checkData(array('check' => array('numeric' => 'check必须是一个数')), array('check' => '4e5')));
        $this->assertNotNull($WN->checkData(array('check' => array('numeric' => 'check必须是一个数')), array('check' => '4x#')));
        $this->assertNull($WN->checkData(array('check' => array('natural' => 'check必须是自然数')), array('check' => '0')));
        $this->assertNull($WN->checkData(array('check' => array('natural' => 'check必须是自然数')), array('check' => '1')));
        $this->assertNotNull($WN->checkData(array('check' => array('natural' => 'check必须是自然数')), array('check' => '-1')));
        $this->assertNull($WN->checkData(array('check' => array('natural_no_zero' => 'check必须是非零自然数')), array('check' => '1')));
        $this->assertNotNull($WN->checkData(array('check' => array('natural_no_zero' => 'check必须是非零自然数')), array('check' => '0')));
        $this->assertNotNull($WN->checkData(array('check' => array('natural_no_zero' => 'check必须是非零自然数')), array('check' => '-1')));
        /**
         * 模式修正符说明:
          i	表示在和模式进行匹配进不区分大小写
          m	将模式视为多行，使用^和$表示任何一行都可以以正则表达式开始或结束
          s	如果没有使用这个模式修正符号，元字符中的"."默认不能表示换行符号,将字符串视为单行
          x	表示模式中的空白忽略不计
          e	正则表达式必须使用在preg_replace替换字符串的函数中时才可以使用(讲这个函数时再说)
          A	以模式字符串开头，相当于元字符^
          Z	以模式字符串结尾，相当于元字符$
          U	正则表达式的特点：就是比较“贪婪”，使用该模式修正符可以取消贪婪模式
         */
        $this->assertNull($WN->checkData(array('check' => array('reg[/^[\]]$/]' => 'check必须是自然数')), array('check' => ']')));
        $this->assertNull($WN->checkData(array('check' => array('reg[/^A$/i]' => 'check必须是a或者A')), array('check' => 'a')));
        $this->assertNotNull($WN->checkData(array('check' => array('reg[/^[\]]$/]' => 'check必须是自然数')), array('check' => 'x')));

        /**
         * 参数默认分割符是逗号, 可以改变这个符号，通过在]后面指定即可。下面的例子使用#分割参数
         */
        $this->assertNull($WN->checkData(array('check' => array('range[3#5]#' => 'check值范围为3-5')), array('check' => '5')));
        $this->assertNotNull($WN->checkData(array('check' => array('range[3#5]#' => 'check值范围为3-5')), array('check' => '6')));
        /**
         * 调用自定义类的各种方法（注意观察参数含义和顺序）
         */
        $this->assertNull($WN->checkData(array('check' => array('Test_validator::callForTest1[3]' => '验证失败')), array('check' => '3')));
        $this->assertNotNull($WN->checkData(array('check' => array('Test_validator::callForTest1[3]' => '验证失败')), array('check' => '4')));
        $this->assertNull($WN->checkData(array('check' => array('Test_validator::callForTest2[3,5]' => '验证失败')), array('check' => '8')));
        $this->assertNotNull($WN->checkData(array('check' => array('Test_validator::callForTest2[3,5]' => '验证失败')), array('check' => '6')));
        $this->assertNull($WN->checkData(array('check' => array('Test_validator::callForTest3' => '验证失败')), array('check' => 'xxx')));
        $this->assertNotNull($WN->checkData(array('check' => array('Test_validator::callForTest3' => '验证失败')), array('check' => '8')));
        $this->assertNull($WN->checkData(array('check' => array('Test_validator::callForTest4' => '验证失败')), array('check' => 'xxx', 'check2' => 'xxx')));
        $this->assertNotNull($WN->checkData(array('check' => array('Test_validator::callForTest4' => '验证失败')), array('check' => 'xxx', 'check2' => '')));
        /**
         * 调用自定义函数（注意观察参数含义和顺序）
         */
        $this->assertNull($WN->checkData(array('check' => array('callForTest' => '验证失败')), array('check' => 1)));
        /**
         * 调用系统函数（注意观察参数含义和顺序）
         */
        $this->assertNotNull($WN->checkData(array('check' => array('callForTest' => '验证失败')), array('check' => 0)));
        $this->assertNull($WN->checkData(array('check' => array('is_int' => 'check不是整数')), array('check' => 1)));
        $this->assertNotNull($WN->checkData(array('check' => array('is_int' => 'check不是整数')), array('check' => '1')));
        $this->assertNotNull($WN->checkData(array('check' => array('trim' => 'check不能为空')), array('check' => '   ')));
    }

    public function callForTest1($val, $data, $arg1) {
        return $arg1 == $val;
    }

    private function callForTest2($val, $data, $arg1, $arg2) {
        return $arg1 + $arg2 == $val;
    }

    public static function callForTest3($val, $data) {
        return $val == 'xxx';
    }

    private static function callForTest4($val, $data) {
        return $val == $data['check2'];
    }

}

function callForTest($val, $data) {
    return $val > 0;
}
