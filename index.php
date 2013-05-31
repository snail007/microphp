<?php
/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		MicroPHP
 * @author		狂奔的蜗牛
 * @email		672308444@163.com
 * @copyright	        Copyright (c) 2013 - 2013, 狂奔的蜗牛, Inc.
 * @link		https://bitbucket.org/snail/microphp/
 * @since		Version 2.0
 * @createdtime       2013-05-31 22:36:55
 */
define('IN_WONIU_APP', TRUE);
//------------------------system config----------------------------
$system['application_folder']='app';
$system['controller_folder']=$system['application_folder'].DIRECTORY_SEPARATOR.'controllers';
$system['model_folder']=$system['application_folder'].DIRECTORY_SEPARATOR.'models';
$system['view_folder']=$system['application_folder'].DIRECTORY_SEPARATOR.'views';
$system['library_folder']=$system['application_folder'].DIRECTORY_SEPARATOR.'library';
$system['helper_folder']=$system['application_folder'].DIRECTORY_SEPARATOR.'helper';
$system['error_page_404']='app/error/error_404.php';
$system['error_page_db']='app/error/error_db.php';
$system['default_controller']='home.welcome';
$system['default_controller_method']='index';
$system['controller_method_prefix']='do';
$system['controller_file_subfix']='.php';
$system['model_file_subfix']='.model.php';
$system['view_file_subfix']='.view.php';
$system['library_file_subfix']='.class.php';
$system['helper_file_subfix']='.php';
$system['controller_method_ucfirst']=TRUE;
$system['autoload_db']=FALSE;
$system['debug']=TRUE;
$system['default_timezone']='PRC';

//-----------------------end system config--------------------------
 
//------------------------database config----------------------------
$woniu_db['active_group'] = 'default';

$woniu_db['default']['dbdriver'] = "mysql";#可用的有mysql,pdo
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
 * $woniu_db['default']['dbdriver'] = "pdo";
 * $woniu_db['default']['hostname'] = 'sqlite:d:/wwwroot/sdb.db';
 * $woniu_db['default']['port'] = '';
 * $woniu_db['default']['username'] = '';
 * $woniu_db['default']['password'] = '';
 * $woniu_db['default']['database'] = '';
 * 2.pdo mysql:
 * $woniu_db['default']['dbdriver'] = "pdo";
 * $woniu_db['default']['hostname'] = 'mysql:host=localhost;port=3306';
 * $woniu_db['default']['port'] = '';
 * $woniu_db['default']['username'] = 'root';
 * $woniu_db['default']['password'] = 'admin';
 * $woniu_db['default']['database'] = 'test';
 * $woniu_db['default']['char_set'] = 'utf8';
 * $woniu_db['default']['dbcollat'] = 'utf8_general_ci';
 */
//-------------------------end database config--------------------------
if (!$system['debug']) {
    error_reporting(0);
} else {
    error_reporting(E_ALL);
}
 



 

 


/* End of file index.php */
include('MicroPHP.php');