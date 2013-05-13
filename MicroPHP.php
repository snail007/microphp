<?php

//####################modules/WoniuRouter.php####################{


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
 * @createdtime       2013-05-13 11:41:18
 */
class WoniuRouter {

    public static function loadClass() {
        global $system;
        $methodInfo = self::parseURI();
        //在解析路由之后，就注册自动加载，这样控制器可以继承类库文件夹里面的自定义父控制器,实现hook功能，达到拓展控制器的功能
        //但是plugin模式下，路由器不再使用，那么这里就不会被执行，自动加载功能会失效，所以在每个instance方法里面再尝试加载一次即可，
        //如此一来就能满足两种模式
        WoniuLoader::classAutoloadRegister();
//        var_dump($methodInfo);
        if (file_exists($methodInfo['file'])) {
            include $methodInfo['file'];
            $class = new $methodInfo['class']();
            if (method_exists($class, $methodInfo['method'])) {
                $methodInfo['parameters'] = is_array($methodInfo['parameters']) ? $methodInfo['parameters'] : array();
                if (method_exists($class, '__output')) {
                    ob_start();
                    call_user_func_array(array($class, $methodInfo['method']), $methodInfo['parameters']);
                    $buffer = ob_get_contents();
                    @ob_end_clean();
                    call_user_func_array(array($class, '__output'), array($buffer));
                } else {
                    call_user_func_array(array($class, $methodInfo['method']), $methodInfo['parameters']);
                }
            } else {
                trigger404($methodInfo['class'] . ':' . $methodInfo['method'] . ' not found.');
            }
        } else {
            if ($system['debug']) {
                trigger404('file:' . $methodInfo['file'] . ' not found.');
            } else {
                trigger404();
            }
        }
    }

    private static function parseURI() {
        global $system;
        $pathinfo = @parse_url($_SERVER['REQUEST_URI']);
        if (empty($pathinfo)) {
            if ($system['debug']) {
                trigger404('request parse error:' . $_SERVER['REQUEST_URI']);
            } else {
                trigger404();
            }
        }
        //优先以查询模式获取查询字符串，然后尝试获取pathinfo模式的查询字符串
        $pathinfo_query = !empty($pathinfo['query']) ? $pathinfo['query'] : (!empty($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '');
        $class_method = $system['default_controller'] . '.' . $system['default_controller_method'];
        //看看是否要处理查询字符串
        if (!empty($pathinfo_query)) {
            //查询字符串去除头部的/
            $pathinfo_query{0} === '/' ? $pathinfo_query = substr($pathinfo_query, 1) : null;
            $requests = explode("/", $pathinfo_query);
            //看看是否指定了类和方法名
            preg_match('/[^&]+(?:\.[^&]+)+/', $requests[0]) ? $class_method = $requests[0] : null;
            if(strstr($class_method, '&')!==false){
                $cm=  explode('&', $class_method);
                $class_method=$cm[0];
            }
        }
        //去掉查询字符串中的类方法部分，只留下参数
        $pathinfo_query = str_replace($class_method, '', $pathinfo_query);
        $pathinfo_query_parameters = explode("&", $pathinfo_query);
        $pathinfo_query_parameters_str = !empty($pathinfo_query_parameters[0]) ? $pathinfo_query_parameters[0] : '';
        //去掉参数开头的/，只留下参数
        $pathinfo_query_parameters_str && $pathinfo_query_parameters_str{0} === '/' ? $pathinfo_query_parameters_str = substr($pathinfo_query_parameters_str, 1) : '';

        //现在已经解析出了，$class_method类方法名称字符串(main.index），$pathinfo_query_parameters_str参数字符串(1/2)，进一步解析为真实路径
        $class_method = explode(".", $class_method);
        $method = end($class_method);
        $method = $system['controller_method_prefix'] . ($system['controller_method_ucfirst'] ? ucfirst($method) : $method);

        unset($class_method[count($class_method) - 1]);

        $file = $system['controller_folder'] . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $class_method) . $system['controller_file_subfix'];
        $class = $class_method[count($class_method) - 1];
        $parameters = explode("/", $pathinfo_query_parameters_str);
        //对参数进行urldecode解码一下
        foreach ($parameters as $key => $value) {
            $parameters[$key] = urldecode($value);
        }
        return array('file' => $file, 'class' => ucfirst($class), 'method' => str_replace('.', '/', $method), 'parameters' => $parameters);
    }

}

/* End of file Router.php */
//####################modules/WoniuLoader.php####################{


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
 * @createdtime       2013-05-13 11:41:18
 */
class WoniuLoader {

    public $db, $input;
    private $helper_files = array();
    public $model;
    private $view_vars = array();
    private static $instance;

    public function __construct() {
        date_default_timezone_set($this->config('system', 'default_timezone'));
        $this->input = new WoniuInput();
        $this->model = new WoniuModelLoader();
        if ($this->config('system', "autoload_db")) {
            $this->database();
        }
        stripslashes_all();
    }

    public function config($config_group, $key = '') {
        global $$config_group;
        if ($key) {
            $config_group = $$config_group;
            return isset($config_group[$key]) ? $config_group[$key] : null;
        } else {
            return isset($$config_group) ? $$config_group : null;
        }
    }

    public function database($config = NULL, $is_return = false) {
        if ($is_return) {
            $db = null;
            //没有传递配置，使用默认配置
            if (!is_array($config)) {
                global $woniu_db;
                $db = WoniuDB::getInstance($woniu_db[$woniu_db['active_group']]);
            } else {
                $db = WoniuDB::getInstance($config);
            }
            return $db;
        } else {
            //没有传递配置，使用默认配置
            if (!is_array($config)) {
                if (!is_object($this->db)) {
                    global $woniu_db;
                    $this->db = WoniuDB::getInstance($woniu_db[$woniu_db['active_group']]);
                }
            } else {
                $this->db = WoniuDB::getInstance($config);
            }
        }
    }

    public function helper($file_name) {
        global $system;
        $filename = $system['helper_folder'] . DIRECTORY_SEPARATOR . $file_name . $system['helper_file_subfix'];
        if (in_array($filename, $this->helper_files)) {
            return;
        }
        if (file_exists($filename)) {
            $this->helper_files[] = $filename;
            //包含文件，并把文件里面的变量变为全局变量
            $before_vars = array_keys(get_defined_vars());
            include $filename;
            $vars = get_defined_vars();
            $all_vars = array_keys($vars);
            foreach ($all_vars as $key) {
                if (!in_array($key, $before_vars) && isset($vars[$key])) {
                    $GLOBALS[$key] = $vars[$key];
                }
            }
        } else {
            trigger404($filename . ' not found.');
        }
    }

    public function model($file_name, $alias_name = null) {
        global $system;
        $classname = $file_name;
        if (strstr($file_name, '/') !== false || strstr($file_name, "\\") !== false) {
            $classname = basename($file_name);
        }
        if (!$alias_name) {
            $alias_name = strtolower($classname);
        }
        $filepath = $system['model_folder'] . DIRECTORY_SEPARATOR . $file_name . $system['model_file_subfix'];
        if (in_array($alias_name, array_keys(WoniuModelLoader::$model_files))) {
            return WoniuModelLoader::$model_files[$alias_name];
        }
        if (file_exists($filepath)) {
            include $filepath;
            if (class_exists($classname)) {
                return WoniuModelLoader::$model_files[$alias_name] = new $classname();
            } else {
                trigger404('Model Class:' . $classname . ' not found.');
            }
        } else {
            trigger404($filepath . ' not found.');
        }
    }

    public function view($view_name, $data = null, $return = false) {
        if (is_array($data)) {
            $this->view_vars = array_merge($this->view_vars, $data);
            extract($this->view_vars);
        }
        global $system;
        $view_path = $system['view_folder'] . DIRECTORY_SEPARATOR . $view_name . $system['view_file_subfix'];
        if (file_exists($view_path)) {
            if ($return) {
                @ob_end_clean();
                ob_start();
                include $view_path;
                $html = ob_get_contents();
                @ob_end_clean();
                return $html;
            } else {
                include $view_path;
            }
        } else {
            trigger404('View:' . $view_path . ' not found');
        }
    }

    public static function classAutoloadRegister() {
        //在plugin模式下，路由器不再使用，那么自动注册不会被执行，自动加载功能会失效，所以在这里再尝试加载一次，
        //如此一来就能满足两种模式
        $found = false;
        $__autoload_found = false;
        $auto_functions = spl_autoload_functions();
        if (is_array($auto_functions)) {
            foreach ($auto_functions as $func) {
                if (is_array($func) && $func[0] == 'WoniuLoader' && $func[1] == 'classAutoloader') {
                    $found = TRUE;
                    break;
                }
            }
            foreach ($auto_functions as $func) {
                if (!is_array($func) && $func == '__autoload') {
                    $__autoload_found = TRUE;
                    break;
                }
            }
        }
        if (function_exists('__autoload') && !$__autoload_found) {
            //如果存在__autoload而且没有被注册过,就显示的注册它，不然它会因为spl_autoload_register的调用而失效
            spl_autoload_register('__autoload');
        }
        if (!$found) {
            //最后注册我们的自动加载器
            spl_autoload_register(array('WoniuLoader', 'classAutoloader'));
        }
    }

    public static function classAutoloader($clazzName) {
        global $system;
        $library = $system['library_folder'] . DIRECTORY_SEPARATOR . $clazzName . $system['library_file_subfix'];
        if (file_exists($library)) {
            include($library);
        }
    }

    public static function instance() {
        //在plugin模式下，路由器不再使用，那么自动注册不会被执行，自动加载功能会失效，所以在这里再尝试加载一次，
        //如此一来就能满足两种模式
        self::classAutoloadRegister();
        return empty(self::$instance) ? self::$instance = new self() : self::$instance;
    }

    public function view_path($view_name) {
        global $system;
        $view_path = $system['view_folder'] . DIRECTORY_SEPARATOR . $view_name . $system['view_file_subfix'];
        return $view_path;
    }

    public function ajax_echo($code, $tip = '', $data = '', $is_exit = true) {
        $str = json_encode(array('code' => $code, 'tip' => $tip ? $tip : '', 'data' => empty($data) ? '' : $data));
        header('Content-type:application/json;charset=urf-8');
        echo $str;
        if ($is_exit) {
            exit();
        }
    }

    public function xml_echo($xml, $is_exit = true) {
        header('Content-type:text/xml;charset=utf-8');
        echo $xml;
        if ($is_exit) {
            exit();
        }
    }

    public function redirect($url, $msg = null, $view = null, $time = 3) {
        if (empty($msg)) {
            header('Location:' . $url);
        } else {
            header("refresh:{$time};url={$url}"); //单位秒
            header("Content-type: text/html; charset=utf-8");
            if (empty($view)) {
                echo $msg;
            } else {
                $this->view($view, array('msg' => $msg, 'url' => $url, 'time' => $time));
            }
        }
    }

    public function message($msg, $view = null, $url = null, $time = 3) {
        if (!empty($url)) {
            header("refresh:{$time};url={$url}"); //单位秒
        }
        header("Content-type: text/html; charset=utf-8");
        if (!empty($view)) {
            $this->view($view, array('msg' => $msg, 'url' => $url, 'time' => $time));
        } else {
            echo $msg;
        }
    }

    public function setCookie($key, $value, $life = null, $path = '/', $domian = null) {
        header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
        setcookie($key, $value, ($life ? $life + time() : null), $path, ($domian ? $domian : '.'.$this->input->server('HTTP_HOST')), ($this->input->server('SERVER_PORT') == 443 ? 1 : 0));
        $_COOKIE[$key] = $value;
    }

}

class WoniuModelLoader {

    public static $model_files = array();

    function __get($classname) {
        return isset(self::$model_files[strtolower($classname)]) ? self::$model_files[strtolower($classname)] : null;
    }

}

/* End of file Loader.php */
//####################modules/WoniuController.php####################{


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
 * @createdtime       2013-05-13 11:41:18
 */
class WoniuController extends WoniuLoader {

    private static $woniu;
    private static $instance;

    public function __construct() {
        parent::__construct();
        self::$woniu = &$this;
    }

    public static function &getInstance() {
        return self::$woniu;
    }
    public static function instance($classname_path) {
        if (empty($classname_path)) {
            return empty(self::$instance) ? self::$instance = new self() : self::$instance;
        }
        global $system;
        $classname_path = str_replace('.', DIRECTORY_SEPARATOR, $classname_path);
        $classname = basename($classname_path);
        $filepath = $system['controller_folder'] . DIRECTORY_SEPARATOR . strtolower($classname_path) . $system['controller_file_subfix'];
        $alias_name = strtolower($filepath);

        if (in_array($alias_name, array_keys(WoniuModelLoader::$model_files))) {
            return WoniuModelLoader::$model_files[$alias_name];
        }
        if (file_exists($filepath)) {
            WoniuLoader::classAutoloadRegister();
            include $filepath;
            if (class_exists($classname)) {
                return WoniuModelLoader::$model_files[$alias_name] = new $classname();
            } else {
                trigger404('Ccontroller Class:' . $classname . ' not found.');
            }
        } else {
            trigger404($filepath . ' not found.');
        }
    }

}

/* End of file Controller.php */
//####################modules/WoniuModel.php####################{


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
 * @createdtime       2013-05-13 11:41:18
 */
class WoniuModel extends WoniuLoader {

    private static $instance;

    public static function instance($classname_path) {
        if (empty($classname_path)) {
            return empty(self::$instance) ? self::$instance = new self() : self::$instance;
        }
        global $system;
        $classname_path = str_replace('.', DIRECTORY_SEPARATOR, $classname_path);
        $classname = basename($classname_path);
        $filepath = $system['model_folder'] . DIRECTORY_SEPARATOR . $classname_path . $system['model_file_subfix'];
        $alias_name = strtolower($filepath);
        if (in_array($alias_name, array_keys(WoniuModelLoader::$model_files))) {
            return WoniuModelLoader::$model_files[$alias_name];
        }
        if (file_exists($filepath)) {
            //在plugin模式下，路由器不再使用，那么自动注册不会被执行，自动加载功能会失效，所以在这里再尝试加载一次，
            //如此一来就能满足两种模式
            WoniuLoader::classAutoloadRegister();
            include $filepath;
            if (class_exists($classname)) {
                return WoniuModelLoader::$model_files[$alias_name] = new $classname();
            } else {
                trigger404('Model Class:' . $classname . ' not found.');
            }
        } else {
            trigger404($filepath . ' not found.');
        }
    }

}

/* End of file Model.php */
//####################modules/DB_driver.php####################{


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
 * @createdtime       2013-05-13 11:41:18
 */
class WoniuDB {

    private static $conns = array();

    public static function getInstance($config) {
        $class = 'CI_DB_' . $config['dbdriver'] . '_driver';
        $hash = md5(sha1(var_export($config, TRUE)));
        if(!isset(self::$conns[$hash])){
            self::$conns[$hash] = new $class($config);
        }
        if ($config['dbdriver'] == 'pdo' && strpos($config['hostname'], 'mysql') !== FALSE) {
            //pdo下面dns设置mysql字符会失效，这里hack一下
            self::$conns[$hash]->simple_query('set names ' . $config['char_set']);
        }
        return self::$conns[$hash];
    }

}

class CI_DB extends CI_DB_active_record {
    
}

// ------------------------------------------------------------------------


