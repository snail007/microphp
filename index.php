<?php

/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		MicroPHP
 * @author		狂奔的蜗牛
 * @email		672308444@163.com
 * @copyright          Copyright (c) 2013 - 2013, 狂奔的蜗牛, Inc.
 * @link		http://git.oschina.net/snail/microphp
 * @since		Version 2.1.12
 * @createdtime       2013-10-30 13:30:44
 */
define('IN_WONIU_APP', TRUE);
define('WDS', DIRECTORY_SEPARATOR);
//------------------------system config----------------------------
$system['application_folder'] = 'application';
$system['controller_folder'] = $system['application_folder'] . WDS . 'controllers';
$system['model_folder'] = $system['application_folder'] . WDS . 'models';
$system['view_folder'] = $system['application_folder'] . WDS . 'views';
$system['library_folder'] = $system['application_folder'] . WDS . 'library';
$system['helper_folder'] = $system['application_folder'] . WDS . 'helper';
$system['error_page_404'] = 'application/error/error_404.php';
$system['error_page_50x'] = 'application/error/error_50x.php';
$system['error_page_db'] = 'application/error/error_db.php';
$system['default_controller'] = 'welcome';
$system['default_controller_method'] = 'index';
$system['controller_method_prefix'] = 'do';
$system['controller_file_subfix'] = '.php';
$system['model_file_subfix'] = '.model.php';
$system['view_file_subfix'] = '.view.php';
$system['library_file_subfix'] = '.class.php';
$system['helper_file_subfix'] = '.php';
$system['helper_file_autoload'] = array(); //array($item);  $item:such as html etc.
$system['library_file_autoload'] = array(); //array($item); $item:such as ImageTool or array('ImageTool'=>'image') etc.
$system['models_file_autoload']=array();//array($item); $item:such as UserModel or array('UserModel'=>'user') etc.
$system['cache_dirname'] = 'cache';
$system['controller_method_ucfirst'] = TRUE;
$system['autoload_db'] = FALSE;
$system['debug'] = TRUE;
$system['default_timezone'] = 'PRC';
$system['route']=array(
    "/^welcome\\/?(.*)$/u"=>'welcome.ajax/$1'
);
//-----------------------end system config--------------------------
//------------------------database config----------------------------
$woniu_db['active_group'] = 'default';

$woniu_db['default']['dbdriver'] = "mysql"; #可用的有mysql,mysqli,pdo,sqlite3,配置见下面
$woniu_db['default']['hostname'] = 'localhost';
$woniu_db['default']['port'] = '3306';
$woniu_db['default']['username'] = 'root';
$woniu_db['default']['password'] = 'admin';
$woniu_db['default']['database'] = 'test';
$woniu_db['default']['dbprefix'] = '';
$woniu_db['default']['pconnect'] = TRUE;
$woniu_db['default']['db_debug'] = TRUE;
$woniu_db['default']['char_set'] = 'utf8';
$woniu_db['default']['dbcollat'] = 'utf8_general_ci';
$woniu_db['default']['swap_pre'] = '';
$woniu_db['default']['autoinit'] = TRUE;
$woniu_db['default']['stricton'] = FALSE;


/*
 * PDO database config demo
 * 1.pdo sqlite3
  $woniu_db['default']['dbdriver'] = "sqlite3";
  $woniu_db['default']['database'] = 'sqlite:d:/wwwroot/sdb.db';
  $woniu_db['default']['dbprefix'] = '';
  $woniu_db['default']['db_debug'] = TRUE;
  $woniu_db['default']['char_set'] = 'utf8';
  $woniu_db['default']['dbcollat'] = 'utf8_general_ci';
  $woniu_db['default']['swap_pre'] = '';
  $woniu_db['default']['autoinit'] = TRUE;
  $woniu_db['default']['stricton'] = FALSE;
 * 2.pdo mysql:
  $woniu_db['default']['dbdriver'] = "pdo";
  $woniu_db['default']['hostname'] = 'mysql:host=localhost;port=3306';
  $woniu_db['default']['username'] = 'root';
  $woniu_db['default']['password'] = 'admin';
  $woniu_db['default']['database'] = 'test';
  $woniu_db['default']['dbprefix'] = '';
  $woniu_db['default']['char_set'] = 'utf8';
  $woniu_db['default']['dbcollat'] = 'utf8_general_ci';
  $woniu_db['default']['swap_pre'] = '';
  $woniu_db['default']['autoinit'] = TRUE;
  $woniu_db['default']['stricton'] = FALSE;
 */
//-------------------------end database config--------------------------













/* End of file index.php */
include('MicroPHP.php');