<?php


/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		MicroPHP
 * @author		狂奔的蜗牛
 * @email		672308444@163.com
 * @copyright           Copyright (c) 2013 - 2013, 狂奔的蜗牛, Inc.
 * @link		http://git.oschina.net/snail/microphp
 * @since		Version 2.2.0
 * @createdtime         2013-11-12 15:04:43
 */
define('IN_WONIU_APP', TRUE);
define('WDS', DIRECTORY_SEPARATOR);
/**
 * --------------------系统配置-------------------------
 */
/**
 * 程序文件夹路径名称，也就是所有的程序文件比如控制器文件夹，
 * 模型文件夹，视图文件夹等所在的文件夹名称。
 */
$system['application_folder'] = 'application';
/**
 * 存放控制器文件的文件夹路径名称
 */
$system['controller_folder'] = $system['application_folder'] . '/controllers';
/**
 * 存放模型文件的文件夹路径名称
 */
$system['model_folder'] = $system['application_folder'] . '/models';
/**
 * 存放视图文件的文件夹路径名称
 */
$system['view_folder'] = $system['application_folder'] . '/views';
/**
 * 存放类库文件的文件夹路径名称,存放在该文件夹的类库中的类会自动加载
 */
$system['library_folder'] = $system['application_folder'] . '/library';
/**
 * 存放函数文件的文件夹路径名称
 */
$system['helper_folder'] = $system['application_folder'] . '/helper';
/**
 * 404错误文件的路径,该文件会在系统找不到相关内容时显示,
 * 文件里面可以使用$msg变量获取出错提示内容
 */
$system['error_page_404'] = 'application/error/error_404.php';
/**
 * 系统错误文件的路径,该文件会在发生Fatal错误和Exeption时显示,
 * 文件里面可以使用$msg变量获取出错提示内容
 */
$system['error_page_50x'] = 'application/error/error_50x.php';
/**
 * 数据库错误文件的路径,该文件会在发生数据库错误时显示,
 * 文件里面可以使用$msg变量获取出错提示内容
 */
$system['error_page_db'] = 'application/error/error_db.php';
/**
 * 默认控制器文件名称,不包含后缀,支持子文件夹,比如home.welcome,
 * 就是控制器文件夹下面的home文件夹里面welcome.php(假定后缀是.php)
 */
$system['default_controller'] = 'welcome';
/**
 * 默认控制器方法名称,不要带前缀
 */
$system['default_controller_method'] = 'index';
/**
 * 控制器方法名称前缀
 */
$system['controller_method_prefix'] = 'do';
/**
 * 控制器文件名称后缀,比如.php或者.controller.php
 */
$system['controller_file_subfix'] = '.php';
/**
 * 模型文件名称后缀,比如.model.php
 */
$system['model_file_subfix'] = '.model.php';
/**
 * 视图文件名称后缀,比如.view.php'
 */
$system['view_file_subfix'] = '.view.php';
/**
 * 类库文件名称后缀,比如.class.php'
 */
$system['library_file_subfix'] = '.class.php';
/**
 * 函数文件名称后缀,比如.php'
 */
$system['helper_file_subfix'] = '.php';
/**
 * 自动加载的helper文件,比如:array($item); 
 * $item是helper文件名,不包含后缀,比如: html 等.
 */
$system['helper_file_autoload'] = array();
/**
 * 自动加载的library文件,比如array($item); 
 * $item是library文件名或者"配置数组",不包含后缀,
 * 比如: ImageTool 或者配置数组array('ImageTool'=>'image')
 * 配置数组的作用是为长的类库名用别名代替.
 */
$system['library_file_autoload'] = array();
/**
 * 自动加载的model,比如array($item); 
 * $item是model文件名或者"配置数组",不包含后缀,
 * 比如: UserModel 或者配置数组 array('UserModel'=>'user')
 * 配置数组的作用是为长的model名用别名代替.
 */
$system['models_file_autoload'] = array();
/**
 * 控制器方法名称是否首字母大写,默认true
 */