/**
 * PDO Database Adapter Class
 *
 * Note: _DB is an extender class that the app controller
 * creates dynamically based on whether the active record
 * class is being used or not.
 *
 * @package		CodeIgniter
 * @subpackage	Drivers
 * @category	Database
 * @author		EllisLab Dev Team
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_pdo_driver extends CI_DB {

    var $dbdriver = 'pdo';
    // the character used to excape - not necessary for PDO
    var $_escape_char = '';
    var $_like_escape_str;
    var $_like_escape_chr;

    /**
     * The syntax to count rows is slightly different across different
     * database engines, so this string appears in each driver and is
     * used for the count_all() and count_all_results() functions.
     */
    var $_count_string = "SELECT COUNT(*) AS ";
    var $_random_keyword;
    var $options = array();

    function __construct($params) {
        parent::__construct($params);

        // clause and character used for LIKE escape sequences
        if (strpos($this->hostname, 'mysql') !== FALSE) {
            $this->_like_escape_str = '';
            $this->_like_escape_chr = '';

            //Prior to this version, the charset can't be set in the dsn
            if (is_php('5.3.6')) {
                $this->hostname .= ";charset={$this->char_set}";
            }

            //Set the charset with the connection options
            $this->options['PDO::MYSQL_ATTR_INIT_COMMAND'] = "SET NAMES {$this->char_set}";
        } elseif (strpos($this->hostname, 'odbc') !== FALSE) {
            $this->_like_escape_str = " {escape '%s'} ";
            $this->_like_escape_chr = '!';
        } else {
            $this->_like_escape_str = " ESCAPE '%s' ";
            $this->_like_escape_chr = '!';
        }

        empty($this->database) OR $this->hostname .= ';dbname=' . $this->database;

        $this->trans_enabled = FALSE;

        $this->_random_keyword = ' RND(' . time() . ')'; // database specific random keyword
    }

    /**
     * Non-persistent database connection
     *
     * @access	private called by the base class
     * @return	resource
     */
    function db_connect() {
        $this->options['PDO::ATTR_ERRMODE'] = PDO::ERRMODE_SILENT;

        return new PDO($this->hostname, $this->username, $this->password, $this->options);
    }

    // --------------------------------------------------------------------

    /**
     * Persistent database connection
     *
     * @access	private called by the base class
     * @return	resource
     */
    function db_pconnect() {
        $this->options['PDO::ATTR_ERRMODE'] = PDO::ERRMODE_SILENT;
        $this->options['PDO::ATTR_PERSISTENT'] = TRUE;

        return new PDO($this->hostname, $this->username, $this->password, $this->options);
    }

    // --------------------------------------------------------------------

    /**
     * Reconnect
     *
     * Keep / reestablish the db connection if no queries have been
     * sent for a length of time exceeding the server's idle timeout
     *
     * @access	public
     * @return	void
     */
    function reconnect() {
        if ($this->db->db_debug) {
            return $this->db->display_error('db_unsuported_feature');
        }
        return FALSE;
    }

    // --------------------------------------------------------------------

    /**
     * Select the database
     *
     * @access	private called by the base class
     * @return	resource
     */
    function db_select() {
        // Not needed for PDO
        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Set client character set
     *
     * @access	public
     * @param	string
     * @param	string
     * @return	resource
     */
    function _db_set_charset($charset, $collation) {
        // @todo - add support if needed
        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Version number query string
     *
     * @access	public
     * @return	string
     */
    function _version() {
        return $this->conn_id->getAttribute(PDO::ATTR_CLIENT_VERSION);
    }

    // --------------------------------------------------------------------

    /**
     * Execute the query
     *
     * @access	private called by the base class
     * @param	string	an SQL query
     * @return	object
     */
    function _execute($sql) {
        $sql = $this->_prep_query($sql);
        $result_id = $this->conn_id->prepare($sql);
        $result_id->execute();

        if (is_object($result_id)) {
            if (is_numeric(stripos($sql, 'SELECT'))) {
                $this->affect_rows = count($result_id->fetchAll());
                $result_id->execute();
            } else {
                $this->affect_rows = $result_id->rowCount();
            }
        } else {
            $this->affect_rows = 0;
        }

        return $result_id;
    }

    // --------------------------------------------------------------------

    /**
     * Prep the query
     *
     * If needed, each database adapter can prep the query string
     *
     * @access	private called by execute()
     * @param	string	an SQL query
     * @return	string
     */
    function _prep_query($sql) {
        return $sql;
    }

    // --------------------------------------------------------------------

    /**
     * Begin Transaction
     *
     * @access	public
     * @return	bool
     */
    function trans_begin($test_mode = FALSE) {
        if (!$this->trans_enabled) {
            return TRUE;
        }

        // When transactions are nested we only begin/commit/rollback the outermost ones
        if ($this->_trans_depth > 0) {
            return TRUE;
        }

        // Reset the transaction failure flag.
        // If the $test_mode flag is set to TRUE transactions will be rolled back
        // even if the queries produce a successful result.
        $this->_trans_failure = (bool) ($test_mode === TRUE);

        return $this->conn_id->beginTransaction();
    }

    // --------------------------------------------------------------------

    /**
     * Commit Transaction
     *
     * @access	public
     * @return	bool
     */
    function trans_commit() {
        if (!$this->trans_enabled) {
            return TRUE;
        }

        // When transactions are nested we only begin/commit/rollback the outermost ones
        if ($this->_trans_depth > 0) {
            return TRUE;
        }

        $ret = $this->conn->commit();
        return $ret;
    }

    // --------------------------------------------------------------------

    /**
     * Rollback Transaction
     *
     * @access	public
     * @return	bool
     */
    function trans_rollback() {
        if (!$this->trans_enabled) {
            return TRUE;
        }

        // When transactions are nested we only begin/commit/rollback the outermost ones
        if ($this->_trans_depth > 0) {
            return TRUE;
        }

        $ret = $this->conn_id->rollBack();
        return $ret;
    }

    // --------------------------------------------------------------------

    /**
     * Escape String
     *
     * @access	public
     * @param	string
     * @param	bool	whether or not the string will be used in a LIKE condition
     * @return	string
     */
    function escape_str($str, $like = FALSE) {
        if (is_array($str)) {
            foreach ($str as $key => $val) {
                $str[$key] = $this->escape_str($val, $like);
            }

            return $str;
        }

        //Escape the string
        $str = $this->conn_id->quote($str);

        //If there are duplicated quotes, trim them away
        if (strpos($str, "'") === 0) {
            $str = substr($str, 1, -1);
        }

        // escape LIKE condition wildcards
        if ($like === TRUE) {
            $str = str_replace(array('%', '_', $this->_like_escape_chr), array($this->_like_escape_chr . '%', $this->_like_escape_chr . '_', $this->_like_escape_chr . $this->_like_escape_chr), $str);
        }

        return $str;
    }

    // --------------------------------------------------------------------

    /**
     * Affected Rows
     *
     * @access	public
     * @return	integer
     */
    function affected_rows() {
        return $this->affect_rows;
    }

    // --------------------------------------------------------------------

    /**
     * Insert ID
     * 
     * @access	public
     * @return	integer
     */
    function insert_id($name = NULL) {
        //Convenience method for postgres insertid
        if (strpos($this->hostname, 'pgsql') !== FALSE) {
            $v = $this->_version();

            $table = func_num_args() > 0 ? func_get_arg(0) : NULL;

            if ($table == NULL && $v >= '8.1') {
                $sql = 'SELECT LASTVAL() as ins_id';
            }
            $query = $this->query($sql);
            $row = $query->row();
            return $row->ins_id;
        } else {
            return $this->conn_id->lastInsertId($name);
        }
    }

    // --------------------------------------------------------------------

    /**
     * "Count All" query
     *
     * Generates a platform-specific query string that counts all records in
     * the specified database
     *
     * @access	public
     * @param	string
     * @return	string
     */
    function count_all($table = '') {
        if ($table == '') {
            return 0;
        }

        $query = $this->query($this->_count_string . $this->_protect_identifiers('numrows') . " FROM " . $this->_protect_identifiers($table, TRUE, NULL, FALSE));

        if ($query->num_rows() == 0) {
            return 0;
        }

        $row = $query->row();
        $this->_reset_select();
        return (int) $row->numrows;
    }

    // --------------------------------------------------------------------

    /**
     * Show table query
     *
     * Generates a platform-specific query string so that the table names can be fetched
     *
     * @access	private
     * @param	boolean
     * @return	string
     */
    function _list_tables($prefix_limit = FALSE) {
        $sql = "SHOW TABLES FROM `" . $this->database . "`";

        if ($prefix_limit !== FALSE AND $this->dbprefix != '') {
            //$sql .= " LIKE '".$this->escape_like_str($this->dbprefix)."%' ".sprintf($this->_like_escape_str, $this->_like_escape_chr);
            return FALSE; // not currently supported
        }

        return $sql;
    }

    // --------------------------------------------------------------------

    /**
     * Show column query
     *
     * Generates a platform-specific query string so that the column names can be fetched
     *
     * @access	public
     * @param	string	the table name
     * @return	string
     */
    function _list_columns($table = '') {
        return "SHOW COLUMNS FROM " . $table;
    }

    // --------------------------------------------------------------------

    /**
     * Field data query
     *
     * Generates a platform-specific query so that the column data can be retrieved
     *
     * @access	public
     * @param	string	the table name
     * @return	object
     */
    function _field_data($table) {
        return "SELECT TOP 1 FROM " . $table;
    }

    // --------------------------------------------------------------------

    /**
     * The error message string
     *
     * @access	private
     * @return	string
     */
    function _error_message() {
        $error_array = $this->conn_id->errorInfo();
        return $error_array[2];
    }

    // --------------------------------------------------------------------

    /**
     * The error message number
     *
     * @access	private
     * @return	integer
     */
    function _error_number() {
        return $this->conn_id->errorCode();
    }

    // --------------------------------------------------------------------

    /**
     * Escape the SQL Identifiers
     *
     * This function escapes column and table names
     *
     * @access	private
     * @param	string
     * @return	string
     */
    function _escape_identifiers($item) {
        if ($this->_escape_char == '') {
            return $item;
        }

        foreach ($this->_reserved_identifiers as $id) {
            if (strpos($item, '.' . $id) !== FALSE) {
                $str = $this->_escape_char . str_replace('.', $this->_escape_char . '.', $item);

                // remove duplicates if the user already included the escape
                return preg_replace('/[' . $this->_escape_char . ']+/', $this->_escape_char, $str);
            }
        }

        if (strpos($item, '.') !== FALSE) {
            $str = $this->_escape_char . str_replace('.', $this->_escape_char . '.' . $this->_escape_char, $item) . $this->_escape_char;
        } else {
            $str = $this->_escape_char . $item . $this->_escape_char;
        }

        // remove duplicates if the user already included the escape
        return preg_replace('/[' . $this->_escape_char . ']+/', $this->_escape_char, $str);
    }

    // --------------------------------------------------------------------

    /**
     * From Tables
     *
     * This function implicitly groups FROM tables so there is no confusion
     * about operator precedence in harmony with SQL standards
     *
     * @access	public
     * @param	type
     * @return	type
     */
    function _from_tables($tables) {
        if (!is_array($tables)) {
            $tables = array($tables);
        }

        return (count($tables) == 1) ? $tables[0] : '(' . implode(', ', $tables) . ')';
    }

    // --------------------------------------------------------------------

    /**
     * Insert statement
     *
     * Generates a platform-specific insert string from the supplied data
     *
     * @access	public
     * @param	string	the table name
     * @param	array	the insert keys
     * @param	array	the insert values
     * @return	string
     */
    function _insert($table, $keys, $values) {
        return "INSERT INTO " . $table . " (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ")";
    }

    // --------------------------------------------------------------------

    /**
     * Insert_batch statement
     *
     * Generates a platform-specific insert string from the supplied data
     *
     * @access  public
     * @param   string  the table name
     * @param   array   the insert keys
     * @param   array   the insert values
     * @return  string
     */
    function _insert_batch($table, $keys, $values) {
        return "INSERT INTO " . $table . " (" . implode(', ', $keys) . ") VALUES " . implode(', ', $values);
    }

    // --------------------------------------------------------------------

    /**
     * Update statement
     *
     * Generates a platform-specific update string from the supplied data
     *
     * @access	public
     * @param	string	the table name
     * @param	array	the update data
     * @param	array	the where clause
     * @param	array	the orderby clause
     * @param	array	the limit clause
     * @return	string
     */
    function _update($table, $values, $where, $orderby = array(), $limit = FALSE) {
        foreach ($values as $key => $val) {
            $valstr[] = $key . " = " . $val;
        }

        $limit = (!$limit) ? '' : ' LIMIT ' . $limit;

        $orderby = (count($orderby) >= 1) ? ' ORDER BY ' . implode(", ", $orderby) : '';

        $sql = "UPDATE " . $table . " SET " . implode(', ', $valstr);

        $sql .= ($where != '' AND count($where) >= 1) ? " WHERE " . implode(" ", $where) : '';

        $sql .= $orderby . $limit;

        return $sql;
    }

    // --------------------------------------------------------------------

    /**
     * Update_Batch statement
     *
     * Generates a platform-specific batch update string from the supplied data
     *
     * @access	public
     * @param	string	the table name
     * @param	array	the update data
     * @param	array	the where clause
     * @return	string
     */
    function _update_batch($table, $values, $index, $where = NULL) {
        $ids = array();
        $where = ($where != '' AND count($where) >= 1) ? implode(" ", $where) . ' AND ' : '';

        foreach ($values as $key => $val) {
            $ids[] = $val[$index];

            foreach (array_keys($val) as $field) {
                if ($field != $index) {
                    $final[$field][] = 'WHEN ' . $index . ' = ' . $val[$index] . ' THEN ' . $val[$field];
                }
            }
        }

        $sql = "UPDATE " . $table . " SET ";
        $cases = '';

        foreach ($final as $k => $v) {
            $cases .= $k . ' = CASE ' . "\n";
            foreach ($v as $row) {
                $cases .= $row . "\n";
            }

            $cases .= 'ELSE ' . $k . ' END, ';
        }

        $sql .= substr($cases, 0, -2);

        $sql .= ' WHERE ' . $where . $index . ' IN (' . implode(',', $ids) . ')';

        return $sql;
    }

    // --------------------------------------------------------------------

    /**
     * Truncate statement
     *
     * Generates a platform-specific truncate string from the supplied data
     * If the database does not support the truncate() command
     * This function maps to "DELETE FROM table"
     *
     * @access	public
     * @param	string	the table name
     * @return	string
     */
    function _truncate($table) {
        return $this->_delete($table);
    }

    // --------------------------------------------------------------------

    /**
     * Delete statement
     *
     * Generates a platform-specific delete string from the supplied data
     *
     * @access	public
     * @param	string	the table name
     * @param	array	the where clause
     * @param	string	the limit clause
     * @return	string
     */
    function _delete($table, $where = array(), $like = array(), $limit = FALSE) {
        $conditions = '';

        if (count($where) > 0 OR count($like) > 0) {
            $conditions = "\nWHERE ";
            $conditions .= implode("\n", $this->ar_where);

            if (count($where) > 0 && count($like) > 0) {
                $conditions .= " AND ";
            }
            $conditions .= implode("\n", $like);
        }

        $limit = (!$limit) ? '' : ' LIMIT ' . $limit;

        return "DELETE FROM " . $table . $conditions . $limit;
    }

    // --------------------------------------------------------------------

    /**
     * Limit string
     *
     * Generates a platform-specific LIMIT clause
     *
     * @access	public
     * @param	string	the sql query string
     * @param	integer	the number of rows to limit the query to
     * @param	integer	the offset value
     * @return	string
     */
    function _limit($sql, $limit, $offset) {
        if (strpos($this->hostname, 'cubrid') !== FALSE || strpos($this->hostname, 'sqlite') !== FALSE) {
            if ($offset == 0) {
                $offset = '';
            } else {
                $offset .= ", ";
            }

            return $sql . "LIMIT " . $offset . $limit;
        } else {
            $sql .= "LIMIT " . $limit;

            if ($offset > 0) {
                $sql .= " OFFSET " . $offset;
            }

            return $sql;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Close DB Connection
     *
     * @access	public
     * @param	resource
     * @return	void
     */
    function _close($conn_id) {
        $this->conn_id = null;
    }

}

/* End of file pdo_driver.php */

/**
 * PDO Result Class
 *
 * This class extends the parent result class: CI_DB_result
 *
 * @category	Database
 * @author		EllisLab Dev Team
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_pdo_result extends CI_DB_result {

    public $num_rows;

    /**
     * Number of rows in the result set
     *
     * @return	int
     */
    public function num_rows() {
        if (is_int($this->num_rows)) {
            return $this->num_rows;
        } elseif (($this->num_rows = $this->result_id->rowCount()) > 0) {
            return $this->num_rows;
        }

        $this->num_rows = count($this->result_id->fetchAll());
        $this->result_id->execute();
        return $this->num_rows;
    }

    // --------------------------------------------------------------------

    /**
     * Number of fields in the result set
     *
     * @access	public
     * @return	integer
     */
    function num_fields() {
        return $this->result_id->columnCount();
    }

    // --------------------------------------------------------------------

    /**
     * Fetch Field Names
     *
     * Generates an array of column names
     *
     * @access	public
     * @return	array
     */
    function list_fields() {
        if ($this->db->db_debug) {
            return $this->db->display_error('db_unsuported_feature');
        }
        return FALSE;
    }

    // --------------------------------------------------------------------

    /**
     * Field data
     *
     * Generates an array of objects containing field meta-data
     *
     * @access	public
     * @return	array
     */
    function field_data() {
        $data = array();

        try {
            for ($i = 0; $i < $this->num_fields(); $i++) {
                $data[] = $this->result_id->getColumnMeta($i);
            }

            return $data;
        } catch (Exception $e) {
            if ($this->db->db_debug) {
                return $this->db->display_error('db_unsuported_feature');
            }
            return FALSE;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Free the result
     *
     * @return	null
     */
    function free_result() {
        if (is_object($this->result_id)) {
            $this->result_id = FALSE;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Data Seek
     *
     * Moves the internal pointer to the desired offset.  We call
     * this internally before fetching results to make sure the
     * result set starts at zero
     *
     * @access	private
     * @return	array
     */
    function _data_seek($n = 0) {
        return FALSE;
    }

    // --------------------------------------------------------------------

    /**
     * Result - associative array
     *
     * Returns the result set as an array
     *
     * @access	private
     * @return	array
     */
    function _fetch_assoc() {
        return $this->result_id->fetch(PDO::FETCH_ASSOC);
    }

    // --------------------------------------------------------------------

    /**
     * Result - object
     *
     * Returns the result set as an object
     *
     * @access	private
     * @return	object
     */
    function _fetch_object() {
        return $this->result_id->fetchObject();
    }

}

/* End of file pdo_result.php */

/**
 * MySQL Database Adapter Class
 *
 * Note: _DB is an extender class that the app controller
 * creates dynamically based on whether the active record
 * class is being used or not.
 *
 * @package		CodeIgniter
 * @subpackage	Drivers
 * @category	Database
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_mysql_driver extends CI_DB {

    var $dbdriver = 'mysql';
// The character used for escaping
    var $_escape_char = '`';
// clause and character used for LIKE escape sequences - not used in MySQL
    var $_like_escape_str = '';
    var $_like_escape_chr = '';

    /**
     * Whether to use the MySQL "delete hack" which allows the number
     * of affected rows to be shown. Uses a preg_replace when enabled,
     * adding a bit more processing to all queries.
     */
    var $delete_hack = TRUE;

    /**
     * The syntax to count rows is slightly different across different
     * database engines, so this string appears in each driver and is
     * used for the count_all() and count_all_results() functions.
     */
    var $_count_string = 'SELECT COUNT(*) AS ';
    var $_random_keyword = ' RAND()'; // database specific random keyword
// whether SET NAMES must be used to set the character set
    var $use_set_names;

    /**
     * Non-persistent database connection
     *
     * @access	private called by the base class
     * @return	resource
     */
    function db_connect() {
        if ($this->port != '') {
            $this->hostname .= ':' . $this->port;
        }
        return @mysql_connect($this->hostname, $this->username, $this->password, TRUE);
    }

// --------------------------------------------------------------------

    /**
     * Persistent database connection
     *
     * @access	private called by the base class
     * @return	resource
     */
    function db_pconnect() {
        if ($this->port != '') {
            $this->hostname .= ':' . $this->port;
        }

        return @mysql_pconnect($this->hostname, $this->username, $this->password);
    }

// --------------------------------------------------------------------

    /**
     * Reconnect
     *
     * Keep / reestablish the db connection if no queries have been
     * sent for a length of time exceeding the server's idle timeout
     *
     * @access	public
     * @return	void
     */
    function reconnect() {
        if (mysql_ping($this->conn_id) === FALSE) {
            $this->conn_id = FALSE;
        }
    }

// --------------------------------------------------------------------

    /**
     * Select the database
     *
     * @access	private called by the base class
     * @return	resource
     */
    function db_select() {
        return @mysql_select_db($this->database, $this->conn_id);
    }

// --------------------------------------------------------------------

    /**
     * Set client character set
     *
     * @access	public
     * @param	string
     * @param	string
     * @return	resource
     */
    function _db_set_charset($charset, $collation) {
        if (!isset($this->use_set_names)) {
// mysql_set_charset() requires PHP >= 5.2.3 and MySQL >= 5.0.7, use SET NAMES as fallback
            $this->use_set_names = (version_compare(PHP_VERSION, '5.2.3', '>=') && version_compare(mysql_get_server_info(), '5.0.7', '>=')) ? FALSE : TRUE;
        }

        if ($this->use_set_names === TRUE) {
            return @mysql_query("SET NAMES '" . $this->escape_str($charset) . "' COLLATE '" . $this->escape_str($collation) . "'", $this->conn_id);
        } else {
            return @mysql_set_charset($charset, $this->conn_id);
        }
    }

// --------------------------------------------------------------------

    /**
     * Version number query string
     *
     * @access	public
     * @return	string
     */
    function _version() {
        return "SELECT version() AS ver";
    }

// --------------------------------------------------------------------

    /**
     * Execute the query
     *
     * @access	private called by the base class
     * @param	string	an SQL query
     * @return	resource
     */
    function _execute($sql) {
        $sql = $this->_prep_query($sql);
        return @mysql_query($sql, $this->conn_id);
    }

// --------------------------------------------------------------------

    /**
     * Prep the query
     *
     * If needed, each database adapter can prep the query string
     *
     * @access	private called by execute()
     * @param	string	an SQL query
     * @return	string
     */
    function _prep_query($sql) {
// "DELETE FROM TABLE" returns 0 affected rows This hack modifies
// the query so that it returns the number of affected rows
        if ($this->delete_hack === TRUE) {
            if (preg_match('/^\s*DELETE\s+FROM\s+(\S+)\s*$/i', $sql)) {
                $sql = preg_replace("/^\s*DELETE\s+FROM\s+(\S+)\s*$/", "DELETE FROM \\1 WHERE 1=1", $sql);
            }
        }

        return $sql;
    }

// --------------------------------------------------------------------

    /**
     * Begin Transaction
     *
     * @access	public
     * @return	bool
     */
    function trans_begin($test_mode = FALSE) {
        if (!$this->trans_enabled) {
            return TRUE;
        }

// When transactions are nested we only begin/commit/rollback the outermost ones
        if ($this->_trans_depth > 0) {
            return TRUE;
        }

// Reset the transaction failure flag.
// If the $test_mode flag is set to TRUE transactions will be rolled back
// even if the queries produce a successful result.
        $this->_trans_failure = ($test_mode === TRUE) ? TRUE : FALSE;

        $this->simple_query('SET AUTOCOMMIT=0');
        $this->simple_query('START TRANSACTION'); // can also be BEGIN or BEGIN WORK
        return TRUE;
    }

// --------------------------------------------------------------------

    /**
     * Commit Transaction
     *
     * @access	public
     * @return	bool
     */
    function trans_commit() {
        if (!$this->trans_enabled) {
            return TRUE;
        }

// When transactions are nested we only begin/commit/rollback the outermost ones
        if ($this->_trans_depth > 0) {
            return TRUE;
        }

        $this->simple_query('COMMIT');
        $this->simple_query('SET AUTOCOMMIT=1');
        return TRUE;
    }

// --------------------------------------------------------------------

    /**
     * Rollback Transaction
     *
     * @access	public
     * @return	bool
     */
    function trans_rollback() {
        if (!$this->trans_enabled) {
            return TRUE;
        }

// When transactions are nested we only begin/commit/rollback the outermost ones
        if ($this->_trans_depth > 0) {
            return TRUE;
        }

        $this->simple_query('ROLLBACK');
        $this->simple_query('SET AUTOCOMMIT=1');
        return TRUE;
    }

// --------------------------------------------------------------------

    /**
     * Escape String
     *
     * @access	public
     * @param	string
     * @param	bool	whether or not the string will be used in a LIKE condition
     * @return	string
     */
    function escape_str($str, $like = FALSE) {
        if (is_array($str)) {
            foreach ($str as $key => $val) {
                $str[$key] = $this->escape_str($val, $like);
            }

            return $str;
        }

        if (function_exists('mysql_real_escape_string') AND is_resource($this->conn_id)) {
            $str = mysql_real_escape_string($str, $this->conn_id);
        } elseif (function_exists('mysql_escape_string')) {
            $str = mysql_escape_string($str);
        } else {
            $str = addslashes($str);
        }

// escape LIKE condition wildcards
        if ($like === TRUE) {
            $str = str_replace(array('%', '_'), array('\\%', '\\_'), $str);
        }

        return $str;
    }

// --------------------------------------------------------------------

    /**
     * Affected Rows
     *
     * @access	public
     * @return	integer
     */
    function affected_rows() {
        return @mysql_affected_rows($this->conn_id);
    }

// --------------------------------------------------------------------

    /**
     * Insert ID
     *
     * @access	public
     * @return	integer
     */
    function insert_id() {
        return @mysql_insert_id($this->conn_id);
    }

// --------------------------------------------------------------------

    /**
     * "Count All" query
     *
     * Generates a platform-specific query string that counts all records in
     * the specified database
     *
     * @access	public
     * @param	string
     * @return	string
     */
    function count_all($table = '') {
        if ($table == '') {
            return 0;
        }

        $query = $this->query($this->_count_string . $this->_protect_identifiers('numrows') . " FROM " . $this->_protect_identifiers($table, TRUE, NULL, FALSE));

        if ($query->num_rows() == 0) {
            return 0;
        }

        $row = $query->row();
        $this->_reset_select();
        return (int) $row->numrows;
    }

// --------------------------------------------------------------------

    /**
     * List table query
     *
     * Generates a platform-specific query string so that the table names can be fetched
     *
     * @access	private
     * @param	boolean
     * @return	string
     */
    function _list_tables($prefix_limit = FALSE) {
        $sql = "SHOW TABLES FROM " . $this->_escape_char . $this->database . $this->_escape_char;

        if ($prefix_limit !== FALSE AND $this->dbprefix != '') {
            $sql .= " LIKE '" . $this->escape_like_str($this->dbprefix) . "%'";
        }

        return $sql;
    }

// --------------------------------------------------------------------

    /**
     * Show column query
     *
     * Generates a platform-specific query string so that the column names can be fetched
     *
     * @access	public
     * @param	string	the table name
     * @return	string
     */
    function _list_columns($table = '') {
        return "SHOW COLUMNS FROM " . $this->_protect_identifiers($table, TRUE, NULL, FALSE);
    }

// --------------------------------------------------------------------

    /**
     * Field data query
     *
     * Generates a platform-specific query so that the column data can be retrieved
     *
     * @access	public
     * @param	string	the table name
     * @return	object
     */
    function _field_data($table) {
        return "DESCRIBE " . $table;
    }

// --------------------------------------------------------------------

    /**
     * The error message string
     *
     * @access	private
     * @return	string
     */
    function _error_message() {
        return mysql_error($this->conn_id);
    }

// --------------------------------------------------------------------

    /**
     * The error message number
     *
     * @access	private
     * @return	integer
     */
    function _error_number() {
        return mysql_errno($this->conn_id);
    }

// --------------------------------------------------------------------

    /**
     * Escape the SQL Identifiers
     *
     * This function escapes column and table names
     *
     * @access	private
     * @param	string
     * @return	string
     */
    function _escape_identifiers($item) {
        if ($this->_escape_char == '') {
            return $item;
        }

        foreach ($this->_reserved_identifiers as $id) {
            if (strpos($item, '.' . $id) !== FALSE) {
                $str = $this->_escape_char . str_replace('.', $this->_escape_char . '.', $item);

// remove duplicates if the user already included the escape
                return preg_replace('/[' . $this->_escape_char . ']+/', $this->_escape_char, $str);
            }
        }

        if (strpos($item, '.') !== FALSE) {
            $str = $this->_escape_char . str_replace('.', $this->_escape_char . '.' . $this->_escape_char, $item) . $this->_escape_char;
        } else {
            $str = $this->_escape_char . $item . $this->_escape_char;
        }

// remove duplicates if the user already included the escape
        return preg_replace('/[' . $this->_escape_char . ']+/', $this->_escape_char, $str);
    }

// --------------------------------------------------------------------

    /**
     * From Tables
     *
     * This function implicitly groups FROM tables so there is no confusion
     * about operator precedence in harmony with SQL standards
     *
     * @access	public
     * @param	type
     * @return	type
     */
    function _from_tables($tables) {
        if (!is_array($tables)) {
            $tables = array($tables);
        }

        return '(' . implode(', ', $tables) . ')';
    }

// --------------------------------------------------------------------

    /**
     * Insert statement
     *
     * Generates a platform-specific insert string from the supplied data
     *
     * @access	public
     * @param	string	the table name
     * @param	array	the insert keys
     * @param	array	the insert values
     * @return	string
     */
    function _insert($table, $keys, $values) {
        return "INSERT INTO " . $table . " (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ")";
    }

// --------------------------------------------------------------------

    /**
     * Replace statement
     *
     * Generates a platform-specific replace string from the supplied data
     *
     * @access	public
     * @param	string	the table name
     * @param	array	the insert keys
     * @param	array	the insert values
     * @return	string
     */
    function _replace($table, $keys, $values) {
        return "REPLACE INTO " . $table . " (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ")";
    }

// --------------------------------------------------------------------

    /**
     * Insert_batch statement
     *
     * Generates a platform-specific insert string from the supplied data
     *
     * @access	public
     * @param	string	the table name
     * @param	array	the insert keys
     * @param	array	the insert values
     * @return	string
     */
    function _insert_batch($table, $keys, $values) {
        return "INSERT INTO " . $table . " (" . implode(', ', $keys) . ") VALUES " . implode(', ', $values);
    }

// --------------------------------------------------------------------

    /**
     * Update statement
     *
     * Generates a platform-specific update string from the supplied data
     *
     * @access	public
     * @param	string	the table name
     * @param	array	the update data
     * @param	array	the where clause
     * @param	array	the orderby clause
     * @param	array	the limit clause
     * @return	string
     */
    function _update($table, $values, $where, $orderby = array(), $limit = FALSE) {
        foreach ($values as $key => $val) {
            $valstr[] = $key . ' = ' . $val;
        }

        $limit = (!$limit) ? '' : ' LIMIT ' . $limit;

        $orderby = (count($orderby) >= 1) ? ' ORDER BY ' . implode(", ", $orderby) : '';

        $sql = "UPDATE " . $table . " SET " . implode(', ', $valstr);

        $sql .= ($where != '' AND count($where) >= 1) ? " WHERE " . implode(" ", $where) : '';

        $sql .= $orderby . $limit;

        return $sql;
    }

// --------------------------------------------------------------------

    /**
     * Update_Batch statement
     *
     * Generates a platform-specific batch update string from the supplied data
     *
     * @access	public
     * @param	string	the table name
     * @param	array	the update data
     * @param	array	the where clause
     * @return	string
     */
    function _update_batch($table, $values, $index, $where = NULL) {
        $ids = array();
        $where = ($where != '' AND count($where) >= 1) ? implode(" ", $where) . ' AND ' : '';

        foreach ($values as $key => $val) {
            $ids[] = $val[$index];

            foreach (array_keys($val) as $field) {
                if ($field != $index) {
                    $final[$field][] = 'WHEN ' . $index . ' = ' . $val[$index] . ' THEN ' . $val[$field];
                }
            }
        }

        $sql = "UPDATE " . $table . " SET ";
        $cases = '';

        foreach ($final as $k => $v) {
            $cases .= $k . ' = CASE ' . "\n";
            foreach ($v as $row) {
                $cases .= $row . "\n";
            }

            $cases .= 'ELSE ' . $k . ' END, ';
        }

        $sql .= substr($cases, 0, -2);

        $sql .= ' WHERE ' . $where . $index . ' IN (' . implode(',', $ids) . ')';

        return $sql;
    }

// --------------------------------------------------------------------

    /**
     * Truncate statement
     *
     * Generates a platform-specific truncate string from the supplied data
     * If the database does not support the truncate() command
     * This function maps to "DELETE FROM table"
     *
     * @access	public
     * @param	string	the table name
     * @return	string
     */
    function _truncate($table) {
        return "TRUNCATE " . $table;
    }

// --------------------------------------------------------------------

    /**
     * Delete statement
     *
     * Generates a platform-specific delete string from the supplied data
     *
     * @access	public
     * @param	string	the table name
     * @param	array	the where clause
     * @param	string	the limit clause
     * @return	string
     */
    function _delete($table, $where = array(), $like = array(), $limit = FALSE) {
        $conditions = '';

        if (count($where) > 0 OR count($like) > 0) {
            $conditions = "\nWHERE ";
            $conditions .= implode("\n", $this->ar_where);

            if (count($where) > 0 && count($like) > 0) {
                $conditions .= " AND ";
            }
            $conditions .= implode("\n", $like);
        }

        $limit = (!$limit) ? '' : ' LIMIT ' . $limit;

        return "DELETE FROM " . $table . $conditions . $limit;
    }

// --------------------------------------------------------------------

    /**
     * Limit string
     *
     * Generates a platform-specific LIMIT clause
     *
     * @access	public
     * @param	string	the sql query string
     * @param	integer	the number of rows to limit the query to
     * @param	integer	the offset value
     * @return	string
     */
    function _limit($sql, $limit, $offset) {
        if ($offset == 0) {
            $offset = '';
        } else {
            $offset .= ", ";
        }

        return $sql . "LIMIT " . $offset . $limit;
    }

// --------------------------------------------------------------------

    /**
     * Close DB Connection
     *
     * @access	public
     * @param	resource
     * @return	void
     */
    function _close($conn_id) {
        @mysql_close($conn_id);
    }

}

/* End of file mysql_driver.php */
/* Location: ./system/database/drivers/mysql/mysql_driver.php */


// --------------------------------------------------------------------

/**
 * MySQL Result Class
 *
 * This class extends the parent result class: CI_DB_result
 *
 * @category	Database
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_mysql_result extends CI_DB_result {

    /**
     * Number of rows in the result set
     *
     * @access	public
     * @return	integer
     */
    function num_rows() {
        return @mysql_num_rows($this->result_id);
    }

// --------------------------------------------------------------------

    /**
     * Number of fields in the result set
     *
     * @access	public
     * @return	integer
     */
    function num_fields() {
        return @mysql_num_fields($this->result_id);
    }

// --------------------------------------------------------------------

    /**
     * Fetch Field Names
     *
     * Generates an array of column names
     *
     * @access	public
     * @return	array
     */
    function list_fields() {
        $field_names = array();
        while ($field = mysql_fetch_field($this->result_id)) {
            $field_names[] = $field->name;
        }

        return $field_names;
    }

// --------------------------------------------------------------------

    /**
     * Field data
     *
     * Generates an array of objects containing field meta-data
     *
     * @access	public
     * @return	array
     */
    function field_data() {
        $retval = array();
        while ($field = mysql_fetch_object($this->result_id)) {
            preg_match('/([a-zA-Z]+)(\(\d+\))?/', $field->Type, $matches);

            $type = (array_key_exists(1, $matches)) ? $matches[1] : NULL;
            $length = (array_key_exists(2, $matches)) ? preg_replace('/[^\d]/', '', $matches[2]) : NULL;

            $F = new stdClass();
            $F->name = $field->Field;
            $F->type = $type;
            $F->default = $field->Default;
            $F->max_length = $length;
            $F->primary_key = ( $field->Key == 'PRI' ? 1 : 0 );

            $retval[] = $F;
        }

        return $retval;
    }

// --------------------------------------------------------------------

    /**
     * Free the result
     *
     * @return	null
     */
    function free_result() {
        if (is_resource($this->result_id)) {
            mysql_free_result($this->result_id);
            $this->result_id = FALSE;
        }
    }

// --------------------------------------------------------------------

    /**
     * Data Seek
     *
     * Moves the internal pointer to the desired offset.  We call
     * this internally before fetching results to make sure the
     * result set starts at zero
     *
     * @access	private
     * @return	array
     */
    function _data_seek($n = 0) {
        return mysql_data_seek($this->result_id, $n);
    }

// --------------------------------------------------------------------

    /**
     * Result - associative array
     *
     * Returns the result set as an array
     *
     * @access	private
     * @return	array
     */
    function _fetch_assoc() {
        return mysql_fetch_assoc($this->result_id);
    }

// --------------------------------------------------------------------

    /**
     * Result - object
     *
     * Returns the result set as an object
     *
     * @access	private
     * @return	object
     */
    function _fetch_object() {
        return mysql_fetch_object($this->result_id);
    }

}

/* End of file mysql_result.php */
/* Location: ./system/database/drivers/mysql/mysql_result.php */

/**
 * Database Driver Class
 *
 * This is the platform-independent base DB implementation class.
 * This class will not be called directly. Rather, the adapter
 * class for the specific database will extend and instantiate it.
 *
 * @package		CodeIgniter
 * @subpackage	Drivers
 * @category	Database
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_driver {

    var $username;
    var $password;
    var $hostname;
    var $database;
    var $dbdriver = 'mysql';
    var $dbprefix = '';
    var $char_set = 'utf8';
    var $dbcollat = 'utf8_general_ci';
    var $autoinit = TRUE; // Whether to automatically initialize the DB
    var $swap_pre = '';
    var $port = '';
    var $pconnect = FALSE;
    var $conn_id = FALSE;
    var $result_id = FALSE;
    var $db_debug = FALSE;
    var $benchmark = 0;
    var $query_count = 0;
    var $bind_marker = '?';
    var $save_queries = TRUE;
    var $queries = array();
    var $query_times = array();
    var $data_cache = array();
    var $trans_enabled = TRUE;
    var $trans_strict = TRUE;
    var $_trans_depth = 0;
    var $_trans_status = TRUE; // Used with transactions to determine if a rollback should occur
    var $cache_on = FALSE;
    var $cachedir = '';
    var $cache_autodel = FALSE;
    var $CACHE; // The cache class object
// Private variables
    var $_protect_identifiers = TRUE;
    var $_reserved_identifiers = array('*'); // Identifiers that should NOT be escaped
// These are use with Oracle
    var $stmt_id;
    var $curs_id;
    var $limit_used;

    /**
     * Constructor.  Accepts one parameter containing the database
     * connection settings.
     *
     * @param array
     */
    function __construct($params) {
        if (is_array($params)) {
            foreach ($params as $key => $val) {
                $this->$key = $val;
            }
        }

        log_message('debug', 'Database Driver Class Initialized');
    }

// --------------------------------------------------------------------

    /**
     * Initialize Database Settings
     *
     * @access	private Called by the constructor
     * @param	mixed
     * @return	void
     */
    function initialize() {
// If an existing connection resource is available
// there is no need to connect and select the database
        if (is_resource($this->conn_id) OR is_object($this->conn_id)) {
            return TRUE;
        }

// ----------------------------------------------------------------
// Connect to the database and set the connection ID
        $this->conn_id = ($this->pconnect == FALSE) ? $this->db_connect() : $this->db_pconnect();

// No connection resource?  Throw an error
        if (!$this->conn_id) {
            log_message('error', 'Unable to connect to the database');

            if ($this->db_debug) {
                $this->display_error('db_unable_to_connect');
            }
            return FALSE;
        }

// ----------------------------------------------------------------
// Select the DB... assuming a database name is specified in the config file
        if ($this->database != '') {
            if (!$this->db_select()) {
                log_message('error', 'Unable to select database: ' . $this->database);

                if ($this->db_debug) {
                    $this->display_error('db_unable_to_select', $this->database);
                }
                return FALSE;
            } else {
// We've selected the DB. Now we set the character set
                if (!$this->db_set_charset($this->char_set, $this->dbcollat)) {
                    return FALSE;
                }

                return TRUE;
            }
        }

        return TRUE;
    }

// --------------------------------------------------------------------

    /**
     * Set client character set
     *
     * @access	public
     * @param	string
     * @param	string
     * @return	resource
     */
    function db_set_charset($charset, $collation) {
        if (!$this->_db_set_charset($this->char_set, $this->dbcollat)) {
            log_message('error', 'Unable to set database connection charset: ' . $this->char_set);

            if ($this->db_debug) {
                $this->display_error('db_unable_to_set_charset', $this->char_set);
            }

            return FALSE;
        }

        return TRUE;
    }

// --------------------------------------------------------------------

    /**
     * The name of the platform in use (mysql, mssql, etc...)
     *
     * @access	public
     * @return	string
     */
    function platform() {
        return $this->dbdriver;
    }

// --------------------------------------------------------------------

    /**
     * Database Version Number.  Returns a string containing the
     * version of the database being used
     *
     * @access	public
     * @return	string
     */
    function version() {
        if (FALSE === ($sql = $this->_version())) {
            if ($this->db_debug) {
                return $this->display_error('db_unsupported_function');
            }
            return FALSE;
        }

// Some DBs have functions that return the version, and don't run special
// SQL queries per se. In these instances, just return the result.
        $driver_version_exceptions = array('oci8', 'sqlite', 'cubrid');

        if (in_array($this->dbdriver, $driver_version_exceptions)) {
            return $sql;
        } else {
            $query = $this->query($sql);
            return $query->row('ver');
        }
    }

// --------------------------------------------------------------------

    /**
     * Execute the query
     *
     * Accepts an SQL string as input and returns a result object upon
     * successful execution of a "read" type query.  Returns boolean TRUE
     * upon successful execution of a "write" type query. Returns boolean
     * FALSE upon failure, and if the $db_debug variable is set to TRUE
     * will raise an error.
     *
     * @access	public
     * @param	string	An SQL query string
     * @param	array	An array of binding data
     * @return	mixed
     */
    function query($sql, $binds = FALSE, $return_object = TRUE) {
        if ($sql == '') {
            if ($this->db_debug) {
                log_message('error', 'Invalid query: ' . $sql);
                return $this->display_error('db_invalid_query');
            }
            return FALSE;
        }

// Verify table prefix and replace if necessary
        if (($this->dbprefix != '' AND $this->swap_pre != '') AND ($this->dbprefix != $this->swap_pre)) {
            $sql = preg_replace("/(\W)" . $this->swap_pre . "(\S+?)/", "\\1" . $this->dbprefix . "\\2", $sql);
        }

// Compile binds if needed
        if ($binds !== FALSE) {
            $sql = $this->compile_binds($sql, $binds);
        }

// Is query caching enabled?  If the query is a "read type"
// we will load the caching class and return the previously
// cached query if it exists
        if ($this->cache_on == TRUE AND stristr($sql, 'SELECT')) {
            if ($this->_cache_init()) {
                $this->load_rdriver();
                if (FALSE !== ($cache = $this->CACHE->read($sql))) {
                    return $cache;
                }
            }
        }

// Save the  query for debugging
        if ($this->save_queries == TRUE) {
            $this->queries[] = $sql;
        }

// Start the Query Timer
        $time_start = list($sm, $ss) = explode(' ', microtime());

// Run the Query
        if (FALSE === ($this->result_id = $this->simple_query($sql))) {
            if ($this->save_queries == TRUE) {
                $this->query_times[] = 0;
            }

// This will trigger a rollback if transactions are being used
            $this->_trans_status = FALSE;

            if ($this->db_debug) {
// grab the error number and message now, as we might run some
// additional queries before displaying the error
                $error_no = $this->_error_number();
                $error_msg = $this->_error_message();

// We call this function in order to roll-back queries
// if transactions are enabled.  If we don't call this here
// the error message will trigger an exit, causing the
// transactions to remain in limbo.
                $this->trans_complete();

// Log and display errors
                log_message('error', 'Query error: ' . $error_msg);
                return $this->display_error(
                                array(
                                    'Error Number: ' . $error_no,
                                    $error_msg,
                                    $sql
                                )
                );
            }

            return FALSE;
        }

// Stop and aggregate the query time results
        $time_end = list($em, $es) = explode(' ', microtime());
        $this->benchmark += ($em + $es) - ($sm + $ss);

        if ($this->save_queries == TRUE) {
            $this->query_times[] = ($em + $es) - ($sm + $ss);
        }

// Increment the query counter
        $this->query_count++;

// Was the query a "write" type?
// If so we'll simply return true
        if ($this->is_write_type($sql) === TRUE) {
// If caching is enabled we'll auto-cleanup any
// existing files related to this particular URI
            if ($this->cache_on == TRUE AND $this->cache_autodel == TRUE AND $this->_cache_init()) {
                $this->CACHE->delete();
            }

            return TRUE;
        }

// Return TRUE if we don't need to create a result object
// Currently only the Oracle driver uses this when stored
// procedures are used
        if ($return_object !== TRUE) {
            return TRUE;
        }

// Load and instantiate the result driver

        $driver = $this->load_rdriver();
        $RES = new $driver();
        $RES->conn_id = $this->conn_id;
        $RES->result_id = $this->result_id;

        if ($this->dbdriver == 'oci8') {
            $RES->stmt_id = $this->stmt_id;
            $RES->curs_id = NULL;
            $RES->limit_used = $this->limit_used;
            $this->stmt_id = FALSE;
        }

// oci8 vars must be set before calling this
        $RES->num_rows = $RES->num_rows();

// Is query caching enabled?  If so, we'll serialize the
// result object and save it to a cache file.
        if ($this->cache_on == TRUE AND $this->_cache_init()) {
// We'll create a new instance of the result object
// only without the platform specific driver since
// we can't use it with cached data (the query result
// resource ID won't be any good once we've cached the
// result object, so we'll have to compile the data
// and save it)
            $CR = new CI_DB_result();
            $CR->num_rows = $RES->num_rows();
            $CR->result_object = $RES->result_object();
            $CR->result_array = $RES->result_array();

// Reset these since cached objects can not utilize resource IDs.
            $CR->conn_id = NULL;
            $CR->result_id = NULL;

            $this->CACHE->write($sql, $CR);
        }

        return $RES;
    }

// --------------------------------------------------------------------

    /**
     * Load the result drivers
     *
     * @access	public
     * @return	string	the name of the result class
     */
    function load_rdriver() {
        $driver = 'CI_DB_' . $this->dbdriver . '_result';

        if (!class_exists($driver)) {
            include_once(BASEPATH . 'database/DB_result.php');
            include_once(BASEPATH . 'database/drivers/' . $this->dbdriver . '/' . $this->dbdriver . '_result.php');
        }

        return $driver;
    }

// --------------------------------------------------------------------

    /**
     * Simple Query
     * This is a simplified version of the query() function.  Internally
     * we only use it when running transaction commands since they do
     * not require all the features of the main query() function.
     *
     * @access	public
     * @param	string	the sql query
     * @return	mixed
     */
    function simple_query($sql) {
        if (!$this->conn_id) {
            $this->initialize();
        }

        return $this->_execute($sql);
    }

// --------------------------------------------------------------------

    /**
     * Disable Transactions
     * This permits transactions to be disabled at run-time.
     *
     * @access	public
     * @return	void
     */
    function trans_off() {
        $this->trans_enabled = FALSE;
    }

// --------------------------------------------------------------------

    /**
     * Enable/disable Transaction Strict Mode
     * When strict mode is enabled, if you are running multiple groups of
     * transactions, if one group fails all groups will be rolled back.
     * If strict mode is disabled, each group is treated autonomously, meaning
     * a failure of one group will not affect any others
     *
     * @access	public
     * @return	void
     */
    function trans_strict($mode = TRUE) {
        $this->trans_strict = is_bool($mode) ? $mode : TRUE;
    }

// --------------------------------------------------------------------

    /**
     * Start Transaction
     *
     * @access	public
     * @return	void
     */
    function trans_start($test_mode = FALSE) {
        if (!$this->trans_enabled) {
            return FALSE;
        }

// When transactions are nested we only begin/commit/rollback the outermost ones
        if ($this->_trans_depth > 0) {
            $this->_trans_depth += 1;
            return;
        }

        $this->trans_begin($test_mode);
    }

// --------------------------------------------------------------------

    /**
     * Complete Transaction
     *
     * @access	public
     * @return	bool
     */
    function trans_complete() {
        if (!$this->trans_enabled) {
            return FALSE;
        }

// When transactions are nested we only begin/commit/rollback the outermost ones
        if ($this->_trans_depth > 1) {
            $this->_trans_depth -= 1;
            return TRUE;
        }

// The query() function will set this flag to FALSE in the event that a query failed
        if ($this->_trans_status === FALSE) {
            $this->trans_rollback();

// If we are NOT running in strict mode, we will reset
// the _trans_status flag so that subsequent groups of transactions
// will be permitted.
            if ($this->trans_strict === FALSE) {
                $this->_trans_status = TRUE;
            }

            log_message('debug', 'DB Transaction Failure');
            return FALSE;
        }

        $this->trans_commit();
        return TRUE;
    }

// --------------------------------------------------------------------

    /**
     * Lets you retrieve the transaction flag to determine if it has failed
     *
     * @access	public
     * @return	bool
     */
    function trans_status() {
        return $this->_trans_status;
    }

// --------------------------------------------------------------------

    /**
     * Compile Bindings
     *
     * @access	public
     * @param	string	the sql statement
     * @param	array	an array of bind data
     * @return	string
     */
    function compile_binds($sql, $binds) {
        if (strpos($sql, $this->bind_marker) === FALSE) {
            return $sql;
        }

        if (!is_array($binds)) {
            $binds = array($binds);
        }

// Get the sql segments around the bind markers
        $segments = explode($this->bind_marker, $sql);

// The count of bind should be 1 less then the count of segments
// If there are more bind arguments trim it down
        if (count($binds) >= count($segments)) {
            $binds = array_slice($binds, 0, count($segments) - 1);
        }

// Construct the binded query
        $result = $segments[0];
        $i = 0;
        foreach ($binds as $bind) {
            $result .= $this->escape($bind);
            $result .= $segments[++$i];
        }

        return $result;
    }

// --------------------------------------------------------------------

    /**
     * Determines if a query is a "write" type.
     *
     * @access	public
     * @param	string	An SQL query string
     * @return	boolean
     */
    function is_write_type($sql) {
        if (!preg_match('/^\s*"?(SET|INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|TRUNCATE|LOAD DATA|COPY|ALTER|GRANT|REVOKE|LOCK|UNLOCK)\s+/i', $sql)) {
            return FALSE;
        }
        return TRUE;
    }

// --------------------------------------------------------------------

    /**
     * Calculate the aggregate query elapsed time
     *
     * @access	public
     * @param	integer	The number of decimal places
     * @return	integer
     */
    function elapsed_time($decimals = 6) {
        return number_format($this->benchmark, $decimals);
    }

// --------------------------------------------------------------------

    /**
     * Returns the total number of queries
     *
     * @access	public
     * @return	integer
     */
    function total_queries() {
        return $this->query_count;
    }

// --------------------------------------------------------------------

    /**
     * Returns the last query that was executed
     *
     * @access	public
     * @return	void
     */
    function last_query() {
        return end($this->queries);
    }

// --------------------------------------------------------------------

    /**
     * "Smart" Escape String
     *
     * Escapes data based on type
     * Sets boolean and null types
     *
     * @access	public
     * @param	string
     * @return	mixed
     */
    function escape($str) {
        if (is_string($str)) {
            $str = "'" . $this->escape_str($str) . "'";
        } elseif (is_bool($str)) {
            $str = ($str === FALSE) ? 0 : 1;
        } elseif (is_null($str)) {
            $str = 'NULL';
        }

        return $str;
    }

// --------------------------------------------------------------------

    /**
     * Escape LIKE String
     *
     * Calls the individual driver for platform
     * specific escaping for LIKE conditions
     *
     * @access	public
     * @param	string
     * @return	mixed
     */
    function escape_like_str($str) {
        return $this->escape_str($str, TRUE);
    }

// --------------------------------------------------------------------

    /**
     * Primary
     *
     * Retrieves the primary key.  It assumes that the row in the first
     * position is the primary key
     *
     * @access	public
     * @param	string	the table name
     * @return	string
     */
    function primary($table = '') {
        $fields = $this->list_fields($table);

        if (!is_array($fields)) {
            return FALSE;
        }

        return current($fields);
    }

// --------------------------------------------------------------------

    /**
     * Returns an array of table names
     *
     * @access	public
     * @return	array
     */
    function list_tables($constrain_by_prefix = FALSE) {
// Is there a cached result?
        if (isset($this->data_cache['table_names'])) {
            return $this->data_cache['table_names'];
        }

        if (FALSE === ($sql = $this->_list_tables($constrain_by_prefix))) {
            if ($this->db_debug) {
                return $this->display_error('db_unsupported_function');
            }
            return FALSE;
        }

        $retval = array();
        $query = $this->query($sql);

        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                if (isset($row['TABLE_NAME'])) {
                    $retval[] = $row['TABLE_NAME'];
                } else {
                    $retval[] = array_shift($row);
                }
            }
        }

        $this->data_cache['table_names'] = $retval;
        return $this->data_cache['table_names'];
    }

