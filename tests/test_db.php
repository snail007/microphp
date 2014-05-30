<?php

require_once 'pluginfortest.php';
require_once('simpletest/autorun.php');
/**
 * MicroPHP数据库测试案例
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
 * @createdtime            2013-11-17 17:52:40
 */
class Test_db extends UnitTestCase {

    private $db, $db_table = 'microphp_test_mysql';

    public function setUp() {
        global $system;
        $system['debug'] = TRUE;
        $system['error_manage'] = FALSE;
        $system['log_error'] = FALSE;
        $system['db_debug'] = FALSE;
        WoniuRouter::setConfig($system);
         //重置WoniuLoader::instance()为初始状态
        WoniuLoader::instance(true);
    }

    public function tearDown() {
        global $default;
        WoniuRouter::setConfig($default);
         //重置WoniuLoader::instance()为初始状态
        WoniuLoader::instance(true);
    }

    public function testDatabaseLoader() {
        $this->assertTrue(is_object(WoniuLoader::instance()->database(null, TRUE)));
        $this->assertSame(WoniuLoader::instance()->database(null, TRUE), WoniuLoader::instance()->database(WoniuLoader::$system['db']['mysql'], TRUE));
        $this->assertTrue(is_object(WoniuLoader::instance()->database(null, TRUE)));
        $this->assertNotEqual(WoniuLoader::instance()->database(null, TRUE), WoniuLoader::instance()->database(WoniuLoader::$system['db']['mysqli'], TRUE));
        $this->assertTrue(is_object(WoniuLoader::instance()->database(WoniuLoader::$system['db']['sqlite3'], TRUE)));
        $this->assertNotEqual(WoniuLoader::instance()->database(WoniuLoader::$system['db']['sqlite3'], TRUE), WoniuLoader::instance()->database(WoniuLoader::$system['db']['mysql'], TRUE));
        $db=WoniuLoader::instance()->database();
        $this->assertIsA($db, 'CI_DB_mysql_driver');
        $db1=WoniuLoader::instance()->database(NULL, TRUE);
        $db2=WoniuLoader::instance()->database(NULL, TRUE,TRUE);
        $this->assertClone(WoniuLoader::instance()->database(NULL, TRUE), $db1);
        $this->assertClone($db2, $db1);
        $this->assertReference(WoniuLoader::instance()->database(), $db);
        $this->assertClone($db, WoniuLoader::instance()->database(WoniuLoader::$system['db']['mysql']));
        
    }

    public function testDBDrivers() {
        global $system;
        foreach (array('mysql', 'mysqli', 'pdo_mysql', 'sqlite3') as $db_cfg_group) {
            $system['db']['active_group'] = $db_cfg_group;
            WoniuRouter::setConfig($system);
            $this->createTable();
            $this->curd($db_cfg_group);
            $this->db->simple_query('drop table ' . $this->db_table);
            if ($db_cfg_group == 'sqlite3') {
                @unlink('test.sqlite3');
            }
        }
    }

    public function curd($type) {
        //insert
        $this->assertTrue($this->db->insert($this->db_table, array('id' => '5', 'name' => 'microphp_user06')), "[{$type}]" . '增加数据:%s');
        //modify
        $this->assertTrue($this->db->where(array('id' => 5))->update($this->db_table, array('name' => 'microphp_user006')), "[{$type}]" . '修改数据:%s');
        //delete
        $this->assertTrue($this->db->where(array('id' => 5))->delete($this->db_table), "[{$type}]" . '删除数据:%s');
        //select
        $this->assertEqual(count($this->db
                                ->where(array('id >' => 1))
                                ->where(array('name <>' => 'microphp_user05'))
                                ->where_in('id', array(2, 3, 4))
                                ->group_by('id')
                                ->order_by('id desc')
                                ->get($this->db_table)
                                ->result_array()
                ), 3, "[{$type}]" . '查询数据:%s');
    }

    public function createTable() {
        $instance = WoniuLoader::instance();
        $type = WoniuLoader::$system['db']['active_group'];
        $instance->database(null, false, true);
        $this->db = $instance->db;
        if ($type == 'pdo_mysql') {
            $type = 'pdo';
        }
        $this->assertIsA($this->db, "CI_DB_{$type}_driver");
        if (!$this->db->table_exists($this->db_table)) {
            $sql = 'create table ' . $this->db_table . ' (id int(11),name varchar(15))';
            $data[] = array('id' => '1', 'name' => 'microphp_user01');
            $data[] = array('id' => '2', 'name' => 'microphp_user02');
            $data[] = array('id' => '3', 'name' => 'microphp_user03');
            $data[] = array('id' => '4', 'name' => 'microphp_user04');
            $data[] = array('id' => '4', 'name' => 'microphp_user05');
            $this->assertTrue($this->db->simple_query($sql), "[{$type}] create table ");
            if ('sqlite3' == $type) {
                foreach ($data as $row) {
                    $this->assertTrue($this->db->insert($this->db_table, $row), "[{$type}] insert ");
                }
            } else {
                $this->assertTrue($this->db->insert_batch($this->db_table, $data), "[{$type}] insert_batch ");
            }
        }
    }

}
