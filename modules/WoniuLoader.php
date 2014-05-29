<?php

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
 * @createdtime            {createdtime}
 * @property CI_DB_active_record $db
 * @property phpFastCache        $cache
 * @property WoniuInput          $input
 * @property WoniuRule           $rule
 */
class WoniuLoader {

    public $model, $lib, $router, $db, $input, $view_vars = array(), $cache, $rule;
    private static $helper_files = array(), $files = array();
    private static $instance, $config = array();
    public static $system;

    public function __construct() {
        $system = WoniuLoader::$system;
        date_default_timezone_set($system['default_timezone']);
        $this->registerErrorHandle();
        $this->router = WoniuInput::$router;
        $this->input = new WoniuInput();
        $this->model = new WoniuModelLoader();
        $this->lib = new WoniuLibLoader();
        if(class_exists('WoniuRule', FALSE)){
            $this->rule = new WoniuRule();
        }
        if (class_exists('phpFastCache', false)) {
            phpFastCache::setup($system['cache_config']);
            $this->cache = phpFastCache($system['cache_config']['storage']);
        }
        if ($system['autoload_db']) {
            $this->database();
        }
        stripslashes_all();
    }

    public function registerErrorHandle() {
        $system = WoniuLoader::$system;
        /**
         * 提醒：
         * error_reporting   控制报告错误类型
         * display_errors    控制是否在页面显示报告了的类型的错误的错误信息
         * 言外之意就是即使报告了所有错误，但是却可以不显示错误信息。
         * 另外：
         * 如果用 set_error_handler() 设定了自定义的错误处理函数，
         * 即使PHP表达式之前放置在一个@ ，但是自定义的错误处理函仍然会被调用，
         * 当出错语句前有 @ 时, error_reporting()将返回 0。
         * 错误处理函数可以调用 error_reporting()处理 @ 的情况。
         */
        //只有设置了报告所有错误，handle才能捕捉所有错误
        error_reporting(E_ALL);
        //是否显示错误
        if ($system['debug']) {
            ini_set('display_errors', true);
        } else {
            ini_set('display_errors', FALSE);
        }
        if ($system['error_manage'] || $system['log_error']) {
            set_exception_handler('woniu_exception_handler');
            set_error_handler('woniu_error_handler');
            register_shutdown_function('woniu_fatal_handler');
        }
    }

    public static function config($config_group, $key = null) {
        if (!is_null($key)) {
            return isset(self::$config[$config_group][$key]) ? self::$config[$config_group][$key] : null;
        } else {
            return isset(self::$config[$config_group]) ? self::$config[$config_group] : null;
        }
    }

    public function database($config = NULL, $is_return = false, $force_new_conn = false) {
        $woniu_db = self::$system['db'];
        $db_cfg_key = $woniu_db['active_group'];
        if (is_string($config) && !empty($config)) {
            //传递配置key
            $db_cfg = $woniu_db[$config];
        } elseif (is_array($config)) {
            //传递配置
            $db_cfg = $config;
        } else {
            //没有传递配置，使用默认配置
            $db_cfg = $woniu_db[$db_cfg_key];
        }
        if ($is_return) {
            return WoniuDB::getInstance($db_cfg, $force_new_conn);
        } else {
            if ($force_new_conn || !is_object($this->db)) {
                return $this->db = WoniuDB::getInstance($db_cfg, $force_new_conn);
            }
            return $this->db;
        }
    }

    public function setConfig($key, $val) {
        self::$config[$key] = $val;
    }

    public static function helper($file_name) {
        $system = WoniuLoader::$system;
        $helper_folders = $system['helper_folder'];
        if (!is_array($helper_folders)) {
            $helper_folders = array($helper_folders);
        }
        $count = count($helper_folders);
        foreach ($helper_folders as $k => $helper_folder) {
            $filename = $helper_folder . DIRECTORY_SEPARATOR . $file_name . $system['helper_file_subfix'];
            $filename = convertPath($filename);
            if (in_array($filename, self::$helper_files)) {
                return;
            }
            if (file_exists($filename)) {
                self::$helper_files[] = $filename;
                //包含文件，并把文件里面的变量放入self::config
                $before_vars = array_keys(get_defined_vars());
                $before_vars[] = 'before_vars';
                include($filename);
                $vars = get_defined_vars();
                $all_vars = array_keys($vars);
                foreach ($all_vars as $key) {
                    if (!in_array($key, $before_vars) && isset($vars[$key])) {
                        self::$config[$key] = $vars[$key];
                    }
                }
                break;
            } else {
                if (($count - 1) == $k) {
                    trigger404($filename . ' not found.');
                }
            }
        }
    }