// --------------------------------------------------------------------

    /**
     * Determine if a particular table exists
     * @access	public
     * @return	boolean
     */
    function table_exists($table_name) {
        return (!in_array($this->_protect_identifiers($table_name, TRUE, FALSE, FALSE), $this->list_tables())) ? FALSE : TRUE;
    }

// --------------------------------------------------------------------

    /**
     * Fetch MySQL Field Names
     *
     * @access	public
     * @param	string	the table name
     * @return	array
     */
    function list_fields($table = '') {
// Is there a cached result?
        if (isset($this->data_cache['field_names'][$table])) {
            return $this->data_cache['field_names'][$table];
        }

        if ($table == '') {
            if ($this->db_debug) {
                return $this->display_error('db_field_param_missing');
            }
            return FALSE;
        }

        if (FALSE === ($sql = $this->_list_columns($table))) {
            if ($this->db_debug) {
                return $this->display_error('db_unsupported_function');
            }
            return FALSE;
        }

        $query = $this->query($sql);

        $retval = array();
        foreach ($query->result_array() as $row) {
            if (isset($row['COLUMN_NAME'])) {
                $retval[] = $row['COLUMN_NAME'];
            } else {
                $retval[] = current($row);
            }
        }

        $this->data_cache['field_names'][$table] = $retval;
        return $this->data_cache['field_names'][$table];
    }

