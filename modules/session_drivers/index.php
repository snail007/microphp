<?php
date_default_timezone_set('PRC');
$system['session_handle'] = array(
    'handle' => '', //mongodb,mysql
    'common' => array(
        'autostart'=>true,
        'cookie_path' => '/',
        'cookie_domain' => '.' . $_SERVER['HTTP_HOST'],
        'session_name' => 'PHPSESSID',
        'lifetime' => 30, // session lifetime in seconds
    ),
    'mongodb' => array(
        'host'=>'127.0.0.1',
        'port'=>27017,
        'user' => 'root',
        'password' => 'local',
        'database' => 'local', // name of MongoDB database
        'collection' => 'session', // name of MongoDB collection
        // persistent related vars
        'persistent' => false, // persistent connection to DB?
        'persistentId' => 'MongoSession', // name of persistent connection
        // whether we're supporting replicaSet
        'replicaSet' => false,
    ),
    'mysql' => array(
        'host' => '10.0.0.251',
        'port'=>3306,
        'user' => 'root',
        'password' => 'snailadmin',
        'database' => 'test',
        'table' => 'session_handler_table',
    ),
);
require 'WoniuSessionHandle.php';
require 'MongodbSessionHandle.php';
require 'MysqlSessionHandle.php';

$session=new MysqlSessionHandle();
$session->start($system['session_handle']);
//session_start();
//if(!isset($_SESSION['user'])){
//    echo 'set';
//    $_SESSION['user']=array('name'=>'用户名4');
//}
var_dump($_SESSION);