    public static function lib($file_name, $alias_name = null) {
        $system = WoniuLoader::$system;
        $classname = $file_name;
        if (strstr($file_name, '/') !== false || strstr($file_name, "\\") !== false) {
            $classname = basename($file_name);
        }
        if (!$alias_name) {
            $alias_name = $classname;
        }
        $library_folders = $system['library_folder'];
        if (!is_array($library_folders)) {
            $library_folders = array($library_folders);
        }
        $count = count($library_folders);
        foreach ($library_folders as $key => $library_folder) {
            $filepath = $library_folder . DIRECTORY_SEPARATOR . $file_name . $system['library_file_subfix'];
            if (in_array($alias_name, array_keys(WoniuLibLoader::$lib_files))) {
                return WoniuLibLoader::$lib_files[$alias_name];
            } else {
                foreach (WoniuLibLoader::$lib_files as $aname => $obj) {
                    if (strtolower(get_class($obj)) === strtolower($classname)) {
                        return WoniuLibLoader::$lib_files[$alias_name] = WoniuLibLoader::$lib_files[$aname];
                    }
                }
            }
            if (file_exists($filepath)) {
                self::includeOnce($filepath);
                if (class_exists($classname, FALSE)) {
                    return WoniuLibLoader::$lib_files[$alias_name] = new $classname();
                } else {
                    if ($key == $count - 1) {
                        trigger404('Library Class:' . $classname . ' not found.');
                    }
                }
            } else {
                if ($key == $count - 1) {
                    trigger404($filepath . ' not found.');
                }
            }
        }
    }

    public static function model($file_name, $alias_name = null) {
        $system = WoniuLoader::$system;
        $classname = $file_name;
        if (strstr($file_name, '/') !== false || strstr($file_name, "\\") !== false) {
            $classname = basename($file_name);
        }
        if (!$alias_name) {
            $alias_name = $classname;
        }
        $model_folders = $system['model_folder'];
        if (!is_array($model_folders)) {
            $model_folders = array($model_folders);
        }
        $count = count($model_folders);
        foreach ($model_folders as $key => $model_folder) {
            //$filepath = $system['model_folder'] . DIRECTORY_SEPARATOR . $file_name . $system['model_file_subfix'];
            $filepath = $model_folder . DIRECTORY_SEPARATOR . $file_name . $system['model_file_subfix'];
            if (in_array($alias_name, array_keys(WoniuModelLoader::$model_files))) {
                return WoniuModelLoader::$model_files[$alias_name];
            } else {
                foreach (WoniuModelLoader::$model_files as &$obj) {
                    if (strtolower(get_class($obj)) == strtolower($classname)) {
                        return WoniuModelLoader::$model_files[$alias_name] = $obj;
                    }
                }
            }
            if (file_exists($filepath)) {
                self::includeOnce($filepath);
                if (class_exists($classname, FALSE)) {
                    return WoniuModelLoader::$model_files[$alias_name] = new $classname();
                } else {
                    if ($key == $count - 1) {
                        trigger404('Model Class:' . $classname . ' not found.');
                    }
                }
            } else {
                if ($key == $count - 1) {
                    trigger404($filepath . ' not  found.');
                }
            }
        }
    }

    public function view($view_name, $data = null, $return = false) {
        if (is_array($data)) {
            $this->view_vars = array_merge($this->view_vars, $data);
            extract($this->view_vars);
        } elseif (is_array($this->view_vars) && !empty($this->view_vars)) {
            extract($this->view_vars);
        }
        $system = WoniuLoader::$system;
        $view_folders = $system['view_folder'];
        if (!is_array($view_folders)) {
            $view_folders = array($view_folders);
        }
        $count = count($view_folders);
        $i = 0;
        if (stripos($view_name, ':') !== false) {
            //指定了键
            $info = explode(':', $view_name);
            $path_key = current($info);
            $view_name = next($info);
            if (!isset($system['view_folder'][$path_key])) {
                trigger404('error key[' . $path_key . '] of $system["view_folder"]');
            } else {
                $dir = $system['view_folder'][$path_key];
                $view_path = $dir . DIRECTORY_SEPARATOR . $view_name . $system['view_file_subfix'];
                if (file_exists($view_path)) {
                    if ($return) {
                        @ob_start();
                        include $view_path;
                        $html = ob_get_contents();
                        @ob_end_clean();
                        return $html;
                    } else {
                        include $view_path;
                        return;
                    }
                } else {
                    trigger404('View:' . $view_path . ' not found');
                }
            }
        } else {
            //没有指定键，遍历所有视图文件夹
            $view_path = '';
            foreach ($view_folders as $dir) {
                $view_path = $dir . DIRECTORY_SEPARATOR . $view_name . $system['view_file_subfix'];
                if (file_exists($view_path)) {
                    if ($return) {
                        @ob_start();
                        include $view_path;
                        $html = ob_get_contents();
                        @ob_end_clean();
                        return $html;
                    } else {
                        include $view_path;
                        return;
                    }
                } elseif (($i++) == $count - 1) {
                    trigger404('View:' . $view_path . ' not found');
                }
            }
        }
    }