// --------------------------------------------------------------------

    /**
     * Determine if a particular field exists
     * @access	public
     * @param	string
     * @param	string
     * @return	boolean
     */
    function field_exists($field_name, $table_name) {
        return (!in_array($field_name, $this->list_fields($table_name))) ? FALSE : TRUE;
    }

// --------------------------------------------------------------------

    /**
     * Returns an object with field data
     *
     * @access	public
     * @param	string	the table name
     * @return	object
     */
    function field_data($table = '') {
        if ($table == '') {
            if ($this->db_debug) {
                return $this->display_error('db_field_param_missing');
            }
            return FALSE;
        }

        $query = $this->query($this->_field_data($this->_protect_identifiers($table, TRUE, NULL, FALSE)));

        return $query->field_data();
    }

// --------------------------------------------------------------------

    /**
     * Generate an insert string
     *
     * @access	public
     * @param	string	the table upon which the query will be performed
     * @param	array	an associative array data of key/values
     * @return	string
     */
    function insert_string($table, $data) {
        $fields = array();
        $values = array();

        foreach ($data as $key => $val) {
            $fields[] = $this->_escape_identifiers($key);
            $values[] = $this->escape($val);
        }

        return $this->_insert($this->_protect_identifiers($table, TRUE, NULL, FALSE), $fields, $values);
    }

