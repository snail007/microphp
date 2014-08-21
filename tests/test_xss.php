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
class Test_xss extends UnitTestCase {

    public function testXss() {
        $w = new WoniuInput(); 
        $xss = array(
            //No Filter Evasion
            'javascript<SCRIPT SRC=http://ha.ckers.org/xss.js></SCRIPT>onclick' => '',
            //Image XSS using the JavaScript directive
            '<iframe src=""></iframE><IMG style="color:red;" onclick="alert(\'test\')" SRC="javascript:alert(\'XSS\');">'=>'<IMG SRC="nojavascript...alert(\'XSS\');">',
            //No quotes and no semicolon
            '<IMG SRC=javascript:alert(\'XSS\')>'=>'<IMG SRC=nojavascript...alert(\'XSS\')>',
            //Case insensitive XSS attack vector
            '<IMG onclick="alert(\'\');" SRC=JaVaScRiPt:alert(\'XSS\')>' => '<IMG SRC=nojavascript...alert(\'XSS\')>',
            //HTML entities
            '<IMG SRC=javascript:alert("XSS")>'=>'<IMG SRC=nojavascript...alert("XSS")>',
            //Grave accent obfuscation
            '<IMG SRC=`javascript:alert("RSnake says, \'XSS\'")`>'=>'<IMG SRC=`nojavascript...alert("RSnake says, \'XSS\'")`>',
            //
            
        );
        foreach ($xss as $key => $value) {
            dump($w->xss_clean($key));            
            //$this->assertEqual($w->xss_clean($key), $value);
        }
    }

}