    public static function classAutoloadRegister() {
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
        $system = WoniuLoader::$system;
        $library_folders = $system['library_folder'];
        if (!is_array($library_folders)) {
            $library_folders = array($library_folders);
        }
        foreach ($library_folders as $library_folder) {
            $library = $library_folder . DIRECTORY_SEPARATOR . $clazzName . $system['library_file_subfix'];
            if (file_exists($library)) {
                self::includeOnce($library);
            } else {
                if (is_dir($library_folder)) {
                    $dir = dir($library_folder);
                    $found = false;
                    while (($file = $dir->read()) !== false) {
                        if ($file == '.' || $file == '..' || is_file($library_folder . DIRECTORY_SEPARATOR . $file)) {
                            continue;
                        }
                        $path = truepath($library_folder) . DIRECTORY_SEPARATOR . $file . DIRECTORY_SEPARATOR . $clazzName . $system['library_file_subfix'];
                        if (file_exists($path)) {
                            self::includeOnce($path);
                            $found = true;
                            break;
                        }
                    }
                    if ($found) {
                        break;
                    }
                }
            }
        }
    }

    /**
     * 自定义Loader，用于拓展框架核心功能,
     * Loader是控制器和模型都继承的一个类，大部分核心功能都在loader中完成。
     * 这里是自定义Loader类文件的完整路径
     * 自定义Loader文件名称和类名称必须是：
     * 文件名称：类名.class.php
     * 比如：MyLoader.class.php，文件里面的类名就是:MyLoader
     * 注意：
     * 1.自定义Loader必须继承WoniuLoader。
     * 2.一个最简单的Loader示意：(假设文件名称是：MyLoader.class.php)
     * class MyLoader extends WoniuLoader {
     *      public function __construct() {
     *          parent::__construct();
     *      }
     *  } 
     * 3.如果无需自定义Loader，留空即可。
     */
    public static function checkUserLoader() {
        global $system;
        if (!class_exists('WoniuLoaderPlus', FALSE)) {
            if (!empty($system['my_loader'])) {
                self::includeOnce($system['my_loader']);
                $clazz = basename($system['my_loader'], '.class.php');
                if (class_exists($clazz, FALSE)) {
                    eval('class WoniuLoaderPlus extends ' . $clazz . '{}');
                } else {
                    eval('class WoniuLoaderPlus extends WoniuLoader{}');
                }
            } else {
                eval('class WoniuLoaderPlus extends WoniuLoader{}');
            }
        }
    }

    /**
     * 实例化一个loader
     * @param type $renew               是否强制重新new一个loader，默认只会new一次
     * @param type $hmvc_module_floder  hmvc模块文件夹名称
     * @return type WoniuLoader
     */
    public static function instance($renew = null, $hmvc_module_floder = null) {
        $default = WoniuLoader::$system;
        if (!empty($hmvc_module_floder)) {
            WoniuRouter::switchHmvcConfig($hmvc_module_floder);
        }
        //在plugin模式下，路由器不再使用，那么自动注册不会被执行，自动加载功能会失效，所以在这里再尝试加载一次，
        //如此一来就能满足两种模式
        self::classAutoloadRegister();
        //这里调用控制器instance是为了触发自动加载，从而避免了插件模式下，直接instance模型，自动加载失效的问题
        WoniuController::instance();
        $renew = is_bool($renew) && $renew === true;
        $ret = empty(self::$instance) || $renew ? self::$instance = new self() : self::$instance;
        WoniuLoader::$system = $default;
        return $ret;
    }

    /**
     * 获取视图绝对路径，在视图中include其它视图的时候用到。
     * 提示：
     * hvmc模式，“视图路经数组”是模块的视图数组和主配置视图数组合并后的数组。
     * 即:$hmvc_system['view_folder']=array_merge($hmvc_system['view_folder'], $system['view_folder']);
     * @param type $view_name 视图名称，不含后缀
     * @param type $path_key  就是配置中“视图路经数组”的键
     * @return string
     */
    public static function view_path($view_name, $path_key = 0) {
        if (stripos($view_name, ':') !== false) {
            $info = explode(':', $view_name);
            $path_key = current($info);
            $view_name = next($info);
        }
        $system = WoniuLoader::$system;
        if (!is_array($system['view_folder'])) {
            $system['view_folder'] = array($system['view_folder']);
        }
        if (!isset($system['view_folder'][$path_key])) {
            trigger404('error key[' . $path_key . '] of $system["view_folder"]');
        }
        $dir = $system['view_folder'][$path_key];
        $view_path = $dir . DIRECTORY_SEPARATOR . $view_name . $system['view_file_subfix'];
        return truepath($view_path);
    }