// --------------------------------------------------------------------

    /**
     * Generate an update string
     *
     * @access	public
     * @param	string	the table upon which the query will be performed
     * @param	array	an associative array data of key/values
     * @param	mixed	the "where" statement
     * @return	string
     */
    function update_string($table, $data, $where) {
        if ($where == '') {
            return false;
        }

        $fields = array();
        foreach ($data as $key => $val) {
            $fields[$this->_protect_identifiers($key)] = $this->escape($val);
        }

        if (!is_array($where)) {
            $dest = array($where);
        } else {
            $dest = array();
            foreach ($where as $key => $val) {
                $prefix = (count($dest) == 0) ? '' : ' AND ';

                if ($val !== '') {
                    if (!$this->_has_operator($key)) {
                        $key .= ' =';
                    }

                    $val = ' ' . $this->escape($val);
                }

                $dest[] = $prefix . $key . $val;
            }
        }

        return $this->_update($this->_protect_identifiers($table, TRUE, NULL, FALSE), $fields, $dest);
    }

// --------------------------------------------------------------------

    /**
     * Tests whether the string has an SQL operator
     *
     * @access	private
     * @param	string
     * @return	bool
     */
    function _has_operator($str) {
        $str = trim($str);
        if (!preg_match("/(\s|<|>|!|=|is null|is not null)/i", $str)) {
            return FALSE;
        }

        return TRUE;
    }

// --------------------------------------------------------------------

    /**
     * Enables a native PHP function to be run, using a platform agnostic wrapper.
     *
     * @access	public
     * @param	string	the function name
     * @param	mixed	any parameters needed by the function
     * @return	mixed
     */
    function call_function($function) {
        $driver = ($this->dbdriver == 'postgre') ? 'pg_' : $this->dbdriver . '_';

        if (FALSE === strpos($driver, $function)) {
            $function = $driver . $function;
        }

        if (!function_exists($function)) {
            if ($this->db_debug) {
                return $this->display_error('db_unsupported_function');
            }
            return FALSE;
        } else {
            $args = (func_num_args() > 1) ? array_splice(func_get_args(), 1) : null;
            if (is_null($args)) {
                return call_user_func($function);
            } else {
                return call_user_func_array($function, $args);
            }
        }
    }

// --------------------------------------------------------------------

    /**
     * Set Cache Directory Path
     *
     * @access	public
     * @param	string	the path to the cache directory
     * @return	void
     */
    function cache_set_path($path = '') {
        $this->cachedir = $path;
    }

// --------------------------------------------------------------------

    /**
     * Enable Query Caching
     *
     * @access	public
     * @return	void
     */
    function cache_on() {
        $this->cache_on = TRUE;
        return TRUE;
    }

// --------------------------------------------------------------------

    /**
     * Disable Query Caching
     *
     * @access	public
     * @return	void
     */
    function cache_off() {
        $this->cache_on = FALSE;
        return FALSE;
    }

// --------------------------------------------------------------------

    /**
     * Delete the cache files associated with a particular URI
     *
     * @access	public
     * @return	void
     */
    function cache_delete($segment_one = '', $segment_two = '') {
        if (!$this->_cache_init()) {
            return FALSE;
        }
        return $this->CACHE->delete($segment_one, $segment_two);
    }

// --------------------------------------------------------------------

    /**
     * Delete All cache files
     *
     * @access	public
     * @return	void
     */
    function cache_delete_all() {
        if (!$this->_cache_init()) {
            return FALSE;
        }

        return $this->CACHE->delete_all();
    }

// --------------------------------------------------------------------

    /**
     * Initialize the Cache Class
     *
     * @access	private
     * @return	void
     */
    function _cache_init() {
        if (is_object($this->CACHE) AND class_exists('CI_DB_Cache')) {
            return TRUE;
        }

        if (!class_exists('CI_DB_Cache')) {
            if (!@include(BASEPATH . 'database/DB_cache.php')) {
                return $this->cache_off();
            }
        }

        $this->CACHE = new CI_DB_Cache($this); // pass db object to support multiple db connections and returned db objects
        return TRUE;
    }

// --------------------------------------------------------------------

    /**
     * Close DB Connection
     *
     * @access	public
     * @return	void
     */
    function close() {
        if (is_resource($this->conn_id) OR is_object($this->conn_id)) {
            $this->_close($this->conn_id);
        }
        $this->conn_id = FALSE;
    }

// --------------------------------------------------------------------

    /**
     * Display an error message
     *
     * @access	public
     * @param	string	the error message
     * @param	string	any "swap" values
     * @param	boolean	whether to localize the message
     * @return	string	sends the application/error_db.php template
     */
    function display_error($error = '', $swap = '', $native = FALSE) {
        $msg = '';
        if (is_array($error)) {
            foreach ($error as $m) {
                $msg.=$m . "</br>";
            }
        } else {
            $msg = $error;
        }
        global $woniu_db, $system;
        if ($woniu_db[$woniu_db['active_group']]['db_debug']) {
            header('HTTP/1.1 500 Internal Server Database Error');
            if (!empty($system['error_page_db']) && file_exists($system['error_page_db'])) {
                include $system['error_page_db'];
            } else {
                echo $msg;
            }
        }
        exit;
    }

// --------------------------------------------------------------------

    /**
     * Protect Identifiers
     *
     * This function adds backticks if appropriate based on db type
     *
     * @access	private
     * @param	mixed	the item to escape
     * @return	mixed	the item with backticks
     */
    function protect_identifiers($item, $prefix_single = FALSE) {
        return $this->_protect_identifiers($item, $prefix_single);
    }

// --------------------------------------------------------------------

    /**
     * Protect Identifiers
     *
     * This function is used extensively by the Active Record class, and by
     * a couple functions in this class.
     * It takes a column or table name (optionally with an alias) and inserts
     * the table prefix onto it.  Some logic is necessary in order to deal with
     * column names that include the path.  Consider a query like this:
     *
     * SELECT * FROM hostname.database.table.column AS c FROM hostname.database.table
     *
     * Or a query with aliasing:
     *
     * SELECT m.member_id, m.member_name FROM members AS m
     *
     * Since the column name can include up to four segments (host, DB, table, column)
     * or also have an alias prefix, we need to do a bit of work to figure this out and
     * insert the table prefix (if it exists) in the proper position, and escape only
     * the correct identifiers.
     *
     * @access	private
     * @param	string
     * @param	bool
     * @param	mixed
     * @param	bool
     * @return	string
     */
    function _protect_identifiers($item, $prefix_single = FALSE, $protect_identifiers = NULL, $field_exists = TRUE) {
        if (!is_bool($protect_identifiers)) {
            $protect_identifiers = $this->_protect_identifiers;
        }

        if (is_array($item)) {
            $escaped_array = array();

            foreach ($item as $k => $v) {
                $escaped_array[$this->_protect_identifiers($k)] = $this->_protect_identifiers($v);
            }

            return $escaped_array;
        }

// Convert tabs or multiple spaces into single spaces
        $item = preg_replace('/[\t ]+/', ' ', $item);

// If the item has an alias declaration we remove it and set it aside.
// Basically we remove everything to the right of the first space
        if (strpos($item, ' ') !== FALSE) {
            $alias = strstr($item, ' ');
            $item = substr($item, 0, - strlen($alias));
        } else {
            $alias = '';
        }

// This is basically a bug fix for queries that use MAX, MIN, etc.
// If a parenthesis is found we know that we do not need to
// escape the data or add a prefix.  There's probably a more graceful
// way to deal with this, but I'm not thinking of it -- Rick
        if (strpos($item, '(') !== FALSE) {
            return $item . $alias;
        }

// Break the string apart if it contains periods, then insert the table prefix
// in the correct location, assuming the period doesn't indicate that we're dealing
// with an alias. While we're at it, we will escape the components
        if (strpos($item, '.') !== FALSE) {
            $parts = explode('.', $item);

// Does the first segment of the exploded item match
// one of the aliases previously identified?  If so,
// we have nothing more to do other than escape the item
            if (in_array($parts[0], $this->ar_aliased_tables)) {
                if ($protect_identifiers === TRUE) {
                    foreach ($parts as $key => $val) {
                        if (!in_array($val, $this->_reserved_identifiers)) {
                            $parts[$key] = $this->_escape_identifiers($val);
                        }
                    }

                    $item = implode('.', $parts);
                }
                return $item . $alias;
            }

// Is there a table prefix defined in the config file?  If not, no need to do anything
            if ($this->dbprefix != '') {
// We now add the table prefix based on some logic.
// Do we have 4 segments (hostname.database.table.column)?
// If so, we add the table prefix to the column name in the 3rd segment.
                if (isset($parts[3])) {
                    $i = 2;
                }
// Do we have 3 segments (database.table.column)?
// If so, we add the table prefix to the column name in 2nd position
                elseif (isset($parts[2])) {
                    $i = 1;
                }
// Do we have 2 segments (table.column)?
// If so, we add the table prefix to the column name in 1st segment
                else {
                    $i = 0;
                }

// This flag is set when the supplied $item does not contain a field name.
// This can happen when this function is being called from a JOIN.
                if ($field_exists == FALSE) {
                    $i++;
                }

// Verify table prefix and replace if necessary
                if ($this->swap_pre != '' && strncmp($parts[$i], $this->swap_pre, strlen($this->swap_pre)) === 0) {
                    $parts[$i] = preg_replace("/^" . $this->swap_pre . "(\S+?)/", $this->dbprefix . "\\1", $parts[$i]);
                }

// We only add the table prefix if it does not already exist
                if (substr($parts[$i], 0, strlen($this->dbprefix)) != $this->dbprefix) {
                    $parts[$i] = $this->dbprefix . $parts[$i];
                }

// Put the parts back together
                $item = implode('.', $parts);
            }

            if ($protect_identifiers === TRUE) {
                $item = $this->_escape_identifiers($item);
            }

            return $item . $alias;
        }

// Is there a table prefix?  If not, no need to insert it
        if ($this->dbprefix != '') {
// Verify table prefix and replace if necessary
            if ($this->swap_pre != '' && strncmp($item, $this->swap_pre, strlen($this->swap_pre)) === 0) {
                $item = preg_replace("/^" . $this->swap_pre . "(\S+?)/", $this->dbprefix . "\\1", $item);
            }

// Do we prefix an item with no segments?
            if ($prefix_single == TRUE AND substr($item, 0, strlen($this->dbprefix)) != $this->dbprefix) {
                $item = $this->dbprefix . $item;
            }
        }

        if ($protect_identifiers === TRUE AND !in_array($item, $this->_reserved_identifiers)) {
            $item = $this->_escape_identifiers($item);
        }

        return $item . $alias;
    }

// --------------------------------------------------------------------

    /**
     * Dummy method that allows Active Record class to be disabled
     *
     * This function is used extensively by every db driver.
     *
     * @return	void
     */
    protected function _reset_select() {
        
    }

}

/* End of file DB_driver.php */
/* Location: ./system/database/DB_driver.php */


// ------------------------------------------------------------------------

/**
 * Database Result Class
 *
 * This is the platform-independent result class.
 * This class will not be called directly. Rather, the adapter
 * class for the specific database will extend and instantiate it.
 *
 * @category	Database
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_result {

    var $conn_id = NULL;
    var $result_id = NULL;
    var $result_array = array();
    var $result_object = array();
    var $custom_result_object = array();
    var $current_row = 0;
    var $num_rows = 0;
    var $row_data = NULL;

    /**
     * Query result.  Acts as a wrapper function for the following functions.
     *
     * @access	public
     * @param	string	can be "object" or "array"
     * @return	mixed	either a result object or array
     */
    public function result($type = 'object') {
        if ($type == 'array')
            return $this->result_array();
        else if ($type == 'object')
            return $this->result_object();
        else
            return $this->custom_result_object($type);
    }

// --------------------------------------------------------------------

    /**
     * Custom query result.
     *
     * @param class_name A string that represents the type of object you want back
     * @return array of objects
     */
    public function custom_result_object($class_name) {
        if (array_key_exists($class_name, $this->custom_result_object)) {
            return $this->custom_result_object[$class_name];
        }

        if ($this->result_id === FALSE OR $this->num_rows() == 0) {
            return array();
        }

// add the data to the object
        $this->_data_seek(0);
        $result_object = array();

        while ($row = $this->_fetch_object()) {
            $object = new $class_name();

            foreach ($row as $key => $value) {
                $object->$key = $value;
            }

            $result_object[] = $object;
        }

// return the array
        return $this->custom_result_object[$class_name] = $result_object;
    }

