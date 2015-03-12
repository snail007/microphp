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
class test_input extends UnitTestCase {

    public function test_gp() {
	$_GET['get'] = 22;
	$_POST['get'] = 33;
	$this->assertEqual(22, WoniuInput::get_post('get'));
	$this->assertEqual(33, WoniuInput::post_get('get'));
	$this->assertEqual(22, WoniuInput::get_post('get',''));
	$this->assertEqual(33, WoniuInput::post_get('get',77));
	$this->assertEqual(44, WoniuInput::post_get('none', 44));
	$this->assertEqual(55, WoniuInput::get_post('none', 55));
	$this->assertEqual('', WoniuInput::get_post('none', ''));
	$this->assertEqual('', WoniuInput::post_get('none', ''));
	//int
	$_POST['int_okay'] = 11;
	$_POST['int_err'] = 15;
	$_GET['int_okay'] = 1;
	$_GET['int_err'] = 5;
	$_GET['int_error'] = 5.9;
	$_POST['int_error'] = 5.9;

	$this->assertEqual(1, WoniuInput::get_int('int_okay'));
	$this->assertNull(WoniuInput::get_int('int_error'));
	$this->assertNull(WoniuInput::get_int('int_err', null, 3));
	$this->assertNull(WoniuInput::get_int('int_err', 6));
	$this->assertEqual(2, WoniuInput::get_int('int_err', 1, 3, 2));

	$this->assertEqual(11, WoniuInput::post_int('int_okay'));
	$this->assertNull(WoniuInput::post_int('int_error'));
	$this->assertNull(WoniuInput::post_int('int_err', null, 13));
	$this->assertNull(WoniuInput::post_int('int_err', 16));
	$this->assertEqual(12, WoniuInput::post_int('int_err', 11, 13, 12));

	$this->assertEqual(1, WoniuInput::get_post_int('int_okay'));
	$this->assertNull(WoniuInput::get_post_int('int_error'));
	$this->assertNull(WoniuInput::get_post_int('int_err', null, 3));
	$this->assertNull(WoniuInput::get_post_int('int_err', 6));
	$this->assertEqual(2, WoniuInput::get_post_int('int_err', 1, 3, 2));

	$this->assertEqual(11, WoniuInput::post_get_int('int_okay'));
	$this->assertNull(WoniuInput::post_get_int('int_error'));
	$this->assertNull(WoniuInput::post_get_int('int_err', null, 13));
	$this->assertNull(WoniuInput::post_get_int('int_err', 16));
	$this->assertEqual(12, WoniuInput::post_get_int('int_err', 11, 13, 12));

	//date
	$_POST['date_okay'] = '2012-10-10';
	$_POST['date_err'] = '2010-10-20';
	$_GET['date_okay'] = '2012-10-10';
	$_GET['date_err'] = '2010-10-10';
	$_GET['date_error'] = '2012-10';
	$_POST['date_error'] = '2012-10';
	$this->assertEqual('2012-10-10', WoniuInput::get_date('date_okay'));
	$this->assertNull(WoniuInput::get_date('date_error'));
	$this->assertNull(WoniuInput::get_date('date_err', null, '2009-10-15'));
	$this->assertNull(WoniuInput::get_date('date_err', '2012-11-10'));
	$this->assertEqual('2012-10-11', WoniuInput::get_date('date_err', '2012-09-10', '2012-10-10', '2012-10-11'));

	$this->assertEqual('2012-10-10', WoniuInput::post_date('date_okay'));
	$this->assertNull(WoniuInput::post_date('date_error'));
	$this->assertNull(WoniuInput::post_date('date_err', null, '2009-10-15'));
	$this->assertNull(WoniuInput::post_date('date_err', '2012-11-10'));
	$this->assertEqual('2012-10-11', WoniuInput::post_date('date_err', '2012-09-10', '2012-10-10', '2012-10-11'));

	$this->assertEqual('2012-10-10', WoniuInput::get_post_date('date_okay'));
	$this->assertNull(WoniuInput::get_post_date('date_error'));
	$this->assertNull(WoniuInput::get_post_date('date_err', null, '2009-10-15'));
	$this->assertNull(WoniuInput::get_post_date('date_err', '2012-11-10'));
	$this->assertEqual('2012-10-11', WoniuInput::get_post_date('date_err', '2012-09-10', '2012-10-10', '2012-10-11'));

	$this->assertEqual('2012-10-10', WoniuInput::post_get_date('date_okay'));
	$this->assertNull(WoniuInput::post_get_date('date_error'));
	$this->assertNull(WoniuInput::post_get_date('date_err', null, '2009-10-15'));
	$this->assertNull(WoniuInput::post_get_date('date_err', '2012-11-10'));
	$this->assertEqual('2012-10-11', WoniuInput::post_get_date('date_err', '2012-09-10', '2012-10-10', '2012-10-11'));

	//time
	$_POST['time_okay'] = '12:10:10';
	$_POST['time_err'] = '10:10:20';
	$_GET['time_okay'] = '10:10:10';
	$_GET['time_err'] = '10:10:10';
	$_GET['time_error'] = '12.10';
	$_POST['time_error'] = '12.10';
	$this->assertEqual('10:10:10', WoniuInput::get_time('time_okay'));
	$this->assertNull(WoniuInput::get_time('time_error'));
	$this->assertNull(WoniuInput::get_time('time_err', null, '09:10:15'));
	$this->assertNull(WoniuInput::get_time('time_err', '12:11:10'));
	$this->assertEqual('12:10:11', WoniuInput::get_time('time_err', '12:09:10', '12:10:10', '12:10:11'));

	$this->assertEqual('12:10:10', WoniuInput::post_time('time_okay'));
	$this->assertNull(WoniuInput::post_time('time_error'));
	$this->assertNull(WoniuInput::post_time('time_err', null, '09:10:15'));
	$this->assertNull(WoniuInput::post_time('time_err', '12:11:10'));
	$this->assertEqual('12:10:11', WoniuInput::post_time('time_err', '12:09:10', '12:10:10', '12:10:11'));

	$this->assertEqual('10:10:10', WoniuInput::get_post_time('time_okay'));
	$this->assertNull(WoniuInput::get_post_time('time_error'));
	$this->assertNull(WoniuInput::get_post_time('time_err', null, '09:10:15'));
	$this->assertNull(WoniuInput::get_post_time('time_err', '12:11:10'));
	$this->assertEqual('12:10:11', WoniuInput::get_post_time('time_err', '12:09:10', '12:10:10', '12:10:11'));

	$this->assertEqual('12:10:10', WoniuInput::post_get_time('time_okay'));
	$this->assertNull(WoniuInput::post_get_time('time_error'));
	$this->assertNull(WoniuInput::post_get_time('time_err', null, '09:10:15'));
	$this->assertNull(WoniuInput::post_get_time('time_err', '12:11:10'));
	$this->assertEqual('12:10:11', WoniuInput::post_get_time('time_err', '12:09:10', '12:10:10', '12:10:11'));


	//datetime
	$_POST['datetime_okay'] = '2012-10-10 12:10:10';
	$_POST['datetime_err'] = '2012-10-10 10:10:20';
	$_GET['datetime_okay'] = '2012-10-10 10:10:10';
	$_GET['datetime_err'] = '2012-10-10 10:10:10';
	$_GET['datetime_error'] = '2012-10-10 12.10';
	$_POST['datetime_error'] = '2012-10-10 12.10';
	$this->assertEqual('2012-10-10 10:10:10', WoniuInput::get_datetime('datetime_okay'));
	$this->assertNull(WoniuInput::get_datetime('datetime_error'));
	$this->assertNull(WoniuInput::get_datetime('datetime_err', null, '2012-10-10 09:10:15'));
	$this->assertNull(WoniuInput::get_datetime('datetime_err', '2012-10-10 12:11:10'));
	$this->assertEqual('2012-10-10 12:10:11', WoniuInput::get_datetime('datetime_err', '2012-10-10 12:09:10', '2012-10-10 12:10:10', '2012-10-10 12:10:11'));

	$this->assertEqual('2012-10-10 12:10:10', WoniuInput::post_datetime('datetime_okay'));
	$this->assertNull(WoniuInput::post_datetime('datetime_error'));
	$this->assertNull(WoniuInput::post_datetime('datetime_err', null, '2012-10-10 09:10:15'));
	$this->assertNull(WoniuInput::post_datetime('datetime_err', '2012-10-10 12:11:10'));
	$this->assertEqual('2012-10-10 12:10:11', WoniuInput::post_datetime('datetime_err', '2012-10-10 12:09:10', '2012-10-10 12:10:10', '2012-10-10 12:10:11'));

	$this->assertEqual('2012-10-10 10:10:10', WoniuInput::get_post_datetime('datetime_okay'));
	$this->assertNull(WoniuInput::get_post_datetime('datetime_error'));
	$this->assertNull(WoniuInput::get_post_datetime('datetime_err', null, '2012-10-10 09:10:15'));
	$this->assertNull(WoniuInput::get_post_datetime('datetime_err', '2012-10-10 12:11:10'));
	$this->assertEqual('2012-10-10 12:10:11', WoniuInput::get_post_datetime('datetime_err', '2012-10-10 12:09:10', '2012-10-10 12:10:10', '2012-10-10 12:10:11'));

	$this->assertEqual('2012-10-10 12:10:10', WoniuInput::post_get_datetime('datetime_okay'));
	$this->assertNull(WoniuInput::post_get_datetime('datetime_error'));
	$this->assertNull(WoniuInput::post_get_datetime('datetime_err', null, '2012-10-10 09:10:15'));
	$this->assertNull(WoniuInput::post_get_datetime('datetime_err', '2012-10-10 12:11:10'));
	$this->assertEqual('2012-10-10 12:10:11', WoniuInput::post_get_datetime('datetime_err', '2012-10-10 12:09:10', '2012-10-10 12:10:10', '2012-10-10 12:10:11'));

	//rule
	$_POST['rule_okay'] = 2;
	$_GET['rule_okay'] = 1;
	$_GET['rule_error'] = -9;
	$_POST['rule_error'] = -10;
	$this->assertEqual(1, WoniuInput::get_rule(WoniuRule::natural(), 'rule_okay'));
	$this->assertNull(WoniuInput::get_rule(WoniuRule::natural(), 'rule_error'));
	$this->assertEqual(1, WoniuInput::get_rule(array(WoniuRule::natural(), WoniuRule::range(0, 2)), 'rule_okay'));
	$this->assertNull(WoniuInput::get_rule(array(WoniuRule::natural(), WoniuRule::range(0, 2)), 'rule_error'));

	$this->assertEqual(2, WoniuInput::post_rule(WoniuRule::natural(), 'rule_okay'));
	$this->assertNull(WoniuInput::post_rule(WoniuRule::natural(), 'rule_error'));
	$this->assertEqual(2, WoniuInput::post_rule(array(WoniuRule::natural(), WoniuRule::range(0, 2)), 'rule_okay'));
	$this->assertNull(WoniuInput::post_rule(array(WoniuRule::natural(), WoniuRule::range(0, 2)), 'rule_error'));

	$this->assertEqual(1, WoniuInput::get_post_rule(WoniuRule::natural(), 'rule_okay'));
	$this->assertNull(WoniuInput::get_post_rule(WoniuRule::natural(), 'rule_error'));
	$this->assertEqual(1, WoniuInput::get_post_rule(array(WoniuRule::natural(), WoniuRule::range(0, 2)), 'rule_okay'));
	$this->assertNull(WoniuInput::get_post_rule(array(WoniuRule::natural(), WoniuRule::range(0, 2)), 'rule_error'));

	$this->assertEqual(2, WoniuInput::post_get_rule(WoniuRule::natural(), 'rule_okay'));
	$this->assertNull(WoniuInput::post_get_rule(WoniuRule::natural(), 'rule_error'));
	$this->assertEqual(2, WoniuInput::post_get_rule(array(WoniuRule::natural(), WoniuRule::range(0, 2)), 'rule_okay'));
	$this->assertNull(WoniuInput::post_get_rule(array(WoniuRule::natural(), WoniuRule::range(0, 2)), 'rule_error'));
    }

}