$system['controller_method_ucfirst'] = TRUE;
/**
 * 是否自动连接数据库,默认true
 */
$system['autoload_db'] = TRUE;
/**
 * 是否开启调试模式,默认true显示错误信息,
 * 如果为false那么程序所有错误将不显示
 */
$system['debug'] = TRUE;
/**
 * 默认时区,PRC是中国
 */
$system['default_timezone'] = 'PRC';
/**
 * 自定义URL路由规则
 */
$system['route'] = array(
    "/^welcome\\/?(.*)$/u" => 'welcome.ajax/$1'
);
/**
 * ---------------------缓存配置-----------------------
 */
/**
 * 自定义缓存驱动文件路径，多个文件路径都放在数组里面即可
 */
$system['cache_drivers'] = array();
/**
 * 缓存配置项
 */
$system['cache_config'] = array(
    /*
     * 默认存储方式
     * 可用的方式有：auto,apc,sqlite,files,memcached,redis,wincache,xcache,memcache
     * auto自动模式寻找的顺序是 : apc,sqlite,files,memcached,redis,wincache,xcache,memcache
     */
    "storage" => "auto",
    /*
     * 默认缓存文件存储的路径
     * 使用绝对全路径，比如： /home/username/cache
     * 留空，系统自己选择
     */
    "path" => "", // 缓存文件存储默认路径
    "securityKey" => "", // 缓存安全key，建议留空，系统会自动处理 PATH/securityKey

    /*
     * 第二驱动
     * 比如：当你现在在代码中使用的是memcached, apc等等，然后你的代码转移到了一个新的服务器而且不支持memcached 或 apc
     * 这时候怎么办呢？设置第二驱动即可，当你设置的驱动不支持的时候，系统就使用第二驱动。
     * $key是你设置的驱动，当设置的“storage”=$key不可用时，就使用$key对应的$value驱动
     */
    "fallback" => array(
        "memcache" => "files",
        "memcached" => "files",
        "redis" => "files",
        "wincache" => "files",
        "xcache" => "files",
        "apc" => "files",
        "sqlite" => "files",
    ),
    /*
     * .htaccess 保护
     * true会自动在缓存文件夹里面建立.htaccess文件防止web非法访问
     */
    "htaccess" => false,
    /*
     * Memcache服务器地址;
     */
    "server" => array(
        array("192.168.199.25", 11211, 1),
    //  array("new.host.ip",11211,1),
    ),
    /*
     * Redis服务器地址;
     */
    "redis" => array(
        'type' => 'tcp', //sock,tcp;连接类型，tcp：使用host port连接，sock：本地sock文件连接
        'prefix' => @$_SERVER['HTTP_HOST'], //key的前缀，便于管理查看，在set和get的时候会自动加上和去除前缀，无前缀请保持null
        'sock' => '', //sock的完整路径
        'host' => '192.168.199.25',
        'port' => 6379,
        'password' => NULL, //密码，如果没有,保持null
        'timeout' => 0, //0意味着没有超时限制，单位秒
        'retry' => 100, //连接失败后的重试时间间隔，单位毫秒
        'db' => 0, // 数据库序号，默认0, 参考 http://redis.io/commands/select
    ),
);
/**
 * -----------------------SESSION管理配置---------------------------
 */
