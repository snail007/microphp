<?php

require_once 'pluginfortest.php';
require_once('simpletest/autorun.php');
define('IN_ALL_TESTS', true);

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
 * @createdtime            2013-11-17 17:53:59
 */
class AllTests extends TestSuite {

    public function AllTests() {
        $ignore_list = array('test_cache.php', 'test_xss.php');
        $this->TestSuite('All tests');
        $dir = dir(TEST_ROOT);
        while ($file = $dir->read()) {
            if (stripos($file, 'test_') === 0) {
                if (!in_array($file, $ignore_list)) {
                    echo "<b style='color:darkgreen'>$file</b><br/>";
                    $this->addFile($file);
                } else {
                    echo "ignore:$file<br/>";
                }
            }
        }
        $dir->close();
        if (isset($_GET['release'])) {
            //unlink('release');
        }
    }
 
}
