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

    public function tearDown() {
        global $default;
        WoniuRouter::setConfig($default);
    }

    public function setUp() {
        global $system;
        $system['db']['mysql']['dbprefix'] = 'wncms_';
        WoniuRouter::setConfig($system);
    }

    public function testForm() {
        $WN = WoniuLoader::instance();
        $WN->database();
        /**
         * set_post用于设置在验证数据后对数据进行处理的函数或者方法
         * 如果设置了set_post，可以通过第三个参数$data接收数据：$WN->checkData($rule, $_POST, $data)
         * $data是验证通过并经过set_post处理后的数据
         */
        $_POST['user'] = 'snail';
        $_POST['password'] = '123456';
        $rule = array(
            'user' => array(
                'alpha_start' => '用户名必须是字母开头',
                'alpha_dash' => '用户名必须是数字、字母、下划线和-组成',
                'range_len[5,16]' => '用户名长度5-16',
            ),
            'password' => array(
                'range_len[6,16]' => '密码长度6-16',
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
                'range_len[5,16]' => '用户名长度5-16',
                'set[trim]' => '',
            ),
        );
        $data = array();
        $this->assertEqual($WN->checkData($rule, $_POST, $data), '用户名长度5-16');
        $this->assertEqual('snai', $data['user']);
        /**
         * set用于设置在验证数据前对数据进行处理的函数或者方法
         * set_post用于设置在验证数据后对数据进行处理的函数或者方法
         * 如果设置了set，数据在验证的时候验证的是处理过的数据
         * 如果设置了set_post，可以通过第三个参数$data接收数据：$WN->checkData($rule, $_POST, $data)，$data是验证通过并经过set_post处理后的数据
         * set和set_post后面是一个或者多个函数或者方法，多个逗号分割
         * 注意：
         * 1.无论是函数或者方法都必须有一个字符串返回
         * 2.如果是系统函数，系统会传递当前值给系统函数，因此系统函数必须是至少接受一个字符串参数
         * 3.如果是自定义的函数，系统会传递当前值和全部数据给自定义的函数，因此自定义函数可以接收两个参数第一个是值，第二个是全部数据$data
         * 4.如果是类的方法写法是：类名称::方法名 （方法静态动态都可以，public，private，都可以）
         */
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
        /**
         * 添加数据，users表的用户名uname不能重复
         */
        $_POST['uname'] = 'admin';
        $rule = array(
            'uname' => array(
                'alpha_start' => '用户名必须是字母开头',
                'alpha_dash' => '用户名必须是数字、字母、下划线和-组成',
                'range_len[5,16]' => '用户名长度5-16',
                'set[trim]' => '',
                'unique[users.uname]' => '用户名已经存在，不能添加'
            )
        );
        $data = array();
        $this->assertEqual($WN->checkData($rule, $_POST, $data), '用户名已经存在，不能添加');

        /**
         * 修改user_id为1的数据，users表的用户名uname不能重复，修改的时候除了user_id为1的记录的uname，用户名不能和其它记录的重复。
         */
        $_POST['uname'] = 'admin';
        $_POST['user_id'] = 1;
        $rule = array(
            'user_id' => array(
                'natural_no_zero' => '用户ID必须是自然数'
            ),
            'uname' => array(
                'alpha_start' => '用户名必须是字母开头',
                'alpha_dash' => '用户名必须是数字、字母、下划线和-组成',
                'range_len[5,16]' => '用户名长度5-16',
                'set[trim]' => '',
                'unique[users.uname,user_id:' . $_POST['user_id'] . ']' => '用户名已经存在，不能修改'
            )
        );
        $data = array();
        $this->assertNull($WN->checkData($rule, $_POST, $data));
        $_POST['uname'] = 'admina';
        $this->assertEqual($WN->checkData($rule, $_POST, $data), '用户名已经存在，不能修改');
    }

    public function testValidator() {
        $WN = WoniuLoader::instance();
        $WN->database();
        $this->assertNull($WN->checkData(array('check' => array('required' => 'check不能为空')), array('check' => 'x')));
        $this->assertNotNull($WN->checkData(array('check' => array('required' => 'check不能为空')), array('check' => '')));
        $this->assertNull($WN->checkData(array('check' => array('match[check2]' => 'check值不匹配check2值')), array('check' => 'check', 'check2' => 'check')));
        $this->assertNotNull($WN->checkData(array('check' => array('match[check2]' => 'check值不匹配check2值')), array('check' => '', 'check2' => '')));
        $this->assertNotNull($WN->checkData(array('check' => array('match[check2]' => 'check值不匹配check2值')), array('check' => 'x', 'check2' => 'check')));
        $this->assertNotNull($WN->checkData(array('check' => array('match[check2]' => 'check值不匹配check2值')), array('check' => 'x')));
        $this->assertNull($WN->checkData(array('check' => array('equal[xxx]' => 'check不等于xxx')), array('check' => 'xxx')));
        $this->assertNotNull($WN->checkData(array('check' => array('equal[xxx]' => 'check不等于xxx')), array('check' => '')));
        $this->assertNull($WN->checkData(array('check' => array('enum[1,a,b]' => 'check只能是1,a,b之一')), array('check' => 'b')));
        $this->assertNotNull($WN->checkData(array('check' => array('enum[1,a,b]' => 'check只能是1,a,b之一')), array('check' => 'xxx')));
        $this->assertNull($WN->checkData(array('check' => array('unique[users.uname]' => 'admin已经存在，不能添加')), array('check' => 'xxx')));
        $this->assertNotNull($WN->checkData(array('check' => array('unique[users.uname]' => 'admin已经存在,不能修改')), array('check' => 'admin')));
        $this->assertNull($WN->checkData(array('check' => array('unique[users.uname,user_id:1]' => 'admin已经存在,不能修改')), array('check' => 'admin')));
        $this->assertNotNull($WN->checkData(array('check' => array('unique[users.uname,user_id:1]' => 'admina已经存在,不能修改')), array('check' => 'admina')));
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
        
        $this->assertNull($WN->checkData(array('check' => array('email' => 'check必须是一个邮箱')), array('check' => 'a@a.com')));
        $this->assertNotNull($WN->checkData(array('check' => array('email' => 'check必须是一个邮箱')), array('check' => '')));
        $this->assertNotNull($WN->checkData(array('check' => array('email' => 'check必须是一个邮箱')), array('check' => 'aa.com')));
        
        $this->assertNull($WN->checkData(array('check' => array('email[true]' => 'check必须是一个邮箱')), array('check' => 'a@a.com')));
        $this->assertNull($WN->checkData(array('check' => array('email[true]' => 'check必须是一个邮箱')), array('check' => '')));
        $this->assertNotNull($WN->checkData(array('check' => array('email[true]' => 'check必须是一个邮箱')), array('check' => 'aa.com')));
        
        $this->assertNull($WN->checkData(array('check' => array('url' => 'check必须是网址')), array('check' => 'http://a.com/xxx')));
        $this->assertNull($WN->checkData(array('check' => array('url' => 'check必须是网址')), array('check' => 'https://a.com/yyy&kkkk')));
        $this->assertNotNull($WN->checkData(array('check' => array('url' => 'check必须是网址')), array('check' => 'aa.com')));
        
        $this->assertNull($WN->checkData(array('check' => array('url[true]' => 'check必须是网址')), array('check' => 'http://a.com/xxx')));
        $this->assertNull($WN->checkData(array('check' => array('url[true]' => 'check必须是网址')), array('check' => 'https://a.com/yyy&kkkk')));
        $this->assertNull($WN->checkData(array('check' => array('url[true]' => 'check必须是网址')), array('check' => '')));
        $this->assertNotNull($WN->checkData(array('check' => array('url[true]' => 'check必须是网址')), array('check' => 'aa.com')));
        
        
        $this->assertNull($WN->checkData(array('check' => array('qq' => 'check必须是一个QQ号')), array('check' => '12345')));
        $this->assertNotNull($WN->checkData(array('check' => array('qq' => 'check必须是一个QQ号')), array('check' => '')));
        $this->assertNotNull($WN->checkData(array('check' => array('qq' => 'check必须是一个QQ号')), array('check' => '312312a')));
        
        $this->assertNull($WN->checkData(array('check' => array('qq[true]' => 'check必须是一个QQ号')), array('check' => '12345')));
        $this->assertNull($WN->checkData(array('check' => array('qq[true]' => 'check必须是一个QQ号')), array('check' => '')));
        $this->assertNotNull($WN->checkData(array('check' => array('qq[true]' => 'check必须是一个QQ号')), array('check' => '312312a')));
        
        $this->assertNull($WN->checkData(array('check' => array('phone' => 'check必须是一个电话号，包含区号')), array('check' => '010-59876744')));
        $this->assertNotNull($WN->checkData(array('check' => array('phone' => 'check必须是一个电话号，包含区号')), array('check' => '0105987674')));
        $this->assertNotNull($WN->checkData(array('check' => array('phone' => 'check必须是一个电话号，包含区号')), array('check' => '')));
        
        $this->assertNull($WN->checkData(array('check' => array('phone[true]' => 'check必须是一个电话号，包含区号')), array('check' => '0105-9876744')));
        $this->assertNotNull($WN->checkData(array('check' => array('phone[true]' => 'check必须是一个电话号，包含区号')), array('check' => '0105987674')));
        $this->assertNull($WN->checkData(array('check' => array('phone[true]' => 'check必须是一个电话号，包含区号')), array('check' => '')));
        
        $this->assertNull($WN->checkData(array('check' => array('mobile' => 'check必须是一个手机号')), array('check' => '13709876567')));
        $this->assertNotNull($WN->checkData(array('check' => array('mobile' => 'check必须是一个手机号')), array('check' => '11709876567')));
        $this->assertNotNull($WN->checkData(array('check' => array('mobile' => 'check必须是一个手机号')), array('check' => '')));
        
        $this->assertNull($WN->checkData(array('check' => array('mobile[true]' => 'check必须是一个手机号')), array('check' => '13709876567')));
        $this->assertNotNull($WN->checkData(array('check' => array('mobile[true]' => 'check必须是一个手机号')), array('check' => '11709876567')));
        $this->assertNull($WN->checkData(array('check' => array('mobile[true]' => 'check必须是一个手机号')), array('check' => '')));
        
        $this->assertNull($WN->checkData(array('check' => array('zipcode' => 'check必须是一个邮编号')), array('check' => '464300')));
        $this->assertNotNull($WN->checkData(array('check' => array('zipcode' => 'check必须是一个邮编号')), array('check' => '4643000')));
        
        $this->assertNull($WN->checkData(array('check' => array('idcard' => 'check必须是一个身份证号')), array('check' => '130527197801166974')));
        $this->assertNotNull($WN->checkData(array('check' => array('idcard' => 'check必须是一个身份证号')), array('check' => '')));
        $this->assertNotNull($WN->checkData(array('check' => array('idcard' => 'check必须是一个身份证号')), array('check' => '13052719780116697')));
        
        $this->assertNull($WN->checkData(array('check' => array('idcard[true]' => 'check必须是一个身份证号')), array('check' => '130527197801166974')));
        $this->assertNull($WN->checkData(array('check' => array('idcard[true]' => 'check必须是一个身份证号')), array('check' => '')));
        $this->assertNotNull($WN->checkData(array('check' => array('idcard[true]' => 'check必须是一个身份证号')), array('check' => '13052719780116697')));
        
        $this->assertNull($WN->checkData(array('check' => array('ip' => 'check必须是一个IP')), array('check' => '127.0.0.1')));
        $this->assertNotNull($WN->checkData(array('check' => array('ip' => 'check必须是一个IP')), array('check' => '')));
        $this->assertNotNull($WN->checkData(array('check' => array('ip' => 'check必须是一个IP')), array('check' => '127.0.0.256')));
        
        $this->assertNull($WN->checkData(array('check' => array('ip[true]' => 'check必须是一个IP')), array('check' => '127.0.0.1')));
        $this->assertNull($WN->checkData(array('check' => array('ip[true]' => 'check必须是一个IP')), array('check' => '')));
        $this->assertNotNull($WN->checkData(array('check' => array('ip[true]' => 'check必须是一个IP')), array('check' => '127.0.0.256')));
        
        $this->assertNull($WN->checkData(array('check' => array('chs' => 'check必须是纯汉字')), array('check' => '必须是纯汉字')));
        $this->assertNotNull($WN->checkData(array('check' => array('chs[false]' => 'check必须是纯汉字')), array('check' => 'a')));
        $this->assertNull($WN->checkData(array('check' => array('chs[true]' => 'check必须是纯汉字')), array('check' => '')));
        $this->assertNotNull($WN->checkData(array('check' => array('chs[true]' => 'check必须是纯汉字')), array('check' => 'a')));
        $this->assertNull($WN->checkData(array('check' => array('chs[true]' => 'check必须是纯汉字')), array('check' => '汉字')));
        $this->assertNull($WN->checkData(array('check' => array('chs[false,2,]' => 'check必须是至少2个纯汉字')), array('check' => '必须')));
        $this->assertNotNull($WN->checkData(array('check' => array('chs[false,2,]' => 'check必须是至少2个纯汉字')), array('check' => '须')));
        $this->assertNotNull($WN->checkData(array('check' => array('chs[false,2,]' => 'check必须是至少2个纯汉字')), array('check' => '')));
        $this->assertNull($WN->checkData(array('check' => array('chs[false,2]' => 'check必须是2个纯汉字')), array('check' => '必须')));
        $this->assertNotNull($WN->checkData(array('check' => array('chs[false,2]' => 'check必须是2个纯汉字')), array('check' => '必')));
        $this->assertNotNull($WN->checkData(array('check' => array('chs[false,2]' => 'check必须是2个纯汉字')), array('check' => '必必须')));
        $this->assertNull($WN->checkData(array('check' => array('chs[false,2,3]' => 'check必须是2-3个纯汉字')), array('check' => '必必须')));
        $this->assertNull($WN->checkData(array('check' => array('chs[true,2,3]' => 'check必须是2-3个纯汉字')), array('check' => '')));
        $this->assertNotNull($WN->checkData(array('check' => array('chs[false,2,3]' => 'check必须是2-3个纯汉字')), array('check' => '必')));
        
        $this->assertNull($WN->checkData(array('check' => array('date' => 'check日期格式错误')), array('check' => '2012-12-12')));
        $this->assertNotNull($WN->checkData(array('check' => array('date' => 'check日期格式错误')), array('check' => '2012-13-12')));
        $this->assertNotNull($WN->checkData(array('check' => array('date' => 'check日期格式错误')), array('check' => '')));
        
        $this->assertNull($WN->checkData(array('check' => array('date[true]' => 'check日期格式错误')), array('check' => '2012-12-12')));
        $this->assertNotNull($WN->checkData(array('check' => array('date[true]' => 'check日期格式错误')), array('check' => '2012-13-12')));
        $this->assertNull($WN->checkData(array('check' => array('date[true]' => 'check日期格式错误')), array('check' => '')));
        
        $this->assertNull($WN->checkData(array('check' => array('time' => 'check日期格式错误')), array('check' => '12:12:10')));
        $this->assertNotNull($WN->checkData(array('check' => array('time' => 'check日期格式错误')), array('check' => '24:12:1')));
        $this->assertNotNull($WN->checkData(array('check' => array('time' => 'check日期格式错误')), array('check' => '')));
        
        $this->assertNull($WN->checkData(array('check' => array('time[true]' => 'check日期格式错误')), array('check' => '12:12:12')));
        $this->assertNotNull($WN->checkData(array('check' => array('time[true]' => 'check日期格式错误')), array('check' => '24:12:12')));
        $this->assertNull($WN->checkData(array('check' => array('time[true]' => 'check日期格式错误')), array('check' => '')));
        
        $this->assertNull($WN->checkData(array('check' => array('datetime' => 'check日期格式错误')), array('check' => '2012-12-12 12:12:12')));
        $this->assertNotNull($WN->checkData(array('check' => array('datetime' => 'check日期格式错误')), array('check' => '2012-12-12 24:12:1')));
        $this->assertNotNull($WN->checkData(array('check' => array('datetime' => 'check日期格式错误')), array('check' => '')));
        
        $this->assertNull($WN->checkData(array('check' => array('datetime[true]' => 'check日期格式错误')), array('check' => '2012-12-12 12:12:12')));
        $this->assertNotNull($WN->checkData(array('check' => array('datetime[true]' => 'check日期格式错误')), array('check' => '2012-12-12 24:12:12')));
        $this->assertNull($WN->checkData(array('check' => array('datetime[true]' => 'check日期格式错误')), array('check' => '')));
        
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
        $this->assertNotNull($WN->checkData(array('check' => array('reg[/^[1-9]{1,}$/]' => 'check必须是自然数')), array('check' => 'x')));

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