// --------------------------------------------------------------------

    /**
     * Query result.  "object" version.
     *
     * @access	public
     * @return	object
     */
    public function result_object() {
        if (count($this->result_object) > 0) {
            return $this->result_object;
        }

// In the event that query caching is on the result_id variable
// will return FALSE since there isn't a valid SQL resource so
// we'll simply return an empty array.
        if ($this->result_id === FALSE OR $this->num_rows() == 0) {
            return array();
        }

        $this->_data_seek(0);
        while ($row = $this->_fetch_object()) {
            $this->result_object[] = $row;
        }

        return $this->result_object;
    }

// --------------------------------------------------------------------

    /**
     * Query result.  "array" version.
     *
     * @access	public
     * @return	array
     */
    public function result_array() {
        if (count($this->result_array) > 0) {
            return $this->result_array;
        }

// In the event that query caching is on the result_id variable
// will return FALSE since there isn't a valid SQL resource so
// we'll simply return an empty array.
        if ($this->result_id === FALSE OR $this->num_rows() == 0) {
            return array();
        }

        $this->_data_seek(0);
        while ($row = $this->_fetch_assoc()) {
            $this->result_array[] = $row;
        }

        return $this->result_array;
    }

// --------------------------------------------------------------------

    /**
     * Query result.  Acts as a wrapper function for the following functions.
     *
     * @access	public
     * @param	string
     * @param	string	can be "object" or "array"
     * @return	mixed	either a result object or array
     */
    public function row($n = 0, $type = 'object') {
        if (!is_numeric($n)) {
// We cache the row data for subsequent uses
            if (!is_array($this->row_data)) {
                $this->row_data = $this->row_array(0);
            }

// array_key_exists() instead of isset() to allow for MySQL NULL values
            if (array_key_exists($n, $this->row_data)) {
                return $this->row_data[$n];
            }
// reset the $n variable if the result was not achieved
            $n = 0;
        }

        if ($type == 'object')
            return $this->row_object($n);
        else if ($type == 'array')
            return $this->row_array($n);
        else
            return $this->custom_row_object($n, $type);
    }

// --------------------------------------------------------------------

    /**
     * Assigns an item into a particular column slot
     *
     * @access	public
     * @return	object
     */
    public function set_row($key, $value = NULL) {
// We cache the row data for subsequent uses
        if (!is_array($this->row_data)) {
            $this->row_data = $this->row_array(0);
        }

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->row_data[$k] = $v;
            }

            return;
        }

        if ($key != '' AND !is_null($value)) {
            $this->row_data[$key] = $value;
        }
    }

// --------------------------------------------------------------------

    /**
     * Returns a single result row - custom object version
     *
     * @access	public
     * @return	object
     */
    public function custom_row_object($n, $type) {
        $result = $this->custom_result_object($type);

        if (count($result) == 0) {
            return $result;
        }

        if ($n != $this->current_row AND isset($result[$n])) {
            $this->current_row = $n;
        }

        return $result[$this->current_row];
    }

    /**
     * Returns a single result row - object version
     *
     * @access	public
     * @return	object
     */
    public function row_object($n = 0) {
        $result = $this->result_object();

        if (count($result) == 0) {
            return $result;
        }

        if ($n != $this->current_row AND isset($result[$n])) {
            $this->current_row = $n;
        }

        return $result[$this->current_row];
    }

// --------------------------------------------------------------------

    /**
     * Returns a single result row - array version
     *
     * @access	public
     * @return	array
     */
    public function row_array($n = 0) {
        $result = $this->result_array();

        if (count($result) == 0) {
            return $result;
        }

        if ($n != $this->current_row AND isset($result[$n])) {
            $this->current_row = $n;
        }

        return $result[$this->current_row];
    }

// --------------------------------------------------------------------

    /**
     * Returns the "first" row
     *
     * @access	public
     * @return	object
     */
    public function first_row($type = 'object') {
        $result = $this->result($type);

        if (count($result) == 0) {
            return $result;
        }
        return $result[0];
    }

// --------------------------------------------------------------------

    /**
     * Returns the "last" row
     *
     * @access	public
     * @return	object
     */
    public function last_row($type = 'object') {
        $result = $this->result($type);

        if (count($result) == 0) {
            return $result;
        }
        return $result[count($result) - 1];
    }

// --------------------------------------------------------------------

    /**
     * Returns the "next" row
     *
     * @access	public
     * @return	object
     */
    public function next_row($type = 'object') {
        $result = $this->result($type);

        if (count($result) == 0) {
            return $result;
        }

        if (isset($result[$this->current_row + 1])) {
            ++$this->current_row;
        }

        return $result[$this->current_row];
    }

// --------------------------------------------------------------------

    /**
     * Returns the "previous" row
     *
     * @access	public
     * @return	object
     */
    public function previous_row($type = 'object') {
        $result = $this->result($type);

        if (count($result) == 0) {
            return $result;
        }

        if (isset($result[$this->current_row - 1])) {
            --$this->current_row;
        }
        return $result[$this->current_row];
    }

// --------------------------------------------------------------------

    /**
     * The following functions are normally overloaded by the identically named
     * methods in the platform-specific driver -- except when query caching
     * is used.  When caching is enabled we do not load the other driver.
     * These functions are primarily here to prevent undefined function errors
     * when a cached result object is in use.  They are not otherwise fully
     * operational due to the unavailability of the database resource IDs with
     * cached results.
     */
    public function num_rows() {
        return $this->num_rows;
    }

    public function num_fields() {
        return 0;
    }

    public function list_fields() {
        return array();
    }

    public function field_data() {
        return array();
    }

    public function free_result() {
        return TRUE;
    }

    protected function _data_seek() {
        return TRUE;
    }

    protected function _fetch_assoc() {
        return array();
    }

    protected function _fetch_object() {
        return array();
    }

}

// END DB_result class

/* End of file DB_result.php */
/* Location: ./system/database/DB_result.php */

// ------------------------------------------------------------------------

/**
 * Active Record Class
 *
 * This is the platform-independent base Active Record implementation class.
 *
 * @package		CodeIgniter
 * @subpackage	Drivers
 * @category	Database
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_active_record extends CI_DB_driver {

    var $ar_select = array();
    var $ar_distinct = FALSE;
    var $ar_from = array();
    var $ar_join = array();
    var $ar_where = array();
    var $ar_like = array();
    var $ar_groupby = array();
    var $ar_having = array();
    var $ar_keys = array();
    var $ar_limit = FALSE;
    var $ar_offset = FALSE;
    var $ar_order = FALSE;
    var $ar_orderby = array();
    var $ar_set = array();
    var $ar_wherein = array();
    var $ar_aliased_tables = array();
    var $ar_store_array = array();
// Active Record Caching variables
    var $ar_caching = FALSE;
    var $ar_cache_exists = array();
    var $ar_cache_select = array();
    var $ar_cache_from = array();
    var $ar_cache_join = array();
    var $ar_cache_where = array();
    var $ar_cache_like = array();
    var $ar_cache_groupby = array();
    var $ar_cache_having = array();
    var $ar_cache_orderby = array();
    var $ar_cache_set = array();
    var $ar_no_escape = array();
    var $ar_cache_no_escape = array();

// --------------------------------------------------------------------

    /**
     * Select
     *
     * Generates the SELECT portion of the query
     *
     * @param	string
     * @return	object
     */
    public function select($select = '*', $escape = NULL) {
        if (is_string($select)) {
            $select = explode(',', $select);
        }

        foreach ($select as $val) {
            $val = trim($val);

            if ($val != '') {
                $this->ar_select[] = $val;
                $this->ar_no_escape[] = $escape;

                if ($this->ar_caching === TRUE) {
                    $this->ar_cache_select[] = $val;
                    $this->ar_cache_exists[] = 'select';
                    $this->ar_cache_no_escape[] = $escape;
                }
            }
        }
        return $this;
    }

// --------------------------------------------------------------------

    /**
     * Select Max
     *
     * Generates a SELECT MAX(field) portion of a query
     *
     * @param	string	the field
     * @param	string	an alias
     * @return	object
     */
    public function select_max($select = '', $alias = '') {
        return $this->_max_min_avg_sum($select, $alias, 'MAX');
    }

// --------------------------------------------------------------------

    /**
     * Select Min
     *
     * Generates a SELECT MIN(field) portion of a query
     *
     * @param	string	the field
     * @param	string	an alias
     * @return	object
     */
    public function select_min($select = '', $alias = '') {
        return $this->_max_min_avg_sum($select, $alias, 'MIN');
    }

// --------------------------------------------------------------------

    /**
     * Select Average
     *
     * Generates a SELECT AVG(field) portion of a query
     *
     * @param	string	the field
     * @param	string	an alias
     * @return	object
     */
    public function select_avg($select = '', $alias = '') {
        return $this->_max_min_avg_sum($select, $alias, 'AVG');
    }

// --------------------------------------------------------------------

    /**
     * Select Sum
     *
     * Generates a SELECT SUM(field) portion of a query
     *
     * @param	string	the field
     * @param	string	an alias
     * @return	object
     */
    public function select_sum($select = '', $alias = '') {
        return $this->_max_min_avg_sum($select, $alias, 'SUM');
    }

// --------------------------------------------------------------------

    /**
     * Processing Function for the four functions above:
     *
     * 	select_max()
     * 	select_min()
     * 	select_avg()
     *  select_sum()
     *
     * @param	string	the field
     * @param	string	an alias
     * @return	object
     */
    protected function _max_min_avg_sum($select = '', $alias = '', $type = 'MAX') {
        if (!is_string($select) OR $select == '') {
            $this->display_error('db_invalid_query');
        }

        $type = strtoupper($type);

        if (!in_array($type, array('MAX', 'MIN', 'AVG', 'SUM'))) {
            show_error('Invalid function type: ' . $type);
        }

        if ($alias == '') {
            $alias = $this->_create_alias_from_table(trim($select));
        }

        $sql = $type . '(' . $this->_protect_identifiers(trim($select)) . ') AS ' . $alias;

        $this->ar_select[] = $sql;

        if ($this->ar_caching === TRUE) {
            $this->ar_cache_select[] = $sql;
            $this->ar_cache_exists[] = 'select';
        }

        return $this;
    }

// --------------------------------------------------------------------

    /**
     * Determines the alias name based on the table
     *
     * @param	string
     * @return	string
     */
    protected function _create_alias_from_table($item) {
        if (strpos($item, '.') !== FALSE) {
            return end(explode('.', $item));
        }

        return $item;
    }

// --------------------------------------------------------------------

    /**
     * DISTINCT
     *
     * Sets a flag which tells the query string compiler to add DISTINCT
     *
     * @param	bool
     * @return	object
     */
    public function distinct($val = TRUE) {
        $this->ar_distinct = (is_bool($val)) ? $val : TRUE;
        return $this;
    }

// --------------------------------------------------------------------

    /**
     * From
     *
     * Generates the FROM portion of the query
     *
     * @param	mixed	can be a string or array
     * @return	object
     */
    public function from($from) {
        foreach ((array) $from as $val) {
            if (strpos($val, ',') !== FALSE) {
                foreach (explode(',', $val) as $v) {
                    $v = trim($v);
                    $this->_track_aliases($v);

                    $this->ar_from[] = $this->_protect_identifiers($v, TRUE, NULL, FALSE);

                    if ($this->ar_caching === TRUE) {
                        $this->ar_cache_from[] = $this->_protect_identifiers($v, TRUE, NULL, FALSE);
                        $this->ar_cache_exists[] = 'from';
                    }
                }
            } else {
                $val = trim($val);

// Extract any aliases that might exist.  We use this information
// in the _protect_identifiers to know whether to add a table prefix
                $this->_track_aliases($val);

                $this->ar_from[] = $this->_protect_identifiers($val, TRUE, NULL, FALSE);

                if ($this->ar_caching === TRUE) {
                    $this->ar_cache_from[] = $this->_protect_identifiers($val, TRUE, NULL, FALSE);
                    $this->ar_cache_exists[] = 'from';
                }
            }
        }

        return $this;
    }

// --------------------------------------------------------------------

    /**
     * Join
     *
     * Generates the JOIN portion of the query
     *
     * @param	string
     * @param	string	the join condition
     * @param	string	the type of join
     * @return	object
     */
    public function join($table, $cond, $type = '') {
        if ($type != '') {
            $type = strtoupper(trim($type));

            if (!in_array($type, array('LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER'))) {
                $type = '';
            } else {
                $type .= ' ';
            }
        }

// Extract any aliases that might exist.  We use this information
// in the _protect_identifiers to know whether to add a table prefix
        $this->_track_aliases($table);

// Strip apart the condition and protect the identifiers
        if (preg_match('/([\w\.]+)([\W\s]+)(.+)/', $cond, $match)) {
            $match[1] = $this->_protect_identifiers($match[1]);
            $match[3] = $this->_protect_identifiers($match[3]);

            $cond = $match[1] . $match[2] . $match[3];
        }

// Assemble the JOIN statement
        $join = $type . 'JOIN ' . $this->_protect_identifiers($table, TRUE, NULL, FALSE) . ' ON ' . $cond;

        $this->ar_join[] = $join;
        if ($this->ar_caching === TRUE) {
            $this->ar_cache_join[] = $join;
            $this->ar_cache_exists[] = 'join';
        }

        return $this;
    }

// --------------------------------------------------------------------

    /**
     * Where
     *
     * Generates the WHERE portion of the query. Separates
     * multiple calls with AND
     *
     * @param	mixed
     * @param	mixed
     * @return	object
     */
    public function where($key, $value = NULL, $escape = TRUE) {
        return $this->_where($key, $value, 'AND ', $escape);
    }

// --------------------------------------------------------------------

    /**
     * OR Where
     *
     * Generates the WHERE portion of the query. Separates
     * multiple calls with OR
     *
     * @param	mixed
     * @param	mixed
     * @return	object
     */
    public function or_where($key, $value = NULL, $escape = TRUE) {
        return $this->_where($key, $value, 'OR ', $escape);
    }

// --------------------------------------------------------------------

    /**
     * Where
     *
     * Called by where() or or_where()
     *
     * @param	mixed
     * @param	mixed
     * @param	string
     * @return	object
     */
    protected function _where($key, $value = NULL, $type = 'AND ', $escape = NULL) {
        if (!is_array($key)) {
            $key = array($key => $value);
        }

// If the escape value was not set will will base it on the global setting
        if (!is_bool($escape)) {
            $escape = $this->_protect_identifiers;
        }

        foreach ($key as $k => $v) {
            $prefix = (count($this->ar_where) == 0 AND count($this->ar_cache_where) == 0) ? '' : $type;

            if (is_null($v) && !$this->_has_operator($k)) {
// value appears not to have been set, assign the test to IS NULL
                $k .= ' IS NULL';
            }

            if (!is_null($v)) {
                if ($escape === TRUE) {
                    $k = $this->_protect_identifiers($k, FALSE, $escape);

                    $v = ' ' . $this->escape($v);
                }

                if (!$this->_has_operator($k)) {
                    $k .= ' = ';
                }
            } else {
                $k = $this->_protect_identifiers($k, FALSE, $escape);
            }

            $this->ar_where[] = $prefix . $k . $v;

            if ($this->ar_caching === TRUE) {
                $this->ar_cache_where[] = $prefix . $k . $v;
                $this->ar_cache_exists[] = 'where';
            }
        }

        return $this;
    }

// --------------------------------------------------------------------

    /**
     * Where_in
     *
     * Generates a WHERE field IN ('item', 'item') SQL query joined with
     * AND if appropriate
     *
     * @param	string	The field to search
     * @param	array	The values searched on
     * @return	object
     */
    public function where_in($key = NULL, $values = NULL) {
        return $this->_where_in($key, $values);
    }

// --------------------------------------------------------------------

    /**
     * Where_in_or
     *
     * Generates a WHERE field IN ('item', 'item') SQL query joined with
     * OR if appropriate
     *
     * @param	string	The field to search
     * @param	array	The values searched on
     * @return	object
     */
    public function or_where_in($key = NULL, $values = NULL) {
        return $this->_where_in($key, $values, FALSE, 'OR ');
    }

// --------------------------------------------------------------------

    /**
     * Where_not_in
     *
     * Generates a WHERE field NOT IN ('item', 'item') SQL query joined
     * with AND if appropriate
     *
     * @param	string	The field to search
     * @param	array	The values searched on
     * @return	object
     */
    public function where_not_in($key = NULL, $values = NULL) {
        return $this->_where_in($key, $values, TRUE);
    }

