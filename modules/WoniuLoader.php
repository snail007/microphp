<?php

/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package                MicroPHP
 * @author                狂奔的蜗牛
 * @email                672308444@163.com
 * @copyright          Copyright (c) 2013 - 2013, 狂奔的蜗牛, Inc.
 * @link                https://bitbucket.org/snail/microphp/
 * @since                Version 1.0
 * @createdtime       {createdtime}
 */
class WoniuLoader {

    public $db, $input;
    private $helper_files = array();
    public $model, $lib;
    private $view_vars = array();
    private static $instance;

    public function __construct() {
        date_default_timezone_set($this->config('system', 'default_timezone'));
        $this->input = new WoniuInput();
        $this->model = new WoniuModelLoader();
        $this->lib = new WoniuLibLoader();
        WoniuCache::$path = 'cache';
        $this->autoload();
        if ($this->config('system', "autoload_db")) {
            $this->database();
        }
        stripslashes_all();
    }

    private function autoload() {
        $autoload_helper = $this->config('system', 'helper_file_autoload');
        $autoload_library = $this->config('system', 'library_file_autoload');
        foreach ($autoload_helper as $file_name) {
            $this->helper($file_name);
        }
        foreach ($autoload_library as $key => $val) {
            if (is_array($val)) {
                $key = key($val);
                $val = $val[$key];
                $this->lib($key, $val);
            } else {
                $this->lib($val);
            }
        }
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

    public function lib($file_name, $alias_name = null) {
        global $system;
        $classname = $file_name;
        if (strstr($file_name, '/') !== false || strstr($file_name, "\\") !== false) {
            $classname = basename($file_name);
        }
        if (!$alias_name) {
            $alias_name = strtolower($classname);
        }
        $filepath = $system['library_folder'] . DIRECTORY_SEPARATOR . $file_name . $system['library_file_subfix'];

        if (in_array($alias_name, array_keys(WoniuLibLoader::$lib_files))) {
            return WoniuLibLoader::$lib_files[$alias_name];
        }
        if (file_exists($filepath)) {
            include $filepath;
            if (class_exists($classname)) {
                return WoniuLibLoader::$lib_files[$alias_name] = new $classname();
            } else {
                trigger404('Model Class:' . $classname . ' not found.');
            }
        } else {
            trigger404($filepath . ' not found.');
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
        setcookie($key, $value, ($life ? $life + time() : null), $path, ($domian ? $domian : '.' . $this->input->server('HTTP_HOST')), ($this->input->server('SERVER_PORT') == 443 ? 1 : 0));
        $_COOKIE[$key] = $value;
    }

    /**
     * 分页函数
     * @param type $total 一共多少记录
     * @param type $page  当前是第几页
     * @param type $pagesize 每页多少
     * @param type $url    url是什么，url里面的{page}会被替换成页码
     * @return type  String
     */
    public function page($total, $page, $pagesize, $url) {
        $a_num = 10;
        $first = ' 首页 ';
        $last = ' 尾页 ';
        $pre = ' 上页 ';
        $next = ' 下页 ';
        $a_num = $a_num % 2 == 0 ? $a_num + 1 : $a_num;
        $pages = ceil($total / $pagesize);
        $curpage = intval($page) ? intval($page) : 1;
        $curpage = $curpage > $pages || $curpage <= 0 ? 1 : $curpage; #当前页超范围置为1
        $body = '';
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
                $start-=5 - ($end - $curpage);
            }//当前页在右半边补左边
        }
        for ($i = $start; $i <= $end; $i++) {
            if ($i == $curpage) {
                $body.='<b>' . $i . '</b>';
            } else {
                $body.='<a href="' . str_replace('{page}', $i, $url) . '"> ' . $i . ' </a>';
            }
        }
        $prefix = ($curpage == 1 ? '' : '<a href="' . str_replace('{page}', 1, $url) . '">' . $first . '</a><a href="' . str_replace('{page}', $curpage - 1, $url) . '">' . $pre . '</a>');
        $subfix = ($curpage == $pages ? '' : '<a href="' . str_replace('{page}', $curpage + 1, $url) . '">' . $next . '</a><a href="' . str_replace('{page}', $pages, $url) . '">' . $last . '</a>');
        $info = " 第{$curpage}/{$pages}页 ";
        $go = '<script>function ekup(){if(event.keyCode==13){clkyup();}}function clkyup(){var num=document.getElementById(\'gsd09fhas9d\').value;if(!/^\d+$/.test(num)||num<=0||num>' . $pages . '){alert(\'请输入正确页码!\');return;};location=\'' . $url . '\'.replace(/\\{page\\}/,document.getElementById(\'gsd09fhas9d\').value);}</script><input onkeyup="ekup()" type="text" id="gsd09fhas9d" style="width:40px;vertical-align:text-baseline;padding:0 2px;font-size:10px;border:1px solid gray;"/> <span id="gsd09fhas9daa" onclick="clkyup();" style="cursor:pointer;text-decoration:underline;">转到</span>';
        $total = "共{$total}条";
        return $total . ' ' . $info . ' ' . $prefix . $body . $subfix . '&nbsp;' . $go;
    }

    /**
     * $source_data和$map的key一致，$map的value是返回数据的key
     * 根据$map的key读取$source_data中的数据，结果是以map的value为key的数数组
     * 
     * @param Array $map 字段映射数组
     */
    public function readData(Array $map, $source_data = null) {
        $data = array();
        $formdata = is_null($source_data) ? $this->input->post() : $source_data;
        foreach ($formdata as $form_key => $val) {
            if (isset($map[$form_key])) {
                $data[$map[$form_key]] = $val;
            }
        }
        return $data;
    }

    public function checkData(Array $rule, Array $data) {
        foreach ($rule as $col => $val) {
            if ($val['rule']) {
                #有规则但是没有数据，就补上空数据，然后进行验证
                if (!isset($data[$col])) {
                    $data[$col] = '';
                }
                #函数验证
                if (strpos($val['rule'], '/') === FALSE) {
                    return $this->{$val['rule']}($data[$col], $data);
                } else {
                    #正则表达式验证
                    if (!preg_match($val['rule'], $data[$col])) {
                        return $val['msg'];
                    }
                }
            }
        }
        return NULL;
    }

}

class WoniuModelLoader {

    public static $model_files = array();

    function __get($classname) {
        return isset(self::$model_files[strtolower($classname)]) ? self::$model_files[strtolower($classname)] : null;
    }

}

class WoniuLibLoader {

    public static $lib_files = array();

    function __get($classname) {
        return isset(self::$lib_files[strtolower($classname)]) ? self::$lib_files[strtolower($classname)] : null;
    }

}

/* End of file Loader.php */