    public function ajax_echo($code, $tip = null, $data = null, $jsonp_callback = null, $is_exit = true) {
        $str = json_encode(array('code' => $code, 'tip' => is_null($tip) ? '' : $tip, 'data' => is_null($data) ? '' : $data));
        if (!empty($jsonp_callback)) {
            echo $jsonp_callback . "($str)";
        } else {
            echo $str;
        }
        if ($is_exit) {
            exit();
        }
    }

    public static function xml_echo($xml, $is_exit = true) {
        header('Content-type:text/xml;charset=utf-8');
        echo $xml;
        if ($is_exit) {
            exit();
        }
    }

    public function redirect($url, $msg = null, $time = 3, $view = null) {
        $time = intval($time) ? intval($time) : 3;
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
        exit();
    }

    public function message($msg, $url = null, $time = 3, $view = null) {
        $time = intval($time) ? intval($time) : 3;
        if (!empty($url)) {
            header("refresh:{$time};url={$url}"); //单位秒
        }
        header("Content-type: text/html; charset=utf-8");
        $view = is_null($view) ? systemInfo('message_page_view') : $view;
        if (!empty($view)) {
            $this->view($view, array('msg' => $msg, 'url' => $url, 'time' => $time));
        } else {
            echo $msg;
        }
        exit();
    }

    public static function setCookieRaw($key, $value, $life = null, $path = '/', $domian = null, $http_only = false) {
        header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
        if (!is_null($domian)) {
            $auto_domain = $domian;
        } else {
            $host = WoniuInput::server('HTTP_HOST');
            // $_host = current(explode(":", $host));
            $is_ip = preg_match('/^((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)$/', $host);
            $not_regular_domain = preg_match('/^[^\\.]+$/', $host);
            if ($is_ip) {
                $auto_domain = $host;
            } elseif ($not_regular_domain) {
                $auto_domain = NULL;
            } else {
                $auto_domain = '.' . $host;
            }
        }
        setcookie($key, $value, ($life ? $life + time() : null), $path, $auto_domain, (WoniuInput::server('SERVER_PORT') == 443 ? 1 : 0), $http_only);
        $_COOKIE[$key] = $value;
    }

    /**
     * 设置一个cookie，该方法会在key前面加上系统配置里面的$system['cookie_key_prefix']前缀
     * 如果不想加前缀，可以使用方法：$this->setCookieRaw()
     */
    public static function setCookie($key, $value, $life = null, $path = '/', $domian = null, $http_only = false) {
        $key = systemInfo('cookie_key_prefix') . $key;
        return self::setCookieRaw($key, $value, $life, $path, $domian, $http_only);
    }