$system['session_handle'] = array(
    'handle' => '', //支持的管理类型：mongodb,mysql,memcache,redis
    'common' => array(
        'autostart' => true, //是否自动session_start()
        'cookie_path' => '/',
        'cookie_domain' => '.' . @$_SERVER['HTTP_HOST'],
        'session_name' => 'PHPSESSID',
        'lifetime' => 3600, // session lifetime in seconds
    ),
    'mongodb' => array(
        'host' => '192.168.199.25',
        'port' => 27017,
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
    /**
     * mysql表结构
     *   CREATE TABLE `session_handler_table` (
      `id` varchar(255) NOT NULL,
      `data` mediumtext NOT NULL,
      `timestamp` int(255) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `id` (`id`,`timestamp`),
      KEY `timestamp` (`timestamp`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
     */
    'mysql' => array(
        'host' => '127.0.0.1',
        'port' => 3306,
        'user' => 'root',
        'password' => 'admin',
        'database' => 'test',
        'table' => 'session_handler_table',
    ),
    /**
     * memcache采用的是session.save_handler管理机制
     * 需要php安装memcache拓展支持
     */
    'memcache' => "tcp://192.168.199.25:11211",
    /**
     * redis采用的是session.save_handler管理机制
     * 需要php安装redis拓展支持,你可以在https://github.com/nicolasff/phpredis 找到该拓展。
     */
    'redis' => "tcp://192.168.199.25:6379",
);
/**
 * ------------------------数据库配置----------------------------
 */
/**
 * 默认使用的数据库组名称，名称就是下面的$system['db'][$key]里面的$key，
 * 可以自定义多个数据库组，然后根据不同的环境选择不同的组作为默认数据库连接信息
 */
$system['db']['active_group'] = 'default';

/**
 * dbdriver：可用的有mysql,mysqli,pdo,sqlite3,配置见下面
 */
/**
 * mysql数据库配置示例
 */
$system['db']['default']['dbdriver'] = "mysql";
$system['db']['default']['hostname'] = 'localhost';
$system['db']['default']['port'] = '3306';
$system['db']['default']['username'] = 'root';
$system['db']['default']['password'] = 'admin';
$system['db']['default']['database'] = 'test';
$system['db']['default']['dbprefix'] = '';
$system['db']['default']['pconnect'] = TRUE;
$system['db']['default']['db_debug'] = TRUE;
$system['db']['default']['char_set'] = 'utf8';
$system['db']['default']['dbcollat'] = 'utf8_general_ci';
$system['db']['default']['swap_pre'] = '';
$system['db']['default']['autoinit'] = TRUE;
$system['db']['default']['stricton'] = FALSE;


/*
 * PDO database config demo
 * 1.pdo sqlite3
 * */
/**
 * sqlite3数据库配置示例
 */
$system['db']['sqlite3']['dbdriver'] = "sqlite3";
$system['db']['sqlite3']['database'] = 'sqlite:d:/wwwroot/sdb.db';
$system['db']['sqlite3']['dbprefix'] = '';
$system['db']['sqlite3']['db_debug'] = TRUE;
$system['db']['sqlite3']['char_set'] = 'utf8';
$system['db']['sqlite3']['dbcollat'] = 'utf8_general_ci';
$system['db']['sqlite3']['swap_pre'] = '';
$system['db']['sqlite3']['autoinit'] = TRUE;
$system['db']['sqlite3']['stricton'] = FALSE;
/**
 * PDO mysql数据库配置示例，hostname 其实就是pdo的dsn部分，
 * 如果连接其它数据库按着pdo的dsn写法连接即可
 */
$system['db']['pdo_msyql']['dbdriver'] = "pdo";
$system['db']['pdo_msyql']['hostname'] = 'mysql:host=localhost;port=3306';
$system['db']['pdo_msyql']['username'] = 'root';
$system['db']['pdo_msyql']['password'] = 'admin';
$system['db']['pdo_msyql']['database'] = 'test';
$system['db']['pdo_msyql']['dbprefix'] = '';
$system['db']['pdo_msyql']['char_set'] = 'utf8';
$system['db']['pdo_msyql']['dbcollat'] = 'utf8_general_ci';
$system['db']['pdo_msyql']['swap_pre'] = '';
$system['db']['pdo_msyql']['autoinit'] = TRUE;
$system['db']['pdo_msyql']['stricton'] = FALSE;
/**
 * -------------------------数据库配置结束--------------------------
 */



/* End of file index.php */
include('MicroPHP.min.php');
WoniuRouter::setConfig($system);
WoniuRouter::loadClass();