// --------------------------------------------------------------------

    /**
     * Where_not_in_or
     *
     * Generates a WHERE field NOT IN ('item', 'item') SQL query joined
     * with OR if appropriate
     *
     * @param	string	The field to search
     * @param	array	The values searched on
     * @return	object
     */
    public function or_where_not_in($key = NULL, $values = NULL) {
        return $this->_where_in($key, $values, TRUE, 'OR ');
    }

// --------------------------------------------------------------------

    /**
     * Where_in
     *
     * Called by where_in, where_in_or, where_not_in, where_not_in_or
     *
     * @param	string	The field to search
     * @param	array	The values searched on
     * @param	boolean	If the statement would be IN or NOT IN
     * @param	string
     * @return	object
     */
    protected function _where_in($key = NULL, $values = NULL, $not = FALSE, $type = 'AND ') {
        if ($key === NULL OR $values === NULL) {
            return;
        }

        if (!is_array($values)) {
            $values = array($values);
        }

        $not = ($not) ? ' NOT' : '';

        foreach ($values as $value) {
            $this->ar_wherein[] = $this->escape($value);
        }

        $prefix = (count($this->ar_where) == 0) ? '' : $type;

        $where_in = $prefix . $this->_protect_identifiers($key) . $not . " IN (" . implode(", ", $this->ar_wherein) . ") ";

        $this->ar_where[] = $where_in;
        if ($this->ar_caching === TRUE) {
            $this->ar_cache_where[] = $where_in;
            $this->ar_cache_exists[] = 'where';
        }

// reset the array for multiple calls
        $this->ar_wherein = array();
        return $this;
    }

// --------------------------------------------------------------------

    /**
     * Like
     *
     * Generates a %LIKE% portion of the query. Separates
     * multiple calls with AND
     *
     * @param	mixed
     * @param	mixed
     * @return	object
     */
    public function like($field, $match = '', $side = 'both') {
        return $this->_like($field, $match, 'AND ', $side);
    }

// --------------------------------------------------------------------

    /**
     * Not Like
     *
     * Generates a NOT LIKE portion of the query. Separates
     * multiple calls with AND
     *
     * @param	mixed
     * @param	mixed
     * @return	object
     */
    public function not_like($field, $match = '', $side = 'both') {
        return $this->_like($field, $match, 'AND ', $side, 'NOT');
    }

// --------------------------------------------------------------------

    /**
     * OR Like
     *
     * Generates a %LIKE% portion of the query. Separates
     * multiple calls with OR
     *
     * @param	mixed
     * @param	mixed
     * @return	object
     */
    public function or_like($field, $match = '', $side = 'both') {
        return $this->_like($field, $match, 'OR ', $side);
    }

// --------------------------------------------------------------------

    /**
     * OR Not Like
     *
     * Generates a NOT LIKE portion of the query. Separates
     * multiple calls with OR
     *
     * @param	mixed
     * @param	mixed
     * @return	object
     */
    public function or_not_like($field, $match = '', $side = 'both') {
        return $this->_like($field, $match, 'OR ', $side, 'NOT');
    }

// --------------------------------------------------------------------

    /**
     * Like
     *
     * Called by like() or orlike()
     *
     * @param	mixed
     * @param	mixed
     * @param	string
     * @return	object
     */
    protected function _like($field, $match = '', $type = 'AND ', $side = 'both', $not = '') {
        if (!is_array($field)) {
            $field = array($field => $match);
        }

        foreach ($field as $k => $v) {
            $k = $this->_protect_identifiers($k);

            $prefix = (count($this->ar_like) == 0) ? '' : $type;

            $v = $this->escape_like_str($v);

            if ($side == 'none') {
                $like_statement = $prefix . " $k $not LIKE '{$v}'";
            } elseif ($side == 'before') {
                $like_statement = $prefix . " $k $not LIKE '%{$v}'";
            } elseif ($side == 'after') {
                $like_statement = $prefix . " $k $not LIKE '{$v}%'";
            } else {
                $like_statement = $prefix . " $k $not LIKE '%{$v}%'";
            }

// some platforms require an escape sequence definition for LIKE wildcards
            if ($this->_like_escape_str != '') {
                $like_statement = $like_statement . sprintf($this->_like_escape_str, $this->_like_escape_chr);
            }

            $this->ar_like[] = $like_statement;
            if ($this->ar_caching === TRUE) {
                $this->ar_cache_like[] = $like_statement;
                $this->ar_cache_exists[] = 'like';
            }
        }
        return $this;
    }

// --------------------------------------------------------------------

    /**
     * GROUP BY
     *
     * @param	string
     * @return	object
     */
    public function group_by($by) {
        if (is_string($by)) {
            $by = explode(',', $by);
        }

        foreach ($by as $val) {
            $val = trim($val);

            if ($val != '') {
                $this->ar_groupby[] = $this->_protect_identifiers($val);

                if ($this->ar_caching === TRUE) {
                    $this->ar_cache_groupby[] = $this->_protect_identifiers($val);
                    $this->ar_cache_exists[] = 'groupby';
                }
            }
        }
        return $this;
    }

// --------------------------------------------------------------------

    /**
     * Sets the HAVING value
     *
     * Separates multiple calls with AND
     *
     * @param	string
     * @param	string
     * @return	object
     */
    public function having($key, $value = '', $escape = TRUE) {
        return $this->_having($key, $value, 'AND ', $escape);
    }

// --------------------------------------------------------------------

    /**
     * Sets the OR HAVING value
     *
     * Separates multiple calls with OR
     *
     * @param	string
     * @param	string
     * @return	object
     */
    public function or_having($key, $value = '', $escape = TRUE) {
        return $this->_having($key, $value, 'OR ', $escape);
    }

// --------------------------------------------------------------------

    /**
     * Sets the HAVING values
     *
     * Called by having() or or_having()
     *
     * @param	string
     * @param	string
     * @return	object
     */
    protected function _having($key, $value = '', $type = 'AND ', $escape = TRUE) {
        if (!is_array($key)) {
            $key = array($key => $value);
        }

        foreach ($key as $k => $v) {
            $prefix = (count($this->ar_having) == 0) ? '' : $type;

            if ($escape === TRUE) {
                $k = $this->_protect_identifiers($k);
            }

            if (!$this->_has_operator($k)) {
                $k .= ' = ';
            }

            if ($v != '') {
                $v = ' ' . $this->escape($v);
            }

            $this->ar_having[] = $prefix . $k . $v;
            if ($this->ar_caching === TRUE) {
                $this->ar_cache_having[] = $prefix . $k . $v;
                $this->ar_cache_exists[] = 'having';
            }
        }

        return $this;
    }

// --------------------------------------------------------------------

    /**
     * Sets the ORDER BY value
     *
     * @param	string
     * @param	string	direction: asc or desc
     * @return	object
     */
    public function order_by($orderby, $direction = '') {
        if (strtolower($direction) == 'random') {
            $orderby = ''; // Random results want or don't need a field name
            $direction = $this->_random_keyword;
        } elseif (trim($direction) != '') {
            $direction = (in_array(strtoupper(trim($direction)), array('ASC', 'DESC'), TRUE)) ? ' ' . $direction : ' ASC';
        }


        if (strpos($orderby, ',') !== FALSE) {
            $temp = array();
            foreach (explode(',', $orderby) as $part) {
                $part = trim($part);
                if (!in_array($part, $this->ar_aliased_tables)) {
                    $part = $this->_protect_identifiers(trim($part));
                }

                $temp[] = $part;
            }

            $orderby = implode(', ', $temp);
        } else if ($direction != $this->_random_keyword) {
            $orderby = $this->_protect_identifiers($orderby);
        }

        $orderby_statement = $orderby . $direction;

        $this->ar_orderby[] = $orderby_statement;
        if ($this->ar_caching === TRUE) {
            $this->ar_cache_orderby[] = $orderby_statement;
            $this->ar_cache_exists[] = 'orderby';
        }

        return $this;
    }

// --------------------------------------------------------------------

    /**
     * Sets the LIMIT value
     *
     * @param	integer	the limit value
     * @param	integer	the offset value
     * @return	object
     */
    public function limit($value, $offset = '') {
        $this->ar_limit = (int) $value;

        if ($offset != '') {
            $this->ar_offset = (int) $offset;
        }

        return $this;
    }

// --------------------------------------------------------------------

    /**
     * Sets the OFFSET value
     *
     * @param	integer	the offset value
     * @return	object
     */
    public function offset($offset) {
        $this->ar_offset = $offset;
        return $this;
    }

// --------------------------------------------------------------------

    /**
     * The "set" function.  Allows key/value pairs to be set for inserting or updating
     *
     * @param	mixed
     * @param	string
     * @param	boolean
     * @return	object
     */
    public function set($key, $value = '', $escape = TRUE) {
        $key = $this->_object_to_array($key);

        if (!is_array($key)) {
            $key = array($key => $value);
        }

        foreach ($key as $k => $v) {
            if ($escape === FALSE) {
                $this->ar_set[$this->_protect_identifiers($k)] = $v;
            } else {
                $this->ar_set[$this->_protect_identifiers($k, FALSE, TRUE)] = $this->escape($v);
            }
        }

        return $this;
    }

// --------------------------------------------------------------------

    /**
     * Get
     *
     * Compiles the select statement based on the other functions called
     * and runs the query
     *
     * @param	string	the table
     * @param	string	the limit clause
     * @param	string	the offset clause
     * @return	object
     */
    public function get($table = '', $limit = null, $offset = null) {
        if ($table != '') {
            $this->_track_aliases($table);
            $this->from($table);
        }

        if (!is_null($limit)) {
            $this->limit($limit, $offset);
        }

        $sql = $this->_compile_select();

        $result = $this->query($sql);
        $this->_reset_select();
        return $result;
    }

    /**
     * "Count All Results" query
     *
     * Generates a platform-specific query string that counts all records
     * returned by an Active Record query.
     *
     * @param	string
     * @return	string
     */
    public function count_all_results($table = '') {
        if ($table != '') {
            $this->_track_aliases($table);
            $this->from($table);
        }

        $sql = $this->_compile_select($this->_count_string . $this->_protect_identifiers('numrows'));

        $query = $this->query($sql);
        $this->_reset_select();

        if ($query->num_rows() == 0) {
            return 0;
        }

        $row = $query->row();
        return (int) $row->numrows;
    }

// --------------------------------------------------------------------

    /**
     * Get_Where
     *
     * Allows the where clause, limit and offset to be added directly
     *
     * @param	string	the where clause
     * @param	string	the limit clause
     * @param	string	the offset clause
     * @return	object
     */
    public function get_where($table = '', $where = null, $limit = null, $offset = null) {
        if ($table != '') {
            $this->from($table);
        }

        if (!is_null($where)) {
            $this->where($where);
        }

        if (!is_null($limit)) {
            $this->limit($limit, $offset);
        }

        $sql = $this->_compile_select();

        $result = $this->query($sql);
        $this->_reset_select();
        return $result;
    }

// --------------------------------------------------------------------

    /**
     * Insert_Batch
     *
     * Compiles batch insert strings and runs the queries
     *
     * @param	string	the table to retrieve the results from
     * @param	array	an associative array of insert values
     * @return	object
     */
    public function insert_batch($table = '', $set = NULL) {
        if (!is_null($set)) {
            $this->set_insert_batch($set);
        }

        if (count($this->ar_set) == 0) {
            if ($this->db_debug) {
//No valid data array.  Folds in cases where keys and values did not match up
                return $this->display_error('db_must_use_set');
            }
            return FALSE;
        }

        if ($table == '') {
            if (!isset($this->ar_from[0])) {
                if ($this->db_debug) {
                    return $this->display_error('db_must_set_table');
                }
                return FALSE;
            }

            $table = $this->ar_from[0];
        }

// Batch this baby
        for ($i = 0, $total = count($this->ar_set); $i < $total; $i = $i + 100) {

            $sql = $this->_insert_batch($this->_protect_identifiers($table, TRUE, NULL, FALSE), $this->ar_keys, array_slice($this->ar_set, $i, 100));

//echo $sql;

            $this->query($sql);
        }

        $this->_reset_write();


        return TRUE;
    }

// --------------------------------------------------------------------

    /**
     * The "set_insert_batch" function.  Allows key/value pairs to be set for batch inserts
     *
     * @param	mixed
     * @param	string
     * @param	boolean
     * @return	object
     */
    public function set_insert_batch($key, $value = '', $escape = TRUE) {
        $key = $this->_object_to_array_batch($key);

        if (!is_array($key)) {
            $key = array($key => $value);
        }

        $keys = array_keys(current($key));
        sort($keys);

        foreach ($key as $row) {
            if (count(array_diff($keys, array_keys($row))) > 0 OR count(array_diff(array_keys($row), $keys)) > 0) {
// batch function above returns an error on an empty array
                $this->ar_set[] = array();
                return;
            }

            ksort($row); // puts $row in the same order as our keys

            if ($escape === FALSE) {
                $this->ar_set[] = '(' . implode(',', $row) . ')';
            } else {
                $clean = array();

                foreach ($row as $value) {
                    $clean[] = $this->escape($value);
                }

                $this->ar_set[] = '(' . implode(',', $clean) . ')';
            }
        }

        foreach ($keys as $k) {
            $this->ar_keys[] = $this->_protect_identifiers($k);
        }

        return $this;
    }

// --------------------------------------------------------------------

    /**
     * Insert
     *
     * Compiles an insert string and runs the query
     *
     * @param	string	the table to insert data into
     * @param	array	an associative array of insert values
     * @return	object
     */
    function insert($table = '', $set = NULL) {
        if (!is_null($set)) {
            $this->set($set);
        }

        if (count($this->ar_set) == 0) {
            if ($this->db_debug) {
                return $this->display_error('db_must_use_set');
            }
            return FALSE;
        }

        if ($table == '') {
            if (!isset($this->ar_from[0])) {
                if ($this->db_debug) {
                    return $this->display_error('db_must_set_table');
                }
                return FALSE;
            }

            $table = $this->ar_from[0];
        }

        $sql = $this->_insert($this->_protect_identifiers($table, TRUE, NULL, FALSE), array_keys($this->ar_set), array_values($this->ar_set));

        $this->_reset_write();
        return $this->query($sql);
    }

// --------------------------------------------------------------------

    /**
     * Replace
     *
     * Compiles an replace into string and runs the query
     *
     * @param	string	the table to replace data into
     * @param	array	an associative array of insert values
     * @return	object
     */
    public function replace($table = '', $set = NULL) {
        if (!is_null($set)) {
            $this->set($set);
        }

        if (count($this->ar_set) == 0) {
            if ($this->db_debug) {
                return $this->display_error('db_must_use_set');
            }
            return FALSE;
        }

        if ($table == '') {
            if (!isset($this->ar_from[0])) {
                if ($this->db_debug) {
                    return $this->display_error('db_must_set_table');
                }
                return FALSE;
            }

            $table = $this->ar_from[0];
        }

        $sql = $this->_replace($this->_protect_identifiers($table, TRUE, NULL, FALSE), array_keys($this->ar_set), array_values($this->ar_set));

        $this->_reset_write();
        return $this->query($sql);
    }

// --------------------------------------------------------------------

    /**
     * Update
     *
     * Compiles an update string and runs the query
     *
     * @param	string	the table to retrieve the results from
     * @param	array	an associative array of update values
     * @param	mixed	the where clause
     * @return	object
     */
    public function update($table = '', $set = NULL, $where = NULL, $limit = NULL) {
// Combine any cached components with the current statements
        $this->_merge_cache();

        if (!is_null($set)) {
            $this->set($set);
        }

        if (count($this->ar_set) == 0) {
            if ($this->db_debug) {
                return $this->display_error('db_must_use_set');
            }
            return FALSE;
        }

        if ($table == '') {
            if (!isset($this->ar_from[0])) {
                if ($this->db_debug) {
                    return $this->display_error('db_must_set_table');
                }
                return FALSE;
            }

            $table = $this->ar_from[0];
        }

        if ($where != NULL) {
            $this->where($where);
        }

        if ($limit != NULL) {
            $this->limit($limit);
        }

        $sql = $this->_update($this->_protect_identifiers($table, TRUE, NULL, FALSE), $this->ar_set, $this->ar_where, $this->ar_orderby, $this->ar_limit);

        $this->_reset_write();
        return $this->query($sql);
    }

// --------------------------------------------------------------------

    /**
     * Update_Batch
     *
     * Compiles an update string and runs the query
     *
     * @param	string	the table to retrieve the results from
     * @param	array	an associative array of update values
     * @param	string	the where key
     * @return	object
     */
    public function update_batch($table = '', $set = NULL, $index = NULL) {
// Combine any cached components with the current statements
        $this->_merge_cache();

        if (is_null($index)) {
            if ($this->db_debug) {
                return $this->display_error('db_must_use_index');
            }

            return FALSE;
        }

        if (!is_null($set)) {
            $this->set_update_batch($set, $index);
        }

        if (count($this->ar_set) == 0) {
            if ($this->db_debug) {
                return $this->display_error('db_must_use_set');
            }

            return FALSE;
        }

        if ($table == '') {
            if (!isset($this->ar_from[0])) {
                if ($this->db_debug) {
                    return $this->display_error('db_must_set_table');
                }
                return FALSE;
            }

            $table = $this->ar_from[0];
        }

// Batch this baby
        for ($i = 0, $total = count($this->ar_set); $i < $total; $i = $i + 100) {
            $sql = $this->_update_batch($this->_protect_identifiers($table, TRUE, NULL, FALSE), array_slice($this->ar_set, $i, 100), $this->_protect_identifiers($index), $this->ar_where);

            $this->query($sql);
        }

        $this->_reset_write();
    }