    /**
     * 分页函数
     * @param type $total 一共多少记录
     * @param type $page  当前是第几页
     * @param type $pagesize 每页多少
     * @param type $url    url是什么，url里面的{page}会被替换成页码
     * @param array $order 分页条的组成，是一个数组，可以按着1-6的序号，选择分页条组成部分和每个部分的顺序
     * @param int $a_count   分页条中a页码链接的总数量,不包含当前页的a标签，默认10个。
     * @return type  String
     * echo WoniuLoader::instance()->page(100,3,10,'?article/list/{page}',array(3,4,5,1,2,6));
     */
    public static function page($total, $page, $pagesize, $url, $order = array(1, 2, 3, 4, 5, 6), $a_count = 10) {
        $a_num = $a_count;
        $first = '首页';
        $last = '尾页';
        $pre = '上页';
        $next = '下页';
        $a_num = $a_num % 2 == 0 ? $a_num + 1 : $a_num;
        $pages = ceil($total / $pagesize);
        $curpage = intval($page) ? intval($page) : 1;
        $curpage = $curpage > $pages || $curpage <= 0 ? 1 : $curpage; #当前页超范围置为1
        $body = '<span class="page_body">';
        $prefix = '';
        $subfix = '';
        $start = $curpage - ($a_num - 1) / 2; #开始页
        $end = $curpage + ($a_num - 1) / 2;  #结束页
        $start = $start <= 0 ? 1 : $start;   #开始页超范围修正
        $end = $end > $pages ? $pages : $end; #结束页超范围修正
        if ($pages >= $a_num) {#总页数大于显示页数
            if ($curpage <= ($a_num - 1) / 2) {
                $end = $a_num;
            }//当前页在左半边补右边
            if ($end - $curpage <= ($a_num - 1) / 2) {
                $start-=floor($a_num / 2) - ($end - $curpage);
            }//当前页在右半边补左边
        }
        for ($i = $start; $i <= $end; $i++) {
            if ($i == $curpage) {
                $body.='<a class="page_cur_page" href="javascript:void(0);"><b>' . $i . '</b></a>';
            } else {
                $body.='<a href="' . str_replace('{page}', $i, $url) . '">' . $i . '</a>';
            }
        }
        $body.='</span>';
        $prefix = ($curpage == 1 ? '' : '<span class="page_bar_prefix"><a href="' . str_replace('{page}', 1, $url) . '">' . $first . '</a><a href="' . str_replace('{page}', $curpage - 1, $url) . '">' . $pre . '</a></span>');
        $subfix = ($curpage == $pages ? '' : '<span class="page_bar_subfix"><a href="' . str_replace('{page}', $curpage + 1, $url) . '">' . $next . '</a><a href="' . str_replace('{page}', $pages, $url) . '">' . $last . '</a></span>');
        $info = "<span class=\"page_cur\">第{$curpage}/{$pages}页</span>";
        $go = '<script>function ekup(){if(event.keyCode==13){clkyup();}}function clkyup(){var num=document.getElementById(\'gsd09fhas9d\').value;if(!/^\d+$/.test(num)||num<=0||num>' . $pages . '){alert(\'请输入正确页码!\');return;};location=\'' . addslashes($url) . '\'.replace(/\\{page\\}/,document.getElementById(\'gsd09fhas9d\').value);}</script><span class="page_input_num"><input onkeyup="ekup()" type="text" id="gsd09fhas9d" style="width:40px;vertical-align:text-baseline;padding:0 2px;font-size:10px;border:1px solid gray;"/></span><span id="gsd09fhas9daa" class="page_btn_go" onclick="clkyup();" style="cursor:pointer;text-decoration:underline;">转到</span>';
        $total = "<span class=\"page_total\">共{$total}条</span>";
        $pagination = array(
            $total,
            $info,
            $prefix,
            $body,
            $subfix,
            $go
        );
        $output = array();
        if (is_null($order)) {
            $order = array(1, 2, 3, 4, 5, 6);
        }
        foreach ($order as $key) {
            if (isset($pagination[$key - 1])) {
                $output[] = $pagination[$key - 1];
            }
        }
        return implode("", $output);
    }

    /**
     * $source_data和$map的key一致，$map的value是返回数据的key
     * 根据$map的key读取$source_data中的数据，结果是以map的value为key的数数组
     * 
     * @param Array $map 字段映射数组,格式：array('表单name名称'=>'表字段名称',...)
     */
    public static function readData(Array $map, $source_data = null) {
        $data = array();
        $formdata = is_null($source_data) ? WoniuInput::post() : $source_data;
        foreach ($formdata as $form_key => $val) {
            if (isset($map[$form_key])) {
                $data[$map[$form_key]] = $val;
            }
        }
        return $data;
    }

    public static function checkData(Array $rule, Array $data = NULL, &$return_data = NULL) {
        if (is_null($data)) {
            $data = WoniuInput::post();
        }
        $return_data = $data;
        /**
         * 验证前默认值规则处理
         */
        foreach ($rule as $col => $val) {
            //提取出默认值
            foreach ($val as $_rule => $msg) {
                if (stripos($_rule, 'default[') === 0) {
                    //删除默认值规则
                    unset($rule[$col][$_rule]);
                    $matches = self::getCheckRuleInfo($_rule);
                    $_r = $matches[1];
                    $args = $matches[2];
                    $return_data[$col] = isset($args[0]) ? $args[0] : '';
                }
            }
        }
        /**
         * 验证前默认值规则处理,没有默认值就补空
         * 并标记最后要清理的key
         */
        $unset_keys = array();
        foreach ($rule as $col => $val) {
            if (!isset($return_data[$col])) {
                $return_data[$col] = '';
                $unset_keys[] = $col;
            }
        }
        /**
         * 验证前set处理
         */
        self::checkSetData('set', $rule, $return_data);
        /**
         * 验证规则
         */
        foreach ($rule as $col => $val) {
            foreach ($val as $_rule => $msg) {
                if (!empty($_rule)) {
                    /**
                     * 可以为空规则检测
                     */
                    if (empty($return_data[$col]) && isset($val['optional'])) {
                        //当前字段，验证通过
                        break;
                    } else {
                        $matches = self::getCheckRuleInfo($_rule);
                        $_r = $matches[1];
                        $args = $matches[2];
                        if ($_r == 'set' || $_r == 'set_post' || $_r == 'optional') {
                            continue;
                        }
                        if (!self::checkRule($_rule, $return_data[$col], $return_data)) {
                            /**
                             * 清理没有传递的key
                             */
                            foreach ($unset_keys as $key) {
                                unset($return_data[$key]);
                            }
                            return $msg;
                        }
                    }
                }
            }
        }
        /**
         * 验证后set_post处理
         */
        self::checkSetData('set_post', $rule, $return_data);

        /**
         * 清理没有传递的key
         */
        foreach ($unset_keys as $key) {
            unset($return_data[$key]);
        }
        return NULL;
    }

