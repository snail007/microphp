<?php
/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		MicroPHP
 * @author		狂奔的蜗牛
 * @email		672308444@163.com
 * @copyright	        Copyright (c) 2008 - 2013, 狂奔的蜗牛, Inc.
 * @link		https://bitbucket.org/snail/microphp/
 * @since		Version 1.0
 * @filesource
 */
define('IN_WONIU_APP', TRUE);
//------------------------system config----------------------------
$system['application_folder']='../app';
$system['controller_folder']=$system['application_folder'].DIRECTORY_SEPARATOR.'controllers';
$system['model_folder']=$system['application_folder'].DIRECTORY_SEPARATOR.'models';
$system['view_folder']=$system['application_folder'].DIRECTORY_SEPARATOR.'views';
$system['library_folder']=$system['application_folder'].DIRECTORY_SEPARATOR.'library';
$system['helper_folder']=$system['application_folder'].DIRECTORY_SEPARATOR.'helper';
$system['error_page_404']='../app/error/error_404.php';
$system['error_page_db']='../app/error/error_db.php';
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
$db['active_group'] = 'default';

$db['default']['hostname'] = 'localhost';
$db['default']['port'] = 3306;
$db['default']['username'] = 'root';
$db['default']['password'] = 'admin';
$db['default']['database'] = 'ectest';
$db['default']['dbprefix'] = 'ecm_';
$db['default']['pconnect'] = TRUE;
$db['default']['db_debug'] = TRUE;
$db['default']['char_set'] = 'utf8';
$db['default']['dbcollat'] = 'utf8_general_ci';
$db['default']['swap_pre'] = '';
$db['default']['autoinit'] = TRUE;
$db['default']['stricton'] = FALSE;

//-------------------------end database config--------------------------
if (!$system['debug']) {
    error_reporting(0);
} else {
    error_reporting(E_ALL);
}
include('Helper.php');
include('DB_driver.php');
include('Router.php'); 
include('Loader.php'); 
include('Controller.php');
include('Model.php'); 
Router::loadClass();

/* End of file index.php */