// --------------------------------------------------------------------

    /**
     * The "set_update_batch" function.  Allows key/value pairs to be set for batch updating
     *
     * @param	array
     * @param	string
     * @param	boolean
     * @return	object
     */
    public function set_update_batch($key, $index = '', $escape = TRUE) {
        $key = $this->_object_to_array_batch($key);

        if (!is_array($key)) {
// @todo error
        }

        foreach ($key as $k => $v) {
            $index_set = FALSE;
            $clean = array();

            foreach ($v as $k2 => $v2) {
                if ($k2 == $index) {
                    $index_set = TRUE;
                } else {
                    $not[] = $k . '-' . $v;
                }

                if ($escape === FALSE) {
                    $clean[$this->_protect_identifiers($k2)] = $v2;
                } else {
                    $clean[$this->_protect_identifiers($k2)] = $this->escape($v2);
                }
            }

            if ($index_set == FALSE) {
                return $this->display_error('db_batch_missing_index');
            }

            $this->ar_set[] = $clean;
        }

        return $this;
    }

// --------------------------------------------------------------------

    /**
     * Empty Table
     *
     * Compiles a delete string and runs "DELETE FROM table"
     *
     * @param	string	the table to empty
     * @return	object
     */
    public function empty_table($table = '') {
        if ($table == '') {
            if (!isset($this->ar_from[0])) {
                if ($this->db_debug) {
                    return $this->display_error('db_must_set_table');
                }
                return FALSE;
            }

            $table = $this->ar_from[0];
        } else {
            $table = $this->_protect_identifiers($table, TRUE, NULL, FALSE);
        }

        $sql = $this->_delete($table);

        $this->_reset_write();

        return $this->query($sql);
    }

// --------------------------------------------------------------------

    /**
     * Truncate
     *
     * Compiles a truncate string and runs the query
     * If the database does not support the truncate() command
     * This function maps to "DELETE FROM table"
     *
     * @param	string	the table to truncate
     * @return	object
     */
    public function truncate($table = '') {
        if ($table == '') {
            if (!isset($this->ar_from[0])) {
                if ($this->db_debug) {
                    return $this->display_error('db_must_set_table');
                }
                return FALSE;
            }

            $table = $this->ar_from[0];
        } else {
            $table = $this->_protect_identifiers($table, TRUE, NULL, FALSE);
        }

        $sql = $this->_truncate($table);

        $this->_reset_write();

        return $this->query($sql);
    }

// --------------------------------------------------------------------

    /**
     * Delete
     *
     * Compiles a delete string and runs the query
     *
     * @param	mixed	the table(s) to delete from. String or array
     * @param	mixed	the where clause
     * @param	mixed	the limit clause
     * @param	boolean
     * @return	object
     */
    public function delete($table = '', $where = '', $limit = NULL, $reset_data = TRUE) {
// Combine any cached components with the current statements
        $this->_merge_cache();

        if ($table == '') {
            if (!isset($this->ar_from[0])) {
                if ($this->db_debug) {
                    return $this->display_error('db_must_set_table');
                }
                return FALSE;
            }

            $table = $this->ar_from[0];
        } elseif (is_array($table)) {
            foreach ($table as $single_table) {
                $this->delete($single_table, $where, $limit, FALSE);
            }

            $this->_reset_write();
            return;
        } else {
            $table = $this->_protect_identifiers($table, TRUE, NULL, FALSE);
        }

        if ($where != '') {
            $this->where($where);
        }

        if ($limit != NULL) {
            $this->limit($limit);
        }

        if (count($this->ar_where) == 0 && count($this->ar_wherein) == 0 && count($this->ar_like) == 0) {
            if ($this->db_debug) {
                return $this->display_error('db_del_must_use_where');
            }

            return FALSE;
        }

        $sql = $this->_delete($table, $this->ar_where, $this->ar_like, $this->ar_limit);

        if ($reset_data) {
            $this->_reset_write();
        }

        return $this->query($sql);
    }

// --------------------------------------------------------------------

    /**
     * DB Prefix
     *
     * Prepends a database prefix if one exists in configuration
     *
     * @param	string	the table
     * @return	string
     */
    public function dbprefix($table = '') {
        if ($table == '') {
            $this->display_error('db_table_name_required');
        }

        return $this->dbprefix . $table;
    }

// --------------------------------------------------------------------

    /**
     * Set DB Prefix
     *
     * Set's the DB Prefix to something new without needing to reconnect
     *
     * @param	string	the prefix
     * @return	string
     */
    public function set_dbprefix($prefix = '') {
        return $this->dbprefix = $prefix;
    }

// --------------------------------------------------------------------

    /**
     * Track Aliases
     *
     * Used to track SQL statements written with aliased tables.
     *
     * @param	string	The table to inspect
     * @return	string
     */
    protected function _track_aliases($table) {
        if (is_array($table)) {
            foreach ($table as $t) {
                $this->_track_aliases($t);
            }
            return;
        }

// Does the string contain a comma?  If so, we need to separate
// the string into discreet statements
        if (strpos($table, ',') !== FALSE) {
            return $this->_track_aliases(explode(',', $table));
        }

// if a table alias is used we can recognize it by a space
        if (strpos($table, " ") !== FALSE) {
// if the alias is written with the AS keyword, remove it
            $table = preg_replace('/\s+AS\s+/i', ' ', $table);

// Grab the alias
            $table = trim(strrchr($table, " "));

// Store the alias, if it doesn't already exist
            if (!in_array($table, $this->ar_aliased_tables)) {
                $this->ar_aliased_tables[] = $table;
            }
        }
    }

// --------------------------------------------------------------------

    /**
     * Compile the SELECT statement
     *
     * Generates a query string based on which functions were used.
     * Should not be called directly.  The get() function calls it.
     *
     * @return	string
     */
    protected function _compile_select($select_override = FALSE) {
// Combine any cached components with the current statements
        $this->_merge_cache();

// ----------------------------------------------------------------
// Write the "select" portion of the query

        if ($select_override !== FALSE) {
            $sql = $select_override;
        } else {
            $sql = (!$this->ar_distinct) ? 'SELECT ' : 'SELECT DISTINCT ';

            if (count($this->ar_select) == 0) {
                $sql .= '*';
            } else {
// Cycle through the "select" portion of the query and prep each column name.
// The reason we protect identifiers here rather then in the select() function
// is because until the user calls the from() function we don't know if there are aliases
                foreach ($this->ar_select as $key => $val) {
                    $no_escape = isset($this->ar_no_escape[$key]) ? $this->ar_no_escape[$key] : NULL;
                    $this->ar_select[$key] = $this->_protect_identifiers($val, FALSE, $no_escape);
                }

                $sql .= implode(', ', $this->ar_select);
            }
        }

// ----------------------------------------------------------------
// Write the "FROM" portion of the query

        if (count($this->ar_from) > 0) {
            $sql .= "\nFROM ";

            $sql .= $this->_from_tables($this->ar_from);
        }

// ----------------------------------------------------------------
// Write the "JOIN" portion of the query

        if (count($this->ar_join) > 0) {
            $sql .= "\n";

            $sql .= implode("\n", $this->ar_join);
        }

// ----------------------------------------------------------------
// Write the "WHERE" portion of the query

        if (count($this->ar_where) > 0 OR count($this->ar_like) > 0) {
            $sql .= "\nWHERE ";
        }

        $sql .= implode("\n", $this->ar_where);

// ----------------------------------------------------------------
// Write the "LIKE" portion of the query

        if (count($this->ar_like) > 0) {
            if (count($this->ar_where) > 0) {
                $sql .= "\nAND ";
            }

            $sql .= implode("\n", $this->ar_like);
        }

// ----------------------------------------------------------------
// Write the "GROUP BY" portion of the query

        if (count($this->ar_groupby) > 0) {
            $sql .= "\nGROUP BY ";

            $sql .= implode(', ', $this->ar_groupby);
        }

// ----------------------------------------------------------------
// Write the "HAVING" portion of the query

        if (count($this->ar_having) > 0) {
            $sql .= "\nHAVING ";
            $sql .= implode("\n", $this->ar_having);
        }

// ----------------------------------------------------------------
// Write the "ORDER BY" portion of the query

        if (count($this->ar_orderby) > 0) {
            $sql .= "\nORDER BY ";
            $sql .= implode(', ', $this->ar_orderby);

            if ($this->ar_order !== FALSE) {
                $sql .= ($this->ar_order == 'desc') ? ' DESC' : ' ASC';
            }
        }

// ----------------------------------------------------------------
// Write the "LIMIT" portion of the query

        if (is_numeric($this->ar_limit)) {
            $sql .= "\n";
            $sql = $this->_limit($sql, $this->ar_limit, $this->ar_offset);
        }

        return $sql;
    }

// --------------------------------------------------------------------

    /**
     * Object to Array
     *
     * Takes an object as input and converts the class variables to array key/vals
     *
     * @param	object
     * @return	array
     */
    public function _object_to_array($object) {
        if (!is_object($object)) {
            return $object;
        }

        $array = array();
        foreach (get_object_vars($object) as $key => $val) {
// There are some built in keys we need to ignore for this conversion
            if (!is_object($val) && !is_array($val) && $key != '_parent_name') {
                $array[$key] = $val;
            }
        }

        return $array;
    }

// --------------------------------------------------------------------

    /**
     * Object to Array
     *
     * Takes an object as input and converts the class variables to array key/vals
     *
     * @param	object
     * @return	array
     */
    public function _object_to_array_batch($object) {
        if (!is_object($object)) {
            return $object;
        }

        $array = array();
        $out = get_object_vars($object);
        $fields = array_keys($out);

        foreach ($fields as $val) {
// There are some built in keys we need to ignore for this conversion
            if ($val != '_parent_name') {

                $i = 0;
                foreach ($out[$val] as $data) {
                    $array[$i][$val] = $data;
                    $i++;
                }
            }
        }

        return $array;
    }

// --------------------------------------------------------------------

    /**
     * Start Cache
     *
     * Starts AR caching
     *
     * @return	void
     */
    public function start_cache() {
        $this->ar_caching = TRUE;
    }

// --------------------------------------------------------------------

    /**
     * Stop Cache
     *
     * Stops AR caching
     *
     * @return	void
     */
    public function stop_cache() {
        $this->ar_caching = FALSE;
    }

// --------------------------------------------------------------------

    /**
     * Flush Cache
     *
     * Empties the AR cache
     *
     * @access	public
     * @return	void
     */
    public function flush_cache() {
        $this->_reset_run(array(
            'ar_cache_select' => array(),
            'ar_cache_from' => array(),
            'ar_cache_join' => array(),
            'ar_cache_where' => array(),
            'ar_cache_like' => array(),
            'ar_cache_groupby' => array(),
            'ar_cache_having' => array(),
            'ar_cache_orderby' => array(),
            'ar_cache_set' => array(),
            'ar_cache_exists' => array(),
            'ar_cache_no_escape' => array()
        ));
    }

// --------------------------------------------------------------------

    /**
     * Merge Cache
     *
     * When called, this function merges any cached AR arrays with
     * locally called ones.
     *
     * @return	void
     */
    protected function _merge_cache() {
        if (count($this->ar_cache_exists) == 0) {
            return;
        }

        foreach ($this->ar_cache_exists as $val) {
            $ar_variable = 'ar_' . $val;
            $ar_cache_var = 'ar_cache_' . $val;

            if (count($this->$ar_cache_var) == 0) {
                continue;
            }

            $this->$ar_variable = array_unique(array_merge($this->$ar_cache_var, $this->$ar_variable));
        }

// If we are "protecting identifiers" we need to examine the "from"
// portion of the query to determine if there are any aliases
        if ($this->_protect_identifiers === TRUE AND count($this->ar_cache_from) > 0) {
            $this->_track_aliases($this->ar_from);
        }

        $this->ar_no_escape = $this->ar_cache_no_escape;
    }

// --------------------------------------------------------------------

    /**
     * Resets the active record values.  Called by the get() function
     *
     * @param	array	An array of fields to reset
     * @return	void
     */
    protected function _reset_run($ar_reset_items) {
        foreach ($ar_reset_items as $item => $default_value) {
            if (!in_array($item, $this->ar_store_array)) {
                $this->$item = $default_value;
            }
        }
    }

// --------------------------------------------------------------------

    /**
     * Resets the active record values.  Called by the get() function
     *
     * @return	void
     */
    protected function _reset_select() {
        $ar_reset_items = array(
            'ar_select' => array(),
            'ar_from' => array(),
            'ar_join' => array(),
            'ar_where' => array(),
            'ar_like' => array(),
            'ar_groupby' => array(),
            'ar_having' => array(),
            'ar_orderby' => array(),
            'ar_wherein' => array(),
            'ar_aliased_tables' => array(),
            'ar_no_escape' => array(),
            'ar_distinct' => FALSE,
            'ar_limit' => FALSE,
            'ar_offset' => FALSE,
            'ar_order' => FALSE,
        );

        $this->_reset_run($ar_reset_items);
    }

// --------------------------------------------------------------------

    /**
     * Resets the active record "write" values.
     *
     * Called by the insert() update() insert_batch() update_batch() and delete() functions
     *
     * @return	void
     */
    protected function _reset_write() {
        $ar_reset_items = array(
            'ar_set' => array(),
            'ar_from' => array(),
            'ar_where' => array(),
            'ar_like' => array(),
            'ar_orderby' => array(),
            'ar_keys' => array(),
            'ar_limit' => FALSE,
            'ar_order' => FALSE
        );

        $this->_reset_run($ar_reset_items);
    }

}

/* End of file DB_active_rec.php */
/* Location: ./system/database/DB_active_rec.php */

function log_message($level, $msg) {/* just suppress logging */
}

//####################modules/WoniuHelper.php####################{


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
 * @createdtime       2013-05-13 11:41:18
 */
function trigger404($msg = '<h1>Not Found</h1>') {
    global $system;
    header('HTTP/1.1 404 NotFound');
    if (!empty($system['error_page_404']) && file_exists($system['error_page_404'])) {
        include $system['error_page_404'];
    } else {
        echo $msg;
    }
    exit();
}

function stripslashes_all() {
    if (!get_magic_quotes_gpc()) {
        return;
    }
    $strip_list = array('_GET', '_POST', '_COOKIE');
    foreach ($strip_list as $val) {
        global $$val;
        $$val = stripslashes2($$val);
    }
}

#过滤魔法转义，参数可以是字符串或者数组，支持嵌套数组

function stripslashes2($var) {
    if (!get_magic_quotes_gpc()) {
        return $var;
    }
    if (is_array($var)) {
        foreach ($var as $key => $val) {
            if (is_array($val)) {
                $var[$key] = stripslashes2($val);
            } else {
                $var[$key] = stripslashes($val);
            }
        }
    } elseif (is_string($var)) {
        $var = stripslashes($var);
    }
    return $var;
}

function is_php($version = '5.0.0') {
    static $_is_php;
    $version = (string) $version;

    if (!isset($_is_php[$version])) {
        $_is_php[$version] = (version_compare(PHP_VERSION, $version) < 0) ? FALSE : TRUE;
    }

    return $_is_php[$version];
}

/* End of file Helper.php */
 
//####################modules/WoniuInput.class.php####################{


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

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
 * @createdtime       2013-05-13 11:41:18
 */
class WoniuInput {

    public static function get_post($key = null, $default = null) {
        $get=self::gpcs('_GET', $key, $default);
        return $get===null?self::gpcs('_POST', $key, $default):$get;
    }

    public static function get($key = null, $default = null) {
        return self::gpcs('_GET', $key, $default);
    }

    public static function post($key = null, $default = null) {
        return self::gpcs('_POST', $key, $default);
    }

    public static function cookie($key = null, $default = null) {
        return self::gpcs('_COOKIE', $key, $default);
    }

    public static function session($key = null, $default = null) {
        return self::gpcs('_SESSION', $key, $default);
    }

    public static function server($key = null, $default = null) {
        $key = strtoupper($key);
        return self::gpcs('_SERVER', $key, $default);
    }

    private static function gpcs($range, $key, $default) {
        global $$range;
        if ($key === null) {
            return $$range;
        } else {
            $range = $$range;
            return isset($range[$key]) ? $range[$key] : ( $default !== null ? $default : null);
        }
    }

}

/* End of file WoniuInput.php */

WoniuRouter::loadClass();