    private static function checkSetData($type, Array $rule, &$return_data = NULL) {
        foreach ($rule as $col => $val) {
            foreach (array_keys($val) as $_rule) {
                if (!empty($_rule)) {
                    #有规则而且不是非必须的，但是没有数据，就补上空数据，然后进行验证
                    if (!isset($return_data[$col])) {
                        if (isset($_rule['optional'])) {
                            break;
                        } else {
                            $return_data[$col] = '';
                        }
                    }
                    $matches = self::getCheckRuleInfo($_rule);
                    $_r = $matches[1];
                    $args = $matches[2];
                    if ($_r == $type) {
                        $_v = $return_data[$col];
                        $_args = array($_v, $return_data);
                        foreach ($args as $func) {
                            if (function_exists($func)) {
                                $reflection = new ReflectionFunction($func);
                                //如果是系统函数
                                if ($reflection->isInternal()) {
                                    $_args = array($_v);
                                }
                            }
                            $_v = self::callFunc($func, $_args);
                        }
                        $return_data[$col] = $_v;
                    }
                }
            }
        }
    }

    private static function getCheckRuleInfo($_rule) {
        $matches = array();
        preg_match('|([^\[]+)(?:\[(.*)\](.?))?|', $_rule, $matches);
        $matches[1] = isset($matches[1]) ? $matches[1] : '';
        $matches[3] = !empty($matches[3]) ? $matches[3] : ',';
        if ($matches[1] != 'reg') {
            $matches[2] = isset($matches[2]) ? explode($matches[3], $matches[2]) : array();
        } else {
            $matches[2] = isset($matches[2]) ? array($matches[2]) : array();
        }
        return $matches;
    }

    /**
     * 调用一个方法或者函数(无论方法是静态还是动态，是私有还是保护还是公有的都可以调用)
     * 所有示例：
     * 1.调用类的静态方法
     * $ret=$this->callFunc('UserModel::encodePassword', $args);
     * 2.调用类的方法
     * $ret=$this->callFunc(array('UserModel','checkPassword), $args);
     * 3.调用用户自定义方法
     * $ret=$this->callFunc('cleanJs', $args);
     * 4.调用系统函数
     * $ret=$this->callFunc('var_dump', $args);
     * @param type $func
     * @param type $args
     * @return boolean
     */
    public static function callFunc($func, $args) {
        if (is_array($func)) {
            return self::callMethod($func, $args);
        } elseif (function_exists($func)) {
            return call_user_func_array($func, $args);
        } elseif (stripos($func, '::')) {
            $_func = explode('::', $func);
            return self::callMethod($_func, $args);
        }
        return null;
    }

    private static function callMethod($_func, $args) {
        $class = $_func[0];
        $method = $_func[1];
        if (is_object($class)) {
            $class = new ReflectionClass(get_class($class));
        } else {
            $class = new ReflectionClass($class);
        }
        $obj = $class->newInstanceArgs();
        $method = $class->getMethod($method);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }

    private static function checkRule($_rule, $val, $data) {
        $matches = self::getCheckRuleInfo($_rule);
        $_rule = $matches[1];
        $args = $matches[2];
        switch ($_rule) {
            case 'required':
                return !empty($val);
            case 'match':
                return isset($args[0]) && isset($data[$args[0]]) ? $val && ($val == $data[$args[0]]) : false;
            case 'equal':
                return isset($args[0]) ? $val && ($val == $args[0]) : false;
            case 'enum':
                return in_array($val, $args);
            case 'unique':#比如unique[user.name] , unique[user.name,id:1]
                if (!$val || !count($args)) {
                    return false;
                }
                $_info = explode('.', $args[0]);
                if (count($_info) != 2) {
                    return false;
                }
                $table = $_info[0];
                $col = $_info[1];
                if (isset($args[1])) {
                    $_id_info = explode(':', $args[1]);
                    if (count($_id_info) != 2) {
                        return false;
                    }
                    $id_col = $_id_info[0];
                    $id = $_id_info[1];
                    $id = stripos($id, '#') === 0 ? WoniuInput::get_post(substr($id, 1)) : $id;
                    $where = array($col => $val, "$id_col <>" => $id);
                } else {
                    $where = array($col => $val);
                }
                return !WoniuLoader::instance()->database()->where($where)->from($table)->count_all_results();
            case 'exists':#比如exists[user.name] , exists[user.name,type:1], exists[user.name,type:1,sex:#sex]
                if (!$val || !count($args)) {
                    return false;
                }
                $_info = explode('.', $args[0]);
                if (count($_info) != 2) {
                    return false;
                }
                $table = $_info[0];
                $col = $_info[1];
                $where = array($col => $val);
                if (count($args) > 1) {
                    foreach (array_slice($args, 1) as $v) {
                        $_id_info = explode(':', $v);
                        if (count($_id_info) != 2) {
                            continue;
                        }
                        $id_col = $_id_info[0];
                        $id = $_id_info[1];
                        $id = stripos($id, '#') === 0 ? WoniuInput::get_post(substr($id, 1)) : $id;
                        $where[$id_col] = $id;
                    }
                }
                return WoniuLoader::instance()->database()->where($where)->from($table)->count_all_results();
            case 'min_len':
                return isset($args[0]) ? (mb_strlen($val, 'UTF-8') >= intval($args[0])) : false;
            case 'max_len':
                return isset($args[0]) ? (mb_strlen($val, 'UTF-8') <= intval($args[0])) : false;
            case 'range_len':
                return count($args) == 2 ? (mb_strlen($val, 'UTF-8') >= intval($args[0])) && (mb_strlen($val, 'UTF-8') <= intval($args[1])) : false;
            case 'len':
                return isset($args[0]) ? (mb_strlen($val, 'UTF-8') == intval($args[0])) : false;
            case 'min':
                return isset($args[0]) && is_numeric($val) ? $val >= $args[0] : false;
            case 'max':
                return isset($args[0]) && is_numeric($val) ? $val <= $args[0] : false;
            case 'range':
                return (count($args) == 2) && is_numeric($val) ? $val >= $args[0] && $val <= $args[1] : false;
            case 'alpha':#纯字母
                return !preg_match('/[^A-Za-z]+/', $val);
            case 'alpha_num':#纯字母和数字
                return !preg_match('/[^A-Za-z0-9]+/', $val);
            case 'alpha_dash':#纯字母和数字和下划线和-
                return !preg_match('/[^A-Za-z0-9_-]+/', $val);
            case 'alpha_start':#以字母开头
                return preg_match('/^[A-Za-z]+/', $val);
            case 'num':#纯数字
                return !preg_match('/[^0-9]+/', $val);
            case 'int':#整数
                return preg_match('/^([-+]?[1-9]\d*|0)$/', $val);
            case 'float':#小数
                return preg_match('/^([1-9]\d*|0)\.\d+$/', $val);
            case 'numeric':#数字-1，1.2，+3，4e5
                return is_numeric($val);
            case 'natural':#自然数0，1，2，3，12，333
                return preg_match('/^([1-9]\d*|0)$/', $val);
            case 'natural_no_zero':#自然数不包含0
                return preg_match('/^[1-9]\d*$/', $val);
            case 'email':
                $args[0] = isset($args[0]) && $args[0] == 'true' ? TRUE : false;
                return !empty($val) ? preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/', $val) : $args[0];
            case 'url':
                $args[0] = isset($args[0]) && $args[0] == 'true' ? TRUE : false;
                return !empty($val) ? preg_match('/^http[s]?:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"])*$/', $val) : $args[0];
            case 'qq':
                $args[0] = isset($args[0]) && $args[0] == 'true' ? TRUE : false;
                return !empty($val) ? preg_match('/^[1-9][0-9]{4,}$/', $val) : $args[0];
            case 'phone':
                $args[0] = isset($args[0]) && $args[0] == 'true' ? TRUE : false;
                return !empty($val) ? preg_match('/^(?:\d{3}-?\d{8}|\d{4}-?\d{7})$/', $val) : $args[0];
            case 'mobile':
                $args[0] = isset($args[0]) && $args[0] == 'true' ? TRUE : false;
                return !empty($val) ? preg_match('/^(((13[0-9]{1})|(15[0-9]{1})|(18[0-9]{1})|(14[0-9]{1}))+\d{8})$/', $val) : $args[0];
            case 'zipcode':
                $args[0] = isset($args[0]) && $args[0] == 'true' ? TRUE : false;
                return !empty($val) ? preg_match('/^[1-9]\d{5}(?!\d)$/', $val) : $args[0];
            case 'idcard':
                $args[0] = isset($args[0]) && $args[0] == 'true' ? TRUE : false;
                return !empty($val) ? preg_match('/^\d{14}(\d{4}|(\d{3}[xX])|\d{1})$/', $val) : $args[0];
            case 'ip':
                $args[0] = isset($args[0]) && $args[0] == 'true' ? TRUE : false;
                return !empty($val) ? preg_match('/^((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)$/', $val) : $args[0];
            case 'chs':
                $count = implode(',', array_slice($args, 1, 2));
                $count = empty($count) ? '1,' : $count;
                $can_empty = isset($args[0]) && $args[0] == 'true';
                return !empty($val) ? preg_match('/^[\x{4e00}-\x{9fa5}]{' . $count . '}$/u', $val) : $can_empty;
            case 'date':
                $args[0] = isset($args[0]) && $args[0] == 'true' ? TRUE : false;
                return !empty($val) ? preg_match('/^[0-9]{4}-(((0[13578]|(10|12))-(0[1-9]|[1-2][0-9]|3[0-1]))|(02-(0[1-9]|[1-2][0-9]))|((0[469]|11)-(0[1-9]|[1-2][0-9]|30)))$/', $val) : $args[0];
            case 'time':
                $args[0] = isset($args[0]) && $args[0] == 'true' ? TRUE : false;
                return !empty($val) ? preg_match('/^(([0-1][0-9])|([2][0-3])):([0-5][0-9])(:([0-5][0-9]))$/', $val) : $args[0];
            case 'datetime':
                $args[0] = isset($args[0]) && $args[0] == 'true' ? TRUE : false;
                return !empty($val) ? preg_match('/^[0-9]{4}-(((0[13578]|(10|12))-(0[1-9]|[1-2][0-9]|3[0-1]))|(02-(0[1-9]|[1-2][0-9]))|((0[469]|11)-(0[1-9]|[1-2][0-9]|30))) (([0-1][0-9])|([2][0-3])):([0-5][0-9])(:([0-5][0-9]))$/', $val) : $args[0];

            case 'reg':#正则表达式验证,reg[/^[\]]$/i]
                /**
                 * 模式修正符说明:
                  i	表示在和模式进行匹配进不区分大小写
                  m	将模式视为多行，使用^和$表示任何一行都可以以正则表达式开始或结束
                  s	如果没有使用这个模式修正符号，元字符中的"."默认不能表示换行符号,将字符串视为单行
                  x	表示模式中的空白忽略不计
                  e	正则表达式必须使用在preg_replace替换字符串的函数中时才可以使用(讲这个函数时再说)
                  A	以模式字符串开头，相当于元字符^
                  Z	以模式字符串结尾，相当于元字符$
                  U	正则表达式的特点：就是比较“贪婪”，使用该模式修正符可以取消贪婪模式
                 */
                return !empty($args[0]) ? preg_match($args[0], $val) : false;
            /**
             * set set_post不参与验证，返回true跳过
             * 
             * 说明：
             * set用于设置在验证数据前对数据进行处理的函数或者方法
             * set_post用于设置在验证数据后对数据进行处理的函数或者方法
             * 如果设置了set，数据在验证的时候验证的是处理过的数据
             * 如果设置了set_post，可以通过第三个参数$data接收数据：$this->checkData($rule, $_POST, $data)，$data是验证通过并经过set_post处理后的数据
             * set和set_post后面是一个或者多个函数或者方法，多个逗号分割
             * 注意：
             * 1.无论是函数或者方法都必须有一个字符串返回
             * 2.如果是系统函数，系统会传递当前值给系统函数，因此系统函数必须是至少接受一个字符串参数，比如md5，trim
             * 3.如果是自定义的函数，系统会传递当前值和全部数据给自定义的函数，因此自定义函数可以接收两个参数第一个是值，第二个是全部数据$data
             * 4.如果是类的方法写法是：类名称::方法名 （方法静态动态都可以，public，private，都可以）
             */
            case 'set':
            case 'set_post':
                return true;
            default:
                $_args = array_merge(array($val, $data), $args);
                $matches = self::getCheckRuleInfo($_rule);
                $func = $matches[1];
                $args = $matches[2];
                if (function_exists($func)) {
                    $reflection = new ReflectionFunction($func);
                    //如果是系统函数
                    if ($reflection->isInternal()) {
                        $_args = isset($_args[0]) ? array($_args[0]) : array();
                    }
                }
                return self::callFunc($_rule, $_args);
        }
        return false;
    }

    public static function includeOnce($file_path) {
        $key = md5(truepath(convertPath($file_path)));
        if (!isset(self::$files[$key])) {
            include $file_path;
            self::$files[$key] = 1;
        }
    }

}


WoniuLoader::checkUserLoader();

class WoniuModelLoader {

    public static $model_files = array();

    function __get($classname) {
        if (isset(self::$model_files[$classname])) {
            return self::$model_files[$classname];
        } else {
            return WoniuLoader::model($classname);
        }
    }

}

class WoniuLibLoader {

    public static $lib_files = array();

    function __get($classname) {
        if (isset(self::$lib_files[$classname])) {
            return self::$lib_files[$classname];
        } else {
            return WoniuLoader::lib($classname);
        }
    }

}

/* End of file Loader.php */
