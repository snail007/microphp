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
 * @since		Version 2.2.7
 * @createdtime         2014-05-13 11:39:58
 */
 


//####################modules/WoniuHelper.php####################{


/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package                MicroPHP
 * @author                狂奔的蜗牛
 * @email                672308444@163.com
 * @copyright          Copyright (c) 2013 - 2014, 狂奔的蜗牛, Inc.
 * @link                http://git.oschina.net/snail/microphp
 * @since                Version 2.2.7
 * @createdtime       2014-05-13 11:39:58
 */
if (!function_exists('dump')) {

    /**
     * 打印变量内容，参数和var_dump一样
     * @param type $arg
     * @param type $_
     */
    function dump($arg, $_ = null) {
        $args = func_get_args();
        if (WoniuInput::isCli()) {
            call_user_func_array('var_dump', $args);
        } else {
            echo '<pre>';
            call_user_func_array('var_dump', $args);
            echo '</pre>';
        }
    }

}
if (!function_exists('url')) {

    /**
     * 生成url链接<br>
     * 使用示例：<br>
     * url(),<br>
     * url('welcome.index'),<br>
     * url('welcome.index','aa','bb'),<br>
     * url('welcome.index',array('a'=>'bb','b'=>'ccc'),'dd','ee'),<br>
     * url('welcome.index','dd','ee',array('a'=>'bb')),<br>
     * url('welcome.index',array('a'=>'bb','b'=>'ccc')),<br>
     * url('','aa','bb'),<br>
     * url('',array('a'=>'bb','b'=>'ccc'),'dd','ee'),<br>
     * url('',array('a'=>'bb','b'=>'ccc')),<br>
     * 另外可以在第一个参数开始加上:<br>
     * #和?用来控制url中显示入口文件名称和使用相对路经<br>
     * 默认不显示入口文件名称，使用绝对路经<br>
     * 使用示例：<br>
     * url('#welcome.index'),<br>
     * url('?welcome.index'),<br>
     * url('#?welcome.index'),<br>
     * url('?#welcome.index'),<br>
     * @return string
     */
    function url() {
        $action = null;
        $argc = func_num_args();
        if ($argc > 0) {
            $action = func_get_arg(0);
        }
        $args = array();
        $get_str_arr = array();
        if ($argc > 1) {
            for ($i = 1; $i < $argc; $i++) {
                if (is_array($arg = func_get_arg($i))) {
                    foreach ($arg as $k => $v) {
                        $get_str_arr[] = $k . '=' . urlencode($v);
                    }
                } else {
                    $args[] = $arg;
                }
            }
        }

        if (empty(WoniuLoader::$system['url_rewrite'])) {
            //url是否包含入口文件名称检查
            $self_name = stripos($action, '#') === 0 || stripos($action, '#') === 1 ? pathinfo(WoniuInput::server('php_self'), PATHINFO_BASENAME) : '';
            $app_start = '?';
            $get_start = '&';
        } else {
            $self_name = '';
            $app_start = '';
            $get_start = '?';
        }
        //是否使用相对路经检查
        $path = (stripos($action, '?') === 0 || stripos($action, '?') === 1 ? '' : urlPath() . '/' );

        $action = ltrim($action, '#?');
        $url_app = $path . $self_name .
                (empty($args) && empty($get_str_arr) && empty($action) ? '' : $app_start) .
                ($action . (empty($args) || empty($action) ? '' : '/' ) . implode('/', $args)) .
                (empty($get_str_arr) ? '' : $get_start . implode('&', $get_str_arr));
        return str_replace('?&', '?', $url_app);
    }

}

if (!function_exists('urlPath')) {

    /**
     * 获取入口文件所在目录url路径。
     * 只能在web访问时使用，在命令行下面会抛出异常。
     * @param type $subpath  子路径或者文件路径，如果非空就会被附加在入口文件所在目录的后面
     * @return type           
     * @throws Exception     
     */
    function urlPath($subpath = null) {
        if (WoniuInput::isCli()) {
            throw new Exception('function urlPath() can not be used in cli mode');
        } else {
            $old_path = getcwd();
            $root = str_replace(array("/", "\\"), '/', WoniuInput::server('DOCUMENT_ROOT'));
            chdir($root);
            $root = getcwd();
            $root = str_replace(array("/", "\\"), '/', $root);
            chdir($old_path);
            $path = path($subpath);
            return str_replace($root, '', $path);
        }
    }

}

if (!function_exists('path')) {

    /**
     * 获取入口文件所在目录绝对路径。
     * @param type $subpath 子路径或者文件路径，如果非空就会被附加在入口文件所在目录的绝对路径后面
     * @return type
     */
    function path($subpath = null) {
        $path = str_replace(array("/", "\\"), '/', realpath('.') . ($subpath ? '/' . trim($subpath, '/\\') : ''));
        return truepath($path);
    }

}
/**
 * 获取系统配置信息,也就是WoniuLoader::$system里面的信息
 * @param type $key  WoniuLoader::$system的键
 * @return null
 */
if (!function_exists('systemInfo')) {

    function systemInfo($key = NULL) {
        if (is_null($key)) {
            return WoniuLoader::$system;
        } elseif (isset(WoniuLoader::$system[$key])) {
            return WoniuLoader::$system[$key];
        } else {
            return null;
        }
    }

}
/**
 * 获取系统数据库配置信息
 * @param type $group  数据库组名称，即WoniuLoader::$system['db']的键.
 *                     为null时返回默认的配置组,即WoniuLoader::$system['db']['active_group']指定的组。
 * @return null
 */
if (!function_exists('dbInfo')) {

    function dbInfo($group = NULL) {
        if (is_null($group)) {
            return WoniuLoader::$system['db'][WoniuLoader::$system['db']['active_group']];
        } elseif (isset(WoniuLoader::$system['db'][$group])) {
            return WoniuLoader::$system['db'][$group];
        } else {
            return null;
        }
    }

}

if (!function_exists('sessionStart')) {

    function sessionStart() {
        if (!isset($_SESSION)) {
            session_start();
        }
    }

}

if (!function_exists('getInstance')) {

    function &getInstance() {
        return WoniuController::getInstance();
    }

}

if (!function_exists('trigger404')) {

    function trigger404($msg = '<h1>Not Found</h1>') {
        $system = WoniuLoader::$system;
        if (!headers_sent()) {
            header('HTTP/1.1 404 NotFound');
        }
        if (!empty($system['error_page_404']) && file_exists($system['error_page_404'])) {
            include $system['error_page_404'];
        } else {
            echo $msg;
        }
        exit();
    }

}

if (!function_exists('truepath')) {

    /**
     * This function is to replace PHP's extremely buggy realpath().
     * @param string The original path, can be relative etc.
     * @return string The resolved path, it might not exist.
     */
    function truepath($path) {
        //是linux系统么？
        $unipath = PATH_SEPARATOR == ':';
        //检测一下是否是相对路径，windows下面没有:,linux下面没有/开头
        //如果是相对路径就加上当前工作目录前缀
        if (strpos($path, ':') === false && strlen($path) && $path{0} != '/') {
            $path = realpath('.') . DIRECTORY_SEPARATOR . $path;
        }
        // resolve path parts (single dot, double dot and double delimiters)
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part)
                continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        //如果是linux这里会导致linux开头的/丢失
        $path = implode(DIRECTORY_SEPARATOR, $absolutes);
        //如果是linux，修复系统前缀
        $path = $unipath ? (strlen($path) && $path{0} != '/' ? '/' . $path : $path) : $path;
        //最后统一分隔符为/，windows兼容/
        $path = str_replace(array('/', '\\'), '/', $path);
        return $path;
    }

}
if (!function_exists('convertPath')) {

    function convertPath($path) {
        return str_replace(array("\\", "/"), '/', $path);
    }

}
if (!function_exists('trigger500')) {

    function trigger500($msg = '<h1>Server Error</h1>') {
        $system = WoniuLoader::$system;
        if (!headers_sent()) {
            header('HTTP/1.1 500 Server Error');
        }
        if (!empty($system['error_page_50x']) && file_exists($system['error_page_50x'])) {
            include $system['error_page_50x'];
        } else {
            echo $msg;
        }
        exit();
    }

}
if (!function_exists('woniu_exception_handler')) {

    function woniu_exception_handler($exception) {
        $errno = $exception->getCode();
        $errfile = pathinfo($exception->getFile(), PATHINFO_FILENAME);
        $errline = $exception->getLine();
        $errstr = $exception->getMessage();
        $system = WoniuLoader::$system;
        if ($system['log_error']) {
            $handle = $system['log_error_handle']['exception'];
            if (!empty($handle)) {
                if (is_array($handle)) {
                    $class = key($handle);
                    $method = $handle[$class];
                    $rclass_obj = new ReflectionClass($class);
                    $rclass_obj = $rclass_obj->newInstanceArgs();
                    if (method_exists($rclass_obj, $method)) {
                        $rclass_obj->{$method}($errno, $errstr, $errfile, $errline, get_strace());
                    }
                } else {
                    if (function_exists($handle)) {
                        $handle($errno, $errstr, $errfile, $errline, get_strace());
                    }
                }
            }
        }
        if ($system['debug']) {
            //@ob_clean();
            echo '<pre>' . format_error($errno, $errstr, $errfile, $errline) . '</pre>';
        }
        exit;
    }

}
if (!function_exists('woniu_error_handler')) {

    /**
     * 非致命错误处理函数。
     * 该函数会接受所有类型的错误，应该过滤掉致命错误
     * @param type $errno
     * @param type $errstr
     * @param type $errfile
     * @param type $errline
     * @return type
     */
    function woniu_error_handler($errno, $errstr, $errfile, $errline) {
        if (!error_reporting()) {
            return;
        }
        $fatal_err = array(E_ERROR, E_USER_ERROR, E_COMPILE_ERROR, E_CORE_ERROR, E_PARSE, E_RECOVERABLE_ERROR);
        if (in_array($errno, $fatal_err)) {
            return true;
        }
        $system = WoniuLoader::$system;
        if ($system['log_error']) {
            $handle = $system['log_error_handle']['error'];
            if (!empty($handle)) {
                if (is_array($handle)) {
                    $class = key($handle);
                    $method = $handle[$class];
                    $rclass_obj = new ReflectionClass($class);
                    $rclass_obj = $rclass_obj->newInstanceArgs();
                    if (method_exists($rclass_obj, $method)) {
                        $rclass_obj->{$method}($errno, $errstr, $errfile, $errline, get_strace());
                    }
                } else {
                    if (function_exists($handle)) {
                        $handle($errno, $errstr, $errfile, $errline, get_strace());
                    }
                }
            }
        }
        if ($system['debug']) {
            //@ob_clean();
            echo '<pre>' . format_error($errno, $errstr, $errfile, $errline) . '</pre>';
        }
    }

}
if (!function_exists('woniu_fatal_handler')) {

    /**
     * 致命错误处理函数。
     * 该函数会接受所有类型的错误，应该只处理致命错误
     * @param type $errno
     * @param type $errstr
     * @param type $errfile
     * @param type $errline
     * @return type
     */
    function woniu_fatal_handler() {
        $system = WoniuLoader::$system;
        $errfile = "unknown file";
        $errstr = "shutdown";
        $errno = E_CORE_ERROR;
        $errline = 0;
        $error = error_get_last();
        $fatal_err = array(E_ERROR, E_USER_ERROR, E_COMPILE_ERROR, E_CORE_ERROR, E_PARSE, E_RECOVERABLE_ERROR);
        if ($error !== NULL && isset($error["type"]) && in_array($error["type"], $fatal_err)) {
            $errno = $error["type"];
            $errfile = $error["file"];
            $errline = $error["line"];
            $errstr = $error["message"];
            if ($system['log_error']) {
                $handle = $system['log_error_handle']['error'];
                if (!empty($handle)) {
                    if (is_array($handle)) {
                        $class = key($handle);
                        $method = $handle[$class];
                        $rclass_obj = new ReflectionClass($class);
                        $rclass_obj = $rclass_obj->newInstanceArgs();
                        if (method_exists($rclass_obj, $method)) {
                            $rclass_obj->{$method}($errno, $errstr, $errfile, $errline, get_strace());
                        }
                    } else {
                        if (function_exists($handle)) {
                            $handle($errno, $errstr, $errfile, $errline, get_strace());
                        }
                    }
                }
            }
            if ($system['debug']) {
                //@ob_clean();
                echo '<pre>' . format_error($errno, $errstr, $errfile, $errline) . '</pre>';
            }
            exit;
        }
    }

}


if (!function_exists('woniu_db_error_handler')) {

    function woniu_db_error_handler($error) {
        $msg = '';
        if (is_array($error)) {
            foreach ($error as $m) {
                $msg.=$m . "\n";
            }
        } else {
            $msg = $error;
        }
        $system = WoniuLoader::$system;
        $woniu_db = WoniuLoader::$system['db'];
        if ($system['log_error']) {
            $handle = $system['log_error_handle']['db_error'];
            if (!empty($handle)) {
                if (is_array($handle)) {
                    $class = key($handle);
                    $method = $handle[$class];
                    $rclass_obj = new ReflectionClass($class);
                    $rclass_obj = $rclass_obj->newInstanceArgs();
                    if (method_exists($rclass_obj, $method)) {
                        $rclass_obj->{$method}($msg, get_strace(TRUE));
                    }
                } else {
                    if (function_exists($handle)) {
                        $handle($msg, get_strace(TRUE));
                    }
                }
            }
        }
        if ($woniu_db[$woniu_db['active_group']]['db_debug'] && $system['debug']) {
            if (!empty($system['error_page_db']) && file_exists($system['error_page_db'])) {
                include $system['error_page_db'];
            } else {
                echo '<pre>' . $msg . get_strace(TRUE) . '</pre>';
            }
            exit;
        }
    }

}

if (!function_exists('format_error')) {

    function format_error($errno, $errstr, $errfile, $errline) {
        $path = truepath(WoniuLoader::$system['application_folder']);
        $path.=empty($path) ? '' : '/';
        $array_map = array('0' => 'EXCEPTION', '1' => 'ERROR', '2' => 'WARNING', '4' => 'PARSE', '8' => 'NOTICE', '16' => 'CORE_ERROR', '32' => 'CORE_WARNING', '64' => 'COMPILE_ERROR', '128' => 'COMPILE_WARNING', '256' => 'USER_ERROR', '512' => 'USER_WARNING', '1024' => 'USER_NOTICE', '2048' => 'STRICT', '4096' => 'RECOVERABLE_ERROR', '8192' => 'DEPRECATED', '16384' => 'USER_DEPRECATED');
        $trace = get_strace();
        $content = '';
        $content .= "错误信息:" . nl2br($errstr) . "\n";
        $content .= "出错文件:" . str_replace($path, '', $errfile) . "\n";
        $content .= "出错行数:{$errline}\n";
        $content .= "错误代码:{$errno}\n";
        $content .= "错误类型:{$array_map[$errno]}\n";
        if (!empty($trace)) {
            $content .= "调用信息:{$trace}\n";
        }
        return $content;
    }

}

if (!function_exists('get_strace')) {

    function get_strace($is_db = false) {
        $trace = debug_backtrace(false);
        foreach ($trace as $t) {
            if (!in_array($t['function'], array('display_error', 'woniu_db_error_handler', 'woniu_fatal_handler', 'woniu_error_handler', 'woniu_exception_handler'))) {
                array_shift($trace);
            } else {
                array_shift($trace);
                break;
            }
        }
        if ($is_db) {
            array_shift($trace);
        }
        array_pop($trace);
        array_pop($trace);
        $str = '';
        $path = truepath(WoniuLoader::$system['application_folder']);
        $path.=empty($path) ? '' : '/';
        foreach ($trace as $k => $e) {
            $file = !empty($e['file']) ? "File:" . str_replace($path, '', $e['file']) . "\n" : '';
            $line = !empty($e['line']) ? "   Line:{$e['line']}\n" : '';
            $space = (empty($file) && empty($line) ? '' : '   ');
            $func = $space . (!empty($e['class']) ? "Function:{$e['class']}{$e['type']}{$e['function']}()\n" : "Function:{$e['function']}()\n");
            $str.="\n#{$k} {$file}{$line}{$func}";
        }
        return $str;
    }

}
if (!function_exists('stripslashes_all')) {

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

}
if (!function_exists('stripslashes2')) {
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

}
if (!function_exists('is_php')) {

    function is_php($version = '5.0.0') {
        static $_is_php;
        $version = (string) $version;

        if (!isset($_is_php[$version])) {
            $_is_php[$version] = (version_compare(PHP_VERSION, $version) < 0) ? FALSE : TRUE;
        }

        return $_is_php[$version];
    }

}
if (!function_exists('forceDownload')) {

    /**
     * 强制下载
     * 经过修改，支持中文名称
     * Generates headers that force a download to happen
     *
     * @access    public
     * @param    string    filename
     * @param    mixed    the data to be downloaded
     * @return    void
     */
    function forceDownload($filename = '', $data = '') {
        if ($filename == '' OR $data == '') {
            return FALSE;
        }
        # Try to determine if the filename includes a file extension.
        # We need it in order to set the MIME type
        if (FALSE === strpos($filename, '.')) {
            return FALSE;
        }
        # Grab the file extension
        $x = explode('.', $filename);
        $extension = end($x);
        # Load the mime types
        $mimes = array('hqx' => 'application/mac-binhex40', 'cpt' => 'application/mac-compactpro', 'csv' => array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel'), 'bin' => 'application/macbinary', 'dms' => 'application/octet-stream', 'lha' => 'application/octet-stream', 'lzh' => 'application/octet-stream', 'exe' => array('application/octet-stream', 'application/x-msdownload'), 'class' => 'application/octet-stream', 'psd' => 'application/x-photoshop', 'so' => 'application/octet-stream', 'sea' => 'application/octet-stream', 'dll' => 'application/octet-stream', 'oda' => 'application/oda', 'pdf' => array('application/pdf', 'application/x-download'), 'ai' => 'application/postscript', 'eps' => 'application/postscript', 'ps' => 'application/postscript', 'smi' => 'application/smil', 'smil' => 'application/smil', 'mif' => 'application/vnd.mif', 'xls' => array('application/excel', 'application/vnd.ms-excel', 'application/msexcel'), 'ppt' => array('application/powerpoint', 'application/vnd.ms-powerpoint'), 'wbxml' => 'application/wbxml', 'wmlc' => 'application/wmlc', 'dcr' => 'application/x-director', 'dir' => 'application/x-director', 'dxr' => 'application/x-director', 'dvi' => 'application/x-dvi', 'gtar' => 'application/x-gtar', 'gz' => 'application/x-gzip', 'php' => 'application/x-httpd-php', 'php4' => 'application/x-httpd-php', 'php3' => 'application/x-httpd-php', 'phtml' => 'application/x-httpd-php', 'phps' => 'application/x-httpd-php-source', 'js' => 'application/x-javascript', 'swf' => 'application/x-shockwave-flash', 'sit' => 'application/x-stuffit', 'tar' => 'application/x-tar', 'tgz' => array('application/x-tar', 'application/x-gzip-compressed'), 'xhtml' => 'application/xhtml+xml', 'xht' => 'application/xhtml+xml', 'zip' => array('application/x-zip', 'application/zip', 'application/x-zip-compressed'), 'mid' => 'audio/midi', 'midi' => 'audio/midi', 'mpga' => 'audio/mpeg', 'mp2' => 'audio/mpeg', 'mp3' => array('audio/mpeg', 'audio/mpg', 'audio/mpeg3', 'audio/mp3'), 'aif' => 'audio/x-aiff', 'aiff' => 'audio/x-aiff', 'aifc' => 'audio/x-aiff', 'ram' => 'audio/x-pn-realaudio', 'rm' => 'audio/x-pn-realaudio', 'rpm' => 'audio/x-pn-realaudio-plugin', 'ra' => 'audio/x-realaudio', 'rv' => 'video/vnd.rn-realvideo', 'wav' => 'audio/x-wav', 'bmp' => 'image/bmp', 'gif' => 'image/gif', 'jpeg' => array('image/jpeg', 'image/pjpeg'), 'jpg' => array('image/jpeg', 'image/pjpeg'), 'jpe' => array('image/jpeg', 'image/pjpeg'), 'png' => array('image/png', 'image/x-png'), 'tiff' => 'image/tiff', 'tif' => 'image/tiff', 'css' => 'text/css', 'html' => 'text/html', 'htm' => 'text/html', 'shtml' => 'text/html', 'txt' => 'text/plain', 'text' => 'text/plain', 'log' => array('text/plain', 'text/x-log'), 'rtx' => 'text/richtext', 'rtf' => 'text/rtf', 'xml' => 'text/xml', 'xsl' => 'text/xml', 'mpeg' => 'video/mpeg', 'mpg' => 'video/mpeg', 'mpe' => 'video/mpeg', 'qt' => 'video/quicktime', 'mov' => 'video/quicktime', 'avi' => 'video/x-msvideo', 'movie' => 'video/x-sgi-movie', 'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'word' => array('application/msword', 'application/octet-stream'), 'xl' => 'application/excel', 'eml' => 'message/rfc822', 'json' => array('application/json', 'text/json'));
        # Set a default mime if we can't find it
        if (!isset($mimes[$extension])) {
            $mime = 'application/octet-stream';
        } else {
            $mime = (is_array($mimes[$extension])) ? $mimes[$extension][0] : $mimes[$extension];
        }
        header('Content-Type: "' . $mime . '"');
        $tmpName = $filename;
        $filename = '"' . urlencode($tmpName) . '"'; #ie中文文件名支持
        if (strstr(strtolower($_SERVER['HTTP_USER_AGENT']), 'firefox') != false) {
            $filename = '"' . $tmpName . '"';
        }#firefox中文文件名支持
        if (strstr(strtolower($_SERVER['HTTP_USER_AGENT']), 'chrome') != false) {
            $filename = urlencode($tmpName);
        }#Chrome中文文件名支持
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header("Content-Transfer-Encoding: binary");
        header('Pragma: no-cache');
        header("Content-Length: " . strlen($data));
        exit($data);
    }

}
if (!function_exists('getRsCol')) {

    /**
     * 获取结果集中的一个字段的数组
     * @param type $rows
     * @param type $col_name
     * @return array
     */
    function getRsCol($rows, $col_name) {
        $ret = array();
        foreach ($rows as &$row) {
            $ret[] = $row[$col_name];
        }
        return $ret;
    }

}
if (!function_exists('chRsKey')) {

    /**
     * 改变结果集数组key
     * @param type $rs  结果集
     * @param type $col 作为结果集key的字段名称
     * @return type
     */
    function chRsKey($rs, $col) {
        $_rs = array();
        foreach ($rs as $v) {
            $_rs[$v[$col]] = $v;
        }
        return $_rs;
    }

}
if (!function_exists('sortRs')) {

    /**
     * 按字段对结果集进行排序
     * @param type $rows
     * @param type $key
     * @param type $order
     * @return array
     */
    function sortRs($rows, $key, $order = 'asc') {
        $sort = array();
        foreach ($rows as $k => $value) {
            $sort[$k] = $value[$key];
        }
        $order == 'asc' ? asort($sort) : arsort($sort);
        $ret = array();
        foreach ($sort as $k => $value) {
            $ret[] = $rows[$k];
        }
        return $ret;
    }

}

if (!function_exists('mergeRs')) {

    /**
     * 合并多个结果集，参数是多个：array($rs,$column_name)，$column_name是该结果集和其它结果集关联的字段
     * 比如：$rs1=array(array('a'=>'1111','b'=>'fasdfas'),array('a'=>'222','b'=>'fasdfas'),array('a'=>'333','b'=>'fasdfas'));
      $rs2=array(array('c'=>'1111','r'=>'fasd22fas'),array('c'=>'222','r'=>'fasd22fas'),array('c'=>'333','r'=>'fasdf22as'));
      $rs3=array(array('a'=>'1111','e'=>'fasd33fas'),array('a'=>'222','e'=>'fas33dfas'),array('a'=>'333','e'=>'fas33dfas'));
      var_dump(mergeRs(array($rs1,'a'),array($rs2,'c'),array($rs3,'a')));
     * 上面的例子中三个结果集中的关联字段是$rs1.a=$rs2.c=$rs3.a
     * @return array
     */
    function mergeRs() {
        $argv = func_get_args();
        $argc = count($argv);
        $ret = array();
        foreach ($argv[0][0] as $v) {
            $r = $v;
            for ($j = 1; $j < $argc; $j++) {
                foreach ($argv[$j][0] as $row) {
                    if ($v[$argv[0][1]] == $row[$argv[$j][1]]) {
                        $r = array_merge($r, $row);
                        break;
                    }
                }
            }
            $ret[] = $r;
        }
        $allkeys = array();
        foreach ($argv as $rs) {
            foreach (array_keys($rs[0][0]) as $key) {
                $allkeys[] = $key;
            }
        }
        foreach ($ret as &$row) {
            foreach ($allkeys as $key) {
                if (!isset($row[$key])) {
                    $row[$key] = null;
                }
            }
        }
        return $ret;
    }

}

if (!function_exists('enableSelectDefault')) {

    function enableSelectDefault($return = false) {
        $js = '<script>var func0797986876;if(typeof(window.onload)=="function"){func0797986876=window.onload};window.onload=function(){func0797986876?func0797986876():null;var b=document.getElementsByTagName("select");var c=function(){for(var k=0;k<b.length;k++){var s=b[k];var a=s.attributes["default"]?s.attributes["default"].value:null;if(a){for(var i=0;i<s.length;i++){if(s[i].value==a){s[i].selected=true;break;}}}}};c();window["select_default"]=c;};</script>';
        if ($return) {
            return $js;
        } else {
            echo $js;
        }
    }

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
 * @package                MicroPHP
 * @author                狂奔的蜗牛
 * @email                672308444@163.com
 * @copyright          Copyright (c) 2013 - 2014, 狂奔的蜗牛, Inc.
 * @link                http://git.oschina.net/snail/microphp
 * @since                Version 2.2.7
 * @createdtime       2014-05-13 11:39:58
 */
class WoniuInput {

    public static $router;

    /**
     * hmvc模块名称，没有模块就为空
     * @return type
     */
    public static function module_name() {
        return self::$router['module'];
    }

    /**
     * url中方法的路径<br/>
     * 比如：<br>
     * 1.home.index<br>
     * 2.user.home.index ，user是文件夹<br>
     * @return type
     */
    public static function method_path() {
        return self::$router['mpath'];
    }

    /**
     * url中方法名称<br/>
     * 比如：<br>
     * 1.index<br>
     * @return type
     */
    public static function method_name() {
        return self::$router['m'];
    }

    /**
     * $system配置中方法前缀,比如：do
     * @return type
     */
    public static function method_prefix() {
        return self::$router['prefix'];
    }

    /**
     * url中控制器的路径<br/>
     * 比如：<br>
     * 1.home<br>
     * 2.user.home ，user是文件夹<br>
     * @return type
     */
    public static function controller_path() {
        return self::$router['cpath'];
    }

    /**
     * url中控制器名称<br/>
     * 比如：<br>
     * 1.home<br>
     * @return type
     */
    public static function controller_name() {
        return self::$router['c'];
    }

    /**
     * url中文件夹名称，没有文件夹返回空<br/>
     * 比如：<br/>
     * 1.user
     */
    public static function folder_name() {
        return self::$router['folder'];
    }

    /**
     * 请求的控制器文件绝对路径<br/>
     * 比如：/home/www/app/controllers/home.php<br/>
     * 
     */
    public static function controller_file() {
        return self::$router['file'];
    }

    /**
     * 请求的控制器类名称<br/>
     * 比如：Home
     */
    public static function class_name() {
        return self::$router['class'];
    }

    /**
     * 请求的控制器方法名称<br/>
     * 比如：doIndex
     */
    public static function class_method_name() {
        return self::$router['method'];
    }

    /**
     * 传递给控制器方法的所有参数的数组，参数为空时返回空数组<br/>
     * 比如：<br/>
     * 1.home.index/username/1234，那么返回的参数数组就是：array('username','1234')<br/>
     * 2.如果传递了$key,比如$key是1， 那么将返回1234。如果$key是2那么将返回null。
     * @param type $key 参数的索引从0开始，如果传递了索引那么将返回索引对应的参数,不存在的索引将返回null
     * @return null
     */
    public static function parameters($key = null) {
        if (!is_null($key)) {
            if (isset(self::$router['parameters'][$key])) {
                return self::$router['parameters'][$key];
            } else {
                return null;
            }
        } else {
            return self::$router['parameters'];
        }
    }

    public static function get_post($key = null, $default = null, $xss_clean = false) {
        $get = self::gpcs('_GET', $key, $default);
        $val = $get === null ? self::gpcs('_POST', $key, $default) : $get;
        return $xss_clean ? self::xss_clean($val) : $val;
    }

    public static function post_get($key = null, $default = null, $xss_clean = false) {
        $get = self::gpcs('_POST', $key, $default);
        $val = $get === null ? self::gpcs('_GET', $key, $default) : $get;
        return $xss_clean ? self::xss_clean($val) : $val;
    }

    public static function get($key = null, $default = null, $xss_clean = false) {
        $val = self::gpcs('_GET', $key, $default);
        return $xss_clean ? self::xss_clean($val) : $val;
    }

    public static function post($key = null, $default = null, $xss_clean = false) {
        $val = self::gpcs('_POST', $key, $default);
        return $xss_clean ? self::xss_clean($val) : $val;
    }

    public static function cookie($key = null, $default = null, $xss_clean = false) {
        $val = self::gpcs('_COOKIE', $key, $default);
        return $xss_clean ? self::xss_clean($val) : $val;
    }

    public static function session($key = null, $default = null) {
        return self::gpcs('_SESSION', $key, $default);
    }

    public static function server($key = null, $default = null) {
        $key = !is_null($key) ? strtoupper($key) : null;
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

    public static function isCli() {
        return php_sapi_name() == 'cli';
    }

    public static function is_ajax() {
        return (self::server('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest');
    }

    public static function xss_clean($val) {
        // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
        // this prevents some character re-spacing such as <java\0script>
        // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
        $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);

        // straight replacements, the user should never need these since they're normal characters
        // this prevents like <IMG SRC=@avascript:alert('XSS')>
        $search = 'abcdefghijklmnopqrstuvwxyz';
        $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $search .= '1234567890!@#$%^&*()';
        $search .= '~`";:?+/={}[]-_|\'\\';
        for ($i = 0; $i < strlen($search); $i++) {
            // ;? matches the ;, which is optional
            // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
            // @ @ search for the hex values
            $val = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val); // with a ;
            // @ @ 0{0,7} matches '0' zero to seven times
            $val = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val); // with a ;
        }

        // now the only remaining whitespace attacks are \t, \n, and \r
        $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
        $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
        $ra = array_merge($ra1, $ra2);

        $found = true; // keep replacing as long as the previous round replaced something
        while ($found == true) {
            $val_before = $val;
            for ($i = 0; $i < sizeof($ra); $i++) {
                $pattern = '/';
                for ($j = 0; $j < strlen($ra[$i]); $j++) {
                    if ($j > 0) {
                        $pattern .= '(';
                        $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                        $pattern .= '|';
                        $pattern .= '|(&#0{0,8}([9|10|13]);)';
                        $pattern .= ')*';
                    }
                    $pattern .= $ra[$i][$j];
                }
                $pattern .= '/i';
                $replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2); // add in <> to nerf the tag
                $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
                if ($val_before == $val) {
                    // no replacements were made, so exit the loop
                    $found = false;
                }
            }
        }
        return $val;
    }

}

/* End of file WoniuInput.php */
//####################modules/WoniuRouter.php####################{


/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package                MicroPHP
 * @author                狂奔的蜗牛
 * @email                672308444@163.com
 * @copyright          Copyright (c) 2013 - 2014, 狂奔的蜗牛, Inc.
 * @link                http://git.oschina.net/snail/microphp
 * @since                Version 2.2.7
 * @createdtime       2014-05-13 11:39:58
 */
class WoniuRouter {

    public static function loadClass() {
        $system = WoniuLoader::$system;
        $methodInfo = self::parseURI();
        //在解析路由之后，就注册自动加载，这样控制器可以继承类库文件夹里面的自定义父控制器,实现hook功能，达到拓展控制器的功能
        //但是plugin模式下，路由器不再使用，那么这里就不会被执行，自动加载功能会失效，所以在每个instance方法里面再尝试加载一次即可，
        //如此一来就能满足两种模式
        WoniuLoader::classAutoloadRegister();
//        var_dump($methodInfo);
        if (file_exists($methodInfo['file'])) {
            include $methodInfo['file'];
            WoniuInput::$router = $methodInfo;
            if (!WoniuInput::isCli()) {
                //session自定义配置检查,只在非命令行模式下启用
                self::checkSession();
            }
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

        $pathinfo_query = self::getQueryStr();

        //路由hmvc模块名称信息检查
        $router['module']=  self::getHmvcModuleName($pathinfo_query);

        $pathinfo_query = self::checkHmvc($pathinfo_query);
        $pathinfo_query = self::checkRouter($pathinfo_query);
        $system = WoniuLoader::$system;
        $class_method = $system['default_controller'] . '.' . $system['default_controller_method'];
        //看看是否要处理查询字符串
        if (!empty($pathinfo_query)) {
            //查询字符串去除头部的/
            $pathinfo_query{0} === '/' ? $pathinfo_query = substr($pathinfo_query, 1) : null;
            $requests = explode("/", $pathinfo_query);
            //看看是否指定了类和方法名
            preg_match('/[^&]+(?:\.[^&]+)+/', $requests[0]) ? $class_method = $requests[0] : null;
            if (strstr($class_method, '&') !== false) {
                $cm = explode('&', $class_method);
                $class_method = $cm[0];
            }
        }
        //去掉查询字符串中的类方法部分，只留下参数
        $pathinfo_query = str_replace($class_method, '', $pathinfo_query);
        $pathinfo_query_parameters = explode("&", $pathinfo_query);
        $pathinfo_query_parameters_str = !empty($pathinfo_query_parameters[0]) ? $pathinfo_query_parameters[0] : '';
        //去掉参数开头的/，只留下参数
        $pathinfo_query_parameters_str && $pathinfo_query_parameters_str{0} === '/' ? $pathinfo_query_parameters_str = substr($pathinfo_query_parameters_str, 1) : '';

        //现在已经解析出了，$class_method类方法名称字符串(main.index），$pathinfo_query_parameters_str参数字符串(1/2)，进一步解析为真实路径
        $origin_class_method = $class_method;
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
        if (count($parameters) === 1 && (empty($parameters[0]) || strpos($parameters[0], '=') !== false)) {
            $parameters = array();
        }
        $info = array('file' => $file, 'class' => ucfirst($class), 'method' => str_replace('.', '/', $method), 'parameters' => $parameters);
        #开始准备router信息
        $path = explode('.', $origin_class_method);
        $router['mpath'] = $origin_class_method;
        $router['m'] = $path[count($path) - 1];
        $router['c'] = '';
        if (count($path) > 1) {
            $router['c'] = $path[count($path) - 2];
        }
        $router['prefix'] = $system['controller_method_prefix'];
        unset($path[count($path) - 1]);
        $router['cpath'] = empty($path) ? '' : implode('.', $path);
        $router['folder'] = '';
        if (count($path) > 1) {
            unset($path[count($path) - 1]);
            $router['folder'] = implode('.', $path);
        }

        return $router + $info;
    }

    private static function getQueryStr() {
        $system = WoniuLoader::$system;
        //命令行运行检查
        if (WoniuInput::isCli()) {
            global $argv;
            $pathinfo_query = isset($argv[1]) ? $argv[1] : '';
        } else {
            $pathinfo = @parse_url($_SERVER['REQUEST_URI']);
            if (empty($pathinfo)) {
                if ($system['debug']) {
                    trigger404('request parse error:' . $_SERVER['REQUEST_URI']);
                } else {
                    trigger404();
                }
            }
            //pathinfo模式下有?,那么$pathinfo['query']也是非空的，这个时候查询字符串是PATH_INFO和query
            $query_str = empty($pathinfo['query']) ? '' : $pathinfo['query'];
            $pathinfo_query = empty($_SERVER['PATH_INFO']) ? $query_str : $_SERVER['PATH_INFO'] . '&' . $query_str;
        }
        if ($pathinfo_query) {
            $pathinfo_query = trim($pathinfo_query, '/&');
        }
        return $pathinfo_query;
    }

    private static function checkSession() {
        $system = WoniuLoader::$system;
        //session自定义配置检测
        if (!empty($system['session_handle']['handle']) && isset($system['session_handle'][$system['session_handle']['handle']])
        ) {
            $driver = $system['session_handle']['handle'];
            $config = $system['session_handle'];
            $handle = ucfirst($driver) . 'SessionHandle';
            $session = new $handle();
            $session->start($config);
        }
    }

    private static function checkRouter($pathinfo_query) {
        $system = WoniuLoader::$system;
        if (is_array($system['route'])) {
            foreach ($system['route'] as $reg => $replace) {
                if (preg_match($reg, $pathinfo_query)) {
                    $pathinfo_query = preg_replace($reg, $replace, $pathinfo_query);
                    break;
                }
            }
        }
        return $pathinfo_query;
    }

    private static function checkHmvc($pathinfo_query) {
        if ($_module = self::getHmvcModuleName($pathinfo_query)) {
            $_system = WoniuLoader::$system;
            self::switchHmvcConfig($_system['hmvc_modules'][$_module]);
            return preg_replace('|^' . $_module . '[\./&]?|', '', $pathinfo_query);
        }
        return $pathinfo_query;
    }

    private static function getHmvcModuleName($pathinfo_query) {
        $_module = current(explode('&', $pathinfo_query));
        $_module = current(explode('/', $_module));
        $_system = WoniuLoader::$system;
        if (isset($_system['hmvc_modules'][$_module])) {
            return $_module;
        } else {
            return '';
        }
    }

    public static function switchHmvcConfig($hmvc_folder) {
        $_system = $system = WoniuLoader::$system;
        $module = $_system['hmvc_folder'] . '/' . $hmvc_folder . '/hmvc.php';
        //$system被hmvc模块配置重写
        include($module);
        //共享主配置：模型，视图，类库，helper,同时保留自动加载的东西
        foreach (array('model_folder', 'view_folder', 'library_folder', 'helper_folder', 'helper_file_autoload', 'library_file_autoload', 'models_file_autoload') as $folder) {
            if (!is_array($_system[$folder])) {
                $_system[$folder] = array($_system[$folder]);
            }
            if (!is_array($system[$folder])) {
                $system[$folder] = array($system[$folder]);
            }
            $system[$folder] = array_merge($system[$folder], $_system[$folder]);
        }
        //切换核心配置
        WoniuLoader::$system = $system;
    }

    public static function setConfig($system) {
        $system['application_folder'] = truepath($system['application_folder']);
        WoniuLoader::$system = $system;
        self::folderAutoInit();
    }

    private static function folderAutoInit() {
        if (!empty(WoniuLoader::$system['folder_auto_init'])) {
            $folder = array('application_folder', 'controller_folder', 'model_folder', 'view_folder', 'library_folder', 'helper_folder', 'hmvc_folder');
            foreach (WoniuLoader::$system as $key => $value) {
                if (in_array($key, $folder)) {
                    if (!is_dir($value)) {
                        mkdir($value, 0755, true);
                        chmod($value, 0755);
                    }
                }
            }
        }
    }

}

/* End of file Router.php */
//####################modules/WoniuLoader.php####################{


/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package                MicroPHP
 * @author                 狂奔的蜗牛
 * @email                  672308444@163.com
 * @copyright              Copyright (c) 2013 - 2014, 狂奔的蜗牛, Inc.
 * @link                   http://git.oschina.net/snail/microphp
 * @since                  Version 2.2.7
 * @createdtime            2014-05-13 11:39:58
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
        $this->rule = new WoniuRule();

        phpFastCache::setup($system['cache_config']);
        $this->cache = phpFastCache($system['cache_config']['storage']);
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

    public function config($config_group, $key = null) {
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

    public function helper($file_name) {
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
                //包含文件，并把文件里面的变量放入$this->config
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

    public function lib($file_name, $alias_name = null) {
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

    public function model($file_name, $alias_name = null) {
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
    public function view_path($view_name, $path_key = 0) {

        $system = WoniuLoader::$system;
        if (!is_array($system['view_folder'])) {
            $system['view_folder'] = array($system['view_folder']);
        }
        if (!isset($system['view_folder'][$path_key])) {
            trigger404('error key[' . $path_key . '] of $system["view_folder"]');
        }
        $dir = $system['view_folder'][$path_key];
        $view_path = $dir . DIRECTORY_SEPARATOR . $view_name . $system['view_file_subfix'];
        return $view_path;
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
        exit();
    }

    public function message($msg, $url = null, $time = 3, $view = null) {
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

    public function setCookie($key, $value, $life = null, $path = '/', $domian = null) {
        header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
        if (!is_null($domian)) {
            $auto_domain = $domian;
        } else {
            $host = $this->input->server('HTTP_HOST');
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
        setcookie($key, $value, ($life ? $life + time() : null), $path, $auto_domain, ($this->input->server('SERVER_PORT') == 443 ? 1 : 0));
        $_COOKIE[$key] = $value;
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
    public function page($total, $page, $pagesize, $url, $order = array(1, 2, 3, 4, 5, 6), $a_count = 10) {
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

    public function checkData(Array $rule, Array $data = NULL, &$return_data = NULL) {
        if (is_null($data)) {
            $data = $this->input->post();
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
                    $matches = $this->getCheckRuleInfo($_rule);
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
        $this->checkSetData('set', $rule, $return_data);
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
                        $matches = $this->getCheckRuleInfo($_rule);
                        $_r = $matches[1];
                        $args = $matches[2];
                        if ($_r == 'set' || $_r == 'set_post' || $_r == 'optional') {
                            continue;
                        }
                        if (!$this->checkRule($_rule, $return_data[$col], $return_data)) {
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
        $this->checkSetData('set_post', $rule, $return_data);

        /**
         * 清理没有传递的key
         */
        foreach ($unset_keys as $key) {
            unset($return_data[$key]);
        }
        return NULL;
    }

    private function checkSetData($type, Array $rule, &$return_data = NULL) {
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
                    $matches = $this->getCheckRuleInfo($_rule);
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
                            $_v = $this->callFunc($func, $_args);
                        }
                        $return_data[$col] = $_v;
                    }
                }
            }
        }
    }

    private function getCheckRuleInfo($_rule) {
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
    public function callFunc($func, $args) {
        if (is_array($func)) {
            return $this->callMethod($func, $args);
        } elseif (function_exists($func)) {
            return call_user_func_array($func, $args);
        } elseif (stripos($func, '::')) {
            $_func = explode('::', $func);
            return $this->callMethod($_func, $args);
        }
        return null;
    }

    private function callMethod($_func, $args) {
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

    private function checkRule($_rule, $val, $data) {
        $matches = $this->getCheckRuleInfo($_rule);
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
                    $id = stripos($id, '#') === 0 ? $this->input->get_post(substr($id, 1)) : $id;
                    $where = array($col => $val, "$id_col <>" => $id);
                } else {
                    $where = array($col => $val);
                }
                return !$this->db->where($where)->from($table)->count_all_results();
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
                $matches = $this->getCheckRuleInfo($_rule);
                $func = $matches[1];
                $args = $matches[2];
                if (function_exists($func)) {
                    $reflection = new ReflectionFunction($func);
                    //如果是系统函数
                    if ($reflection->isInternal()) {
                        $_args = isset($_args[0]) ? array($_args[0]) : array();
                    }
                }
                return $this->callFunc($_rule, $_args);
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

/**
 * 表单规则助手类，再不用记忆规则名称
 */
class WoniuRule {

    /**
     * 规则说明：<br/>
     * 如果元素为空，则返回FALSE<br/><br/><br/>
     */
    public static function required() {
        return 'required';
    }

    /**
     * 规则说明：<br/>
     * 当没有post对应字段的值或者值为空的时候那么就会使用默认规则的值作为该字段的值。<br/>
     * 然后用这个值继续 后面的规则进行验证。<br/>
     * @param string $val 默认值<br/><br/><br/>
     */
    public static function defaultVal($val = '') {
        return 'default[' . $val . ']';
    }

    /**
     * 规则说明：<br/>
     * 可以为空规则。例如user字段规则中有optional,当没有传递字段user的值或者值是空的时候，<br/> 
     * user验证会通过(忽略其它规则即使有required规则)， <br/>
     * 提示： <br/>
     * $this->checkData($rule, $_POST, $ret_data)返回的数据$ret_data， <br/>
     * 如果传递了user字段$ret_data就有user字段，反之没有user字段. <br/>
     * 如果user传递有值，那么就会用这个值继续后面的规则进行验证。<br/><br/><br/>
     */
    public static function optional() {
        return 'optional';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素的值与参数中对应的表单字段的值不相等，则返回FALSE<br/>
     * @param string $field_name 表单字段名称<br/><br/><br/>
     */
    public static function match($field_name) {
        return 'match[' . $field_name . ']';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素的值不与指定的值相等，则返回FALSE<br/>
     * @param string $val 指定的值<br/><br/><br/>
     */
    public static function equal($val) {
        return 'equal[' . $val . ']';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值不在指定的几个值中，则返回FALSE<br/>
     * @param string $val 规则内容,多个值用逗号分割，或者用第个参数指定的分割符<br/>
     * @param string $delimiter 规则内容的分割符，比如：# ，默认为空即可<br/><br/><br/>
     */
    public static function enum($val, $delimiter = '') {
        return 'enum[' . $val . ']' . $delimiter;
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素的值与指定数据表栏位有重复，则返回False<br/>
     * 比如unique[user.email]，那么验证类会去查找user表中email字段有没有与表单元素一样的值，<br/>
     * 如存重复，则返回false，这样开发者就不必另写callback验证代码。<br/>
     * 如果指定了id:1,那么除了id为1之外的记录的email字段不能与表单元素一样，<br/>
     * 如果一样返回false<br/>
     * @param string $val 规则内容，比如：1、table.field 2、table.field,id:1<br/>
     * @param string $delimiter 规则内容的分割符，比如：# ，默认为空即可<br/><br/><br/>
     */
    public static function unique($val, $delimiter = '') {
        return 'unique[' . $val . ']' . $delimiter;
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值的字符长度小于参数定义的值，则返回FALSE<br/>
     * @param int $val 长度数值<br/><br/><br/>
     */
    public static function min_len($val) {
        return 'min_len[' . $val . ']';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值的字符长度小于参数定义的值，则返回FALSE<br/>
     * @param int $val 长度数值<br/><br/><br/>
     */
    public static function max_len($val) {
        return 'min_len[' . $val . ']';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值的字符长度不在指定的范围，则返回FALSE<br/>
     * @param int $min_len 最小长度数值<br/>
     * @param int $max_len 最大长度数值<br/><br/><br/>
     */
    public static function range_len($min_len, $max_len) {
        return 'range_len[' . $min_len . ',' . $max_len . ']';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值的字符长度不是指定的长度，则返回FALSE<br/>
     * @param int $val 长度数值<br/><br/><br/>
     */
    public static function len($val) {
        return 'len[' . $val . ']';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值不是数字或者小于指定的值，则返回FALSE<br/>
     * @param int $val 数值<br/><br/><br/>
     */
    public static function min($val) {
        return 'min[' . $val . ']';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值不是数字或者大于指定的值，则返回FALSE<br/>
     * @param int $val 数值<br/><br/><br/>
     */
    public static function max($val) {
        return 'max[' . $val . ']';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值不是数字或者大小不在指定的范围内，则返回 FALSE<br/>
     * @param int $min 最小数值<br/>
     * @param int $max 最大数值<br/><br/><br/>
     */
    public static function range($min, $max) {
        return 'range[' . $min . ',' . $max . ']';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素中包含除字母以外的字符，则返回FALSE<br/><br/><br/>
     */
    public static function alpha() {
        return 'alpha';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素中包含除字母和数字以外的字符，则返回FALSE<br/><br/><br/>
     */
    public static function alpha_num() {
        return 'alpha_num';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值中包含除字母/数字/下划线/破折号以外的其他字符，则返回FALSE<br/><br/><br/>
     */
    public static function alpha_dash() {
        return 'alpha_dash';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素中不是字母开头，则返回FALSE<br/><br/><br/>
     */
    public static function alpha_start() {
        return 'alpha_start';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素中不是纯数字，则返回FALSE<br/><br/><br/>
     */
    public static function num() {
        return 'num';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素中不是整数，则返回FALSE<br/><br/><br/>
     */
    public static function int() {
        return 'int';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素中不是小数，则返回FALSE<br/><br/><br/>
     */
    public static function float() {
        return 'float';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素中不是一个数，则返回FALSE<br/><br/><br/>
     */
    public static function numeric() {
        return 'numeric';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值中包含了非自然数的其他数值 （其他数值不包括零），则返回FALSE。<br/><br/><br/>
     * 自然数形如：0,1,2,3....等等。
     */
    public static function natural() {
        return 'natural';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值包含了非自然数的其他数值 （其他数值包括零），则返回FALSE。<br/><br/><br/>
     * 非零的自然数：1,2,3.....等等。
     */
    public static function natural_no_zero() {
        return 'natural_no_zero';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值不是一个网址，则返回FALSE<br/>
     * @param boolean $can_empty 是否允许为空。true:允许 false:不允许。默认：false<br/><br/><br/>
     */
    public static function url($can_empty = false) {
        return self::can_empty_rule('qq', $can_empty);
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值包含不合法的email地址，则返回FALSE<br/>
     * @param boolean $can_empty 是否允许为空。true:允许 false:不允许。默认：false<br/><br/><br/>
     */
    public static function email($can_empty = false) {
        return self::can_empty_rule('email', $can_empty);
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值不是一个QQ号，则返回FALSE<br/>
     * @param boolean $can_empty 是否允许为空。true:允许 false:不允许。默认：false<br/><br/><br/>
     */
    public static function qq($can_empty = false) {
        return self::can_empty_rule('qq', $can_empty);
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值不是一个电话号码，则返回FALSE<br/>
     * @param boolean $can_empty 是否允许为空。true:允许 false:不允许。默认：false<br/><br/><br/>
     */
    public static function phone($can_empty = false) {
        return self::can_empty_rule('phone', $can_empty);
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值不是一个手机号，则返回FALSE<br/>
     * @param boolean $can_empty 是否允许为空。true:允许 false:不允许。默认：false<br/><br/><br/>
     */
    public static function mobile($can_empty = false) {
        return self::can_empty_rule('mobile', $can_empty);
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值不是一个邮政编码，则返回FALSE<br/>
     * @param boolean $can_empty 是否允许为空。true:允许 false:不允许。默认：false<br/><br/><br/>
     */
    public static function zipcode($can_empty = false) {
        return self::can_empty_rule('zipcode', $can_empty);
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值不是一个身份证号，则返回FALSE<br/>
     * @param boolean $can_empty 是否允许为空。true:允许 false:不允许。默认：false<br/><br/><br/>
     */
    public static function idcard($can_empty = false) {
        return self::can_empty_rule('idcard', $can_empty);
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值不是一个合法的IPv4地址，则返回FALSE。<br/>
     * @param boolean $can_empty 是否允许为空。true:允许 false:不允许。默认：false<br/><br/><br/>
     */
    public static function ip($can_empty = false) {
        return self::can_empty_rule('ip', $can_empty);
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值不是汉字，或者不是指定的长度，则返回FALSE<br/>
     * 规则示例：<br/>
     * 1.规则内容：false    描述：必须是汉字，不能为空<br/>
     * 2.规则内容：true     描述：必须是汉字，可以为空<br/>
     * 3.规则内容：false,2  描述：必须是2个汉字，不能为空<br/>
     * 4.规则内容：true,2   描述：必须是2个汉字，可以为空<br/>
     * 5.规则内容：true,2,3 描述：必须是2-3个汉字，可以为空<br/>
     * 6.规则内容：false,2, 描述：必须是2个以上汉字，不能为空<br/>
     * @param boolean $val 规则内容。默认为空，即规则：必须是汉字不能为空<br/>
     * @param string $delimiter 规则内容的分割符，比如：# ，默认为空即可<br/><br/><br/>
     */
    public static function chs($val = '', $delimiter = '') {
        return 'chs' . ($val ? '[' . $val . ']' . $delimiter : '');
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值不是正确的日期格式YYYY-MM-DD，则返回FALSE<br/>
     * @param boolean $can_empty 是否允许为空。true:允许 false:不允许。默认：false<br/><br/><br/>
     */
    public static function date($can_empty = false) {
        return self::can_empty_rule('date', $can_empty);
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值不是正确的日期时间格式YYYY-MM-DD HH:MM:SS，则返回FALSE<br/>
     * @param boolean $can_empty 是否允许为空。true:允许 false:不允许。默认：false<br/><br/><br/>
     */
    public static function datetime($can_empty = false) {
        return self::can_empty_rule('datetime', $can_empty);
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值不是正确的时间格式HH:MM:SS，则返回FALSE<br/>
     * @param boolean $can_empty 是否允许为空。true:允许 false:不允许。默认：false<br/><br/><br/>
     */
    public static function time($can_empty = false) {
        return self::can_empty_rule('time', $can_empty);
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值不匹配指定的正则表达式，则返回FALSE<br/>
     * @param string $val 正则表达式。比如：1./^[]]$/ 2./^A$/i<br/>
     * 模式修正符说明:<br/>
     * i 表示在和模式进行匹配进不区分大小写<br/>
     * m 将模式视为多行，使用^和$表示任何一行都可以以正则表达式开始或结束<br/>
     * s 如果没有使用这个模式修正符号，元字符中的"."默认不能表示换行符号,将字符串视为单行<br/>
     * x 表示模式中的空白忽略不计<br/>
     * e 正则表达式必须使用在preg_replace替换字符串的函数中时才可以使用(讲这个函数时再说)<br/>
     * A 以模式字符串开头，相当于元字符^<br/>
     * Z 以模式字符串结尾，相当于元字符$<br/>
     * U 正则表达式的特点：就是比较“贪婪”，使用该模式修正符可以取消贪婪模式<br/><br/><br/>
     */
    public static function reg($val) {
        return 'reg[' . $val . ']';
    }

    /**
     * 规则说明：<br/>
     * 数据在验证之前处理数据的规则，数据在验证的时候验证的是处理过的数据<br/>
     * 注意：<br/>
     * set和set_post后面是一个或者多个函数或者方法，多个逗号分割<br/>
     * 1.无论是函数或者方法都必须有一个字符串返回<br/>
     * 2.如果是系统函数，系统会传递当前值给系统函数，因此系统函数必须是至少接受一个字符串参数<br/>
     * 3.如果是自定义的函数，系统会传递当前值和全部数据给自定义的函数，因此自定义函数可以接收两个参数第一个是值，第二个是全部数据$data<br/>
     * 4.如果是类的方法写法是：类名称::方法名 （方法静态动态都可以，public，private，都可以）<br/>
     * @param string $val 规则内容。比如：trim<br/>
     * @param string $delimiter 规则内容的分割符，比如：# ，默认为空即可<br/><br/><br/>
     */
    public static function set($val, $delimiter = '') {
        return 'set[' . $val . ']' . $delimiter;
    }

    /**
     * 规则说明：<br/>
     * 数据在验证通过之后处理数据的规则，$this->checkData()第三个变量接收的就是set和set_post处理过的数据<br/>
     * 注意：<br/>
     * set和set_post后面是一个或者多个函数或者方法，多个逗号分割<br/>
     * 1.无论是函数或者方法都必须有一个字符串返回<br/>
     * 2.如果是系统函数，系统会传递当前值给系统函数，因此系统函数必须是至少接受一个字符串参数<br/>
     * 3.如果是自定义的函数，系统会传递当前值和全部数据给自定义的函数，因此自定义函数可以接收两个参数第一个是值，第二个是全部数据$data<br/>
     * 4.如果是类的方法写法是：类名称::方法名 （方法静态动态都可以，public，private，都可以）<br/>
     * @param string $val 规则内容。比如：sha1,md5<br/>
     * @param string $delimiter 规则内容的分割符，比如：# ，默认为空即可<br/><br/><br/>
     */
    public static function set_post($val, $delimiter = '') {
        return 'set_post[' . $val . ']' . $delimiter;
    }

    private static function can_empty_rule($rule_name, $can_empty) {
        return $rule_name . ($can_empty ? '[true]' : '');
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

//####################modules/WoniuController.php####################{


/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package                MicroPHP
 * @author                狂奔的蜗牛
 * @email                672308444@163.com
 * @copyright          Copyright (c) 2013 - 2014, 狂奔的蜗牛, Inc.
 * @link                http://git.oschina.net/snail/microphp
 * @since                Version 2.2.7
 * @createdtime       2014-05-13 11:39:58
 * @property CI_DB_active_record $db
 * @property phpFastCache        $cache
 * @property WoniuInput          $input
 */
class WoniuController extends WoniuLoaderPlus {

    private static $woniu;
    private static $instance;

    public function __construct() {
        $this->autoload();
        parent::__construct();
        self::$woniu = &$this;
    }

    private function autoload() {
        $system = WoniuLoader::$system;
        $autoload_helper = $system['helper_file_autoload'];
        $autoload_library = $system['library_file_autoload'];
        $autoload_models = $system['models_file_autoload'];
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
        foreach ($autoload_models as $key => $val) {
            if (is_array($val)) {
                $key = key($val);
                $val = $val[$key];
                $this->model($key, $val);
            } else {
                $this->model($val);
            }
        }
        /**
         * 如果使用了自定义缓存驱动，加载相应的文件
         */
        static $included = array();
        foreach ($system['cache_drivers'] as $filepath) {
            $file = pathinfo($filepath, PATHINFO_BASENAME);
            $namex = str_replace(".php", "", $file);
            //只include选择的缓存驱动文件
            if ($namex == $system['cache_config']['storage']) {
                if (!isset($included[truepath($filepath)])) {
                    WoniuLoader::includeOnce($filepath);
                } else {
                    $included[truepath($filepath)] = 1;
                }
            }
        }
    }

    public static function &getInstance() {
        return self::$woniu;
    }

    /**
     * 实例化一个控制器
     * @staticvar array $loadedClasses
     * @param type $classname_path
     * @param type $hmvc_module_floder
     * @return WoniuController
     */
    public static function instance($classname_path = null, $hmvc_module_floder = NULL) {
        if (!empty($hmvc_module_floder)) {
            WoniuRouter::switchHmvcConfig($hmvc_module_floder);
        }
        if (empty($classname_path)) {
            WoniuLoader::classAutoloadRegister();
            return self::$instance = new self();
        }
        $system = WoniuLoader::$system;
        $classname_path = str_replace('.', DIRECTORY_SEPARATOR, $classname_path);
        $classname = basename($classname_path);
        $filepath = $system['controller_folder'] . DIRECTORY_SEPARATOR . $classname_path . $system['controller_file_subfix'];
        $alias_name = strtolower($classname);
        static $loadedClasses = array();
        if (in_array($alias_name, array_keys($loadedClasses))) {
            return $loadedClasses[$alias_name];
        }
        if (file_exists($filepath)) {
            //在plugin模式下，路由器不再使用，那么自动注册不会被执行，自动加载功能会失效，所以在这里再尝试加载一次，
            //如此一来就能满足两种模式
            WoniuLoader::classAutoloadRegister();
            WoniuLoader::includeOnce($filepath);
            if (class_exists($classname, FALSE)) {
                return $loadedClasses[$alias_name] = new $classname();
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
 * @package                MicroPHP
 * @author                狂奔的蜗牛
 * @email                672308444@163.com
 * @copyright          Copyright (c) 2013 - 2014, 狂奔的蜗牛, Inc.
 * @link                http://git.oschina.net/snail/microphp
 * @since                Version 2.2.7
 * @createdtime       2014-05-13 11:39:58
 * @property CI_DB_active_record $db
 * @property phpFastCache        $cache
 * @property WoniuInput          $input
 */
class WoniuModel extends WoniuLoaderPlus {

    private static $instance;

    /**
     * 实例化一个模型
     * @param type $classname_path
     * @param type $hmvc_module_floder
     * @return type WoniuModel
     */
    public static function instance($classname_path = null, $hmvc_module_floder = NULL) {
        if (!empty($hmvc_module_floder)) {
            WoniuRouter::switchHmvcConfig($hmvc_module_floder);
        }
        //这里调用控制器instance是为了触发自动加载，从而避免了插件模式下，直接instance模型，自动加载失效的问题
        WoniuController::instance();
        if (empty($classname_path)) {
            $renew = is_bool($classname_path) && $classname_path === true;
            WoniuLoader::classAutoloadRegister();
            return empty(self::$instance) || $renew ? self::$instance = new self() : self::$instance;
        }
        $system = WoniuLoader::$system;
        $classname_path = str_replace('.', DIRECTORY_SEPARATOR, $classname_path);
        $classname = basename($classname_path);

        $model_folders = $system['model_folder'];

        if (!is_array($model_folders)) {
            $model_folders = array($model_folders);
        }
        $count = count($model_folders);
        WoniuLoader::classAutoloadRegister();
        foreach ($model_folders as $key => $model_folder) {
            $filepath = $model_folder . DIRECTORY_SEPARATOR . $classname_path . $system['model_file_subfix'];
            $alias_name = $classname;
            if (in_array($alias_name, array_keys(WoniuModelLoader::$model_files))) {
                return WoniuModelLoader::$model_files[$alias_name];
            }
            if (file_exists($filepath)) {
                //在plugin模式下，路由器不再使用，那么自动注册不会被执行，自动加载功能会失效，所以在这里再尝试加载一次，
                //如此一来就能满足两种模式
                //WoniuLoader::classAutoloadRegister();
                if (!class_exists($classname, FALSE)) {
                    WoniuLoader::includeOnce($filepath);
                }
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

}

/* End of file Model.php */

//####################modules/db-drivers/db.drivers.php####################{


/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package                MicroPHP
 * @author                狂奔的蜗牛
 * @email                672308444@163.com
 * @copyright          Copyright (c) 2013 - 2014, 狂奔的蜗牛, Inc.
 * @link                http://git.oschina.net/snail/microphp
 * @since                Version 2.2.7
 * @createdtime       2014-05-13 11:39:58
 */
class WoniuDB {

    private static $conns = array();

    public static function getInstance($config, $force_new_conn = false) {
        $default['dbdriver'] = "mysql";
        $default['hostname'] = '127.0.0.1';
        $default['port'] = '3306';
        $default['username'] = 'root';
        $default['password'] = '';
        $default['database'] = 'test';
        $default['dbprefix'] = '';
        $default['pconnect'] = TRUE;
        $default['db_debug'] = TRUE;
        $default['char_set'] = 'utf8';
        $default['dbcollat'] = 'utf8_general_ci';
        $default['swap_pre'] = '';
        $default['autoinit'] = TRUE;
        $default['stricton'] = FALSE;
        $config=  array_merge($default,$config);
        $class = 'CI_DB_' . $config['dbdriver'] . '_driver';
        $hash = md5(sha1(var_export($config, TRUE)));
        if ($force_new_conn || !isset(self::$conns[$hash])) {
            self::$conns[$hash] = new $class($config);
        }
        if ($config['dbdriver'] == 'pdo' && strpos($config['hostname'], 'mysql') !== FALSE) {
            //pdo下面dns设置mysql字符会失效，这里hack一下
            self::$conns[$hash]->simple_query('set names ' . $config['char_set']);
        }
        return self::$conns[$hash];
    }

}

/**
 * CI_DB_mysql_driver -> CI_DB -> CI_DB_active_record -> CI_DB_driver
 * CI_DB_mysql_result -> Woniu_DB_result -> CI_DB_result
 */
class CI_DB extends CI_DB_active_record {
    
}

/**
 * Database Driver Class
 *
 * This is the platform-independent base DB implementation class.
 * This class will not be called directly. Rather, the adapter
 * class for the specific database will extend and instantiate it.
 *
 * @package                CodeIgniter
 * @subpackage        Drivers
 * @category        Database
 * @author                ExpressionEngine Dev Team
 * @link                http://codeigniter.com/user_guide/database/
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
     * @access        private Called by the constructor
     * @param        mixed
     * @return        void
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

            if ($this->db_debug || WoniuLoader::$system['error_manage']) {
                $this->display_error('db_unable_to_connect');
            }
            return FALSE;
        }

// ----------------------------------------------------------------
// Select the DB... assuming a database name is specified in the config file
        if ($this->database != '') {
            if (!$this->db_select()) {
                log_message('error', 'Unable to select database: ' . $this->database);

                if ($this->db_debug || WoniuLoader::$system['error_manage']) {
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
     * @access        public
     * @param        string
     * @param        string
     * @return        resource
     */
    function db_set_charset($charset, $collation) {
        if (!$this->_db_set_charset($this->char_set, $this->dbcollat)) {
            log_message('error', 'Unable to set database connection charset: ' . $this->char_set);

            if ($this->db_debug || WoniuLoader::$system['error_manage']) {
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
     * @access        public
     * @return        string
     */
    function platform() {
        return $this->dbdriver;
    }

// --------------------------------------------------------------------

    /**
     * Database Version Number.  Returns a string containing the
     * version of the database being used
     *
     * @access        public
     * @return        string
     */
    function version() {
        if (FALSE === ($sql = $this->_version())) {
            if ($this->db_debug || WoniuLoader::$system['error_manage']) {
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
     * @access        public
     * @param        string        An SQL query string
     * @param        array        An array of binding data
     * @return        mixed
     */
    function query($sql, $binds = FALSE, $return_object = TRUE) {
        if ($sql == '') {
            if ($this->db_debug || WoniuLoader::$system['error_manage']) {
                log_message('error', 'Invalid query: ' . $sql);
                return $this->display_error('db_invalid_query');
            }
            return FALSE;
        }

// Verify table prefix and replace if necessary
        if (($this->dbprefix != '' AND $this->swap_pre != '') AND ( $this->dbprefix != $this->swap_pre)) {
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

            if ($this->db_debug || WoniuLoader::$system['error_manage']) {
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
     * @access        public
     * @return        string        the name of the result class
     */
    function load_rdriver() {
        $driver = 'CI_DB_' . $this->dbdriver . '_result';

        if (!class_exists($driver, FALSE)) {
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
     * @access        public
     * @param        string        the sql query
     * @return        mixed
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
     * @access        public
     * @return        void
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
     * @access        public
     * @return        void
     */
    function trans_strict($mode = TRUE) {
        $this->trans_strict = is_bool($mode) ? $mode : TRUE;
    }

// --------------------------------------------------------------------

    /**
     * Start Transaction
     *
     * @access        public
     * @return        void
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
     * @access        public
     * @return        bool
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
     * @access        public
     * @return        bool
     */
    function trans_status() {
        return $this->_trans_status;
    }

// --------------------------------------------------------------------

    /**
     * Compile Bindings
     *
     * @access        public
     * @param        string        the sql statement
     * @param        array        an array of bind data
     * @return        string
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
     * @access        public
     * @param        string        An SQL query string
     * @return        boolean
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
     * @access        public
     * @param        integer        The number of decimal places
     * @return        integer
     */
    function elapsed_time($decimals = 6) {
        return number_format($this->benchmark, $decimals);
    }

// --------------------------------------------------------------------

    /**
     * Returns the total number of queries
     *
     * @access        public
     * @return        integer
     */
    function total_queries() {
        return $this->query_count;
    }

// --------------------------------------------------------------------

    /**
     * Returns the last query that was executed
     *
     * @access        public
     * @return        void
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
     * @access        public
     * @param        string
     * @return        mixed
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
     * @access        public
     * @param        string
     * @return        mixed
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
     * @access        public
     * @param        string        the table name
     * @return        string
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
     * @access        public
     * @return        array
     */
    function list_tables($constrain_by_prefix = FALSE) {
// Is there a cached result?
        if (isset($this->data_cache['table_names'])) {
            return $this->data_cache['table_names'];
        }

        if (FALSE === ($sql = $this->_list_tables($constrain_by_prefix))) {
            if ($this->db_debug || WoniuLoader::$system['error_manage']) {
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
     * @access        public
     * @return        boolean
     */
    function table_exists($table_name) {
        return (!in_array($this->_protect_identifiers($table_name, TRUE, FALSE, FALSE), $this->list_tables())) ? FALSE : TRUE;
    }

// --------------------------------------------------------------------

    /**
     * Fetch MySQL Field Names
     *
     * @access        public
     * @param        string        the table name
     * @return        array
     */
    function list_fields($table = '') {
// Is there a cached result?
        if (isset($this->data_cache['field_names'][$table])) {
            return $this->data_cache['field_names'][$table];
        }

        if ($table == '') {
            if ($this->db_debug || WoniuLoader::$system['error_manage']) {
                return $this->display_error('db_field_param_missing');
            }
            return FALSE;
        }

        if (FALSE === ($sql = $this->_list_columns($table))) {
            if ($this->db_debug || WoniuLoader::$system['error_manage']) {
                return $this->display_error('db_unsupported_function');
            }
            return FALSE;
        }

        $query = $this->query($sql);

        $retval = array();
        foreach ($query->result_array() as $row) {
            if (isset($row['COLUMN_NAME'])) {
                $retval[] = $row['COLUMN_NAME'];
            } else if ($this->dbdriver == 'sqlite3') {
                $retval[] = $row['name'];
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
     * @access        public
     * @param        string
     * @param        string
     * @return        boolean
     */
    function field_exists($field_name, $table_name) {
        return (!in_array($field_name, $this->list_fields($table_name))) ? FALSE : TRUE;
    }

// --------------------------------------------------------------------

    /**
     * Returns an object with field data
     *
     * @access        public
     * @param        string        the table name
     * @return        object
     */
    function field_data($table = '') {
        if ($table == '') {
            if ($this->db_debug || WoniuLoader::$system['error_manage']) {
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
     * @access        public
     * @param        string        the table upon which the query will be performed
     * @param        array        an associative array data of key/values
     * @return        string
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
     * @access        public
     * @param        string        the table upon which the query will be performed
     * @param        array        an associative array data of key/values
     * @param        mixed        the "where" statement
     * @return        string
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
     * @access        private
     * @param        string
     * @return        bool
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
     * @access        public
     * @param        string        the function name
     * @param        mixed        any parameters needed by the function
     * @return        mixed
     */
    function call_function($function) {
        $driver = ($this->dbdriver == 'postgre') ? 'pg_' : $this->dbdriver . '_';

        if (FALSE === strpos($driver, $function)) {
            $function = $driver . $function;
        }

        if (!function_exists($function)) {
            if ($this->db_debug || WoniuLoader::$system['error_manage']) {
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
     * @access        public
     * @param        string        the path to the cache directory
     * @return        void
     */
    function cache_set_path($path = '') {
        $this->cachedir = $path;
    }

// --------------------------------------------------------------------

    /**
     * Enable Query Caching
     *
     * @access        public
     * @return        void
     */
    function cache_on() {
        $this->cache_on = TRUE;
        return TRUE;
    }

// --------------------------------------------------------------------

    /**
     * Disable Query Caching
     *
     * @access        public
     * @return        void
     */
    function cache_off() {
        $this->cache_on = FALSE;
        return FALSE;
    }

// --------------------------------------------------------------------

    /**
     * Delete the cache files associated with a particular URI
     *
     * @access        public
     * @return        void
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
     * @access        public
     * @return        void
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
     * @access        private
     * @return        void
     */
    function _cache_init() {
        if (is_object($this->CACHE) AND class_exists('CI_DB_Cache', FALSE)) {
            return TRUE;
        }

        if (!class_exists('CI_DB_Cache', FALSE)) {
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
     * @access        public
     * @return        void
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
     * @access        public
     * @param        string        the error message
     * @param        string        any "swap" values
     * @param        boolean        whether to localize the message
     * @return        string        sends the application/error_db.php template
     */
    function display_error($error = '', $swap = '', $native = FALSE) {
        woniu_db_error_handler($error, $swap, $native);
    }

// --------------------------------------------------------------------

    /**
     * Protect Identifiers
     *
     * This function adds backticks if appropriate based on db type
     *
     * @access        private
     * @param        mixed        the item to escape
     * @return        mixed        the item with backticks
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
     * @access        private
     * @param        string
     * @param        bool
     * @param        mixed
     * @param        bool
     * @return        string
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

        if ($protect_identifiers === TRUE AND ! in_array($item, $this->_reserved_identifiers)) {
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
     * @return        void
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
 * @category        Database
 * @author                ExpressionEngine Dev Team
 * @link                http://codeigniter.com/user_guide/database/
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
     * @access        public
     * @param        string        can be "object" or "array"
     * @return        mixed        either a result object or array
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
                if (method_exists($object, 'set_' . $key)) {
                    $object->{'set_' . $key}($value);
                } else {
                    $object->$key = $value;
                }
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
     * @access        public
     * @return        object
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
     * @access        public
     * @return        array
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
     * @access        public
     * @param        string
     * @param        string        can be "object" or "array"
     * @return        mixed        either a result object or array
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
     * @access        public
     * @return        object
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

        if ($key != '' AND ! is_null($value)) {
            $this->row_data[$key] = $value;
        }
    }

// --------------------------------------------------------------------

    /**
     * Returns a single result row - custom object version
     *
     * @access        public
     * @return        object
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
     * @access        public
     * @return        object
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
     * @access        public
     * @return        array
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
     * @access        public
     * @return        object
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
     * @access        public
     * @return        object
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
     * @access        public
     * @return        object
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
     * @access        public
     * @return        object
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
 * @package                CodeIgniter
 * @subpackage        Drivers
 * @category        Database
 * @author                ExpressionEngine Dev Team
 * @link                http://codeigniter.com/user_guide/database/
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
     * @param        string
     * @return        object
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
     * @param        string        the field
     * @param        string        an alias
     * @return        object
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
     * @param        string        the field
     * @param        string        an alias
     * @return        object
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
     * @param        string        the field
     * @param        string        an alias
     * @return        object
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
     * @param        string        the field
     * @param        string        an alias
     * @return        object
     */
    public function select_sum($select = '', $alias = '') {
        return $this->_max_min_avg_sum($select, $alias, 'SUM');
    }

// --------------------------------------------------------------------

    /**
     * Processing Function for the four functions above:
     *
     *         select_max()
     *         select_min()
     *         select_avg()
     *  select_sum()
     *
     * @param        string        the field
     * @param        string        an alias
     * @return        object
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
     * @param        string
     * @return        string
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
     * @param        bool
     * @return        object
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
     * @param        mixed        can be a string or array
     * @return        object
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
     * @param        string
     * @param        string        the join condition
     * @param        string        the type of join
     * @return        object
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
     * @param        mixed
     * @param        mixed
     * @return        object
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
     * @param        mixed
     * @param        mixed
     * @return        object
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
     * @param        mixed
     * @param        mixed
     * @param        string
     * @return        object
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
     * @param        string        The field to search
     * @param        array        The values searched on
     * @return        object
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
     * @param        string        The field to search
     * @param        array        The values searched on
     * @return        object
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
     * @param        string        The field to search
     * @param        array        The values searched on
     * @return        object
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
     * @param        string        The field to search
     * @param        array        The values searched on
     * @return        object
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
     * @param        string        The field to search
     * @param        array        The values searched on
     * @param        boolean        If the statement would be IN or NOT IN
     * @param        string
     * @return        object
     */
    protected function _where_in($key = NULL, $values = NULL, $not = FALSE, $type = 'AND ') {
        if ($key === NULL OR $values === NULL) {
            return;
        }

        if (!is_array($values)) {
            $values = array($values);
        } elseif (empty($values)) {
            $values = array('');
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
     * @param        mixed
     * @param        mixed
     * @return        object
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
     * @param        mixed
     * @param        mixed
     * @return        object
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
     * @param        mixed
     * @param        mixed
     * @return        object
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
     * @param        mixed
     * @param        mixed
     * @return        object
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
     * @param        mixed
     * @param        mixed
     * @param        string
     * @return        object
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
     * @param        string
     * @return        object
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
     * @param        string
     * @param        string
     * @return        object
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
     * @param        string
     * @param        string
     * @return        object
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
     * @param        string
     * @param        string
     * @return        object
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
     * @param        string
     * @param        string        direction: asc or desc
     * @return        object
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
     * @param        integer        the limit value
     * @param        integer        the offset value
     * @return        object
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
     * @param        integer        the offset value
     * @return        object
     */
    public function offset($offset) {
        $this->ar_offset = $offset;
        return $this;
    }

// --------------------------------------------------------------------

    /**
     * The "set" function.  Allows key/value pairs to be set for inserting or updating
     *
     * @param        mixed
     * @param        string
     * @param        boolean
     * @return        object
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
     * @param        string        the table
     * @param        string        the limit clause
     * @param        string        the offset clause
     * @return        object
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
     * @param        string
     * @return        string
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
     * @param        string        the where clause
     * @param        string        the limit clause
     * @param        string        the offset clause
     * @return        object
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
     * @param        string        the table to retrieve the results from
     * @param        array        an associative array of insert values
     * @return        object
     */
    public function insert_batch($table = '', $set = NULL) {
        if (!is_null($set)) {
            $this->set_insert_batch($set);
        }

        if (count($this->ar_set) == 0) {
            if ($this->db_debug || WoniuLoader::$system['error_manage']) {
//No valid data array.  Folds in cases where keys and values did not match up
                return $this->display_error('db_must_use_set');
            }
            return FALSE;
        }

        if ($table == '') {
            if (!isset($this->ar_from[0])) {
                if ($this->db_debug || WoniuLoader::$system['error_manage']) {
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
     * @param        mixed
     * @param        string
     * @param        boolean
     * @return        object
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
     * @param        string        the table to insert data into
     * @param        array        an associative array of insert values
     * @return        object
     */
    function insert($table = '', $set = NULL) {
        if (!is_null($set)) {
            $this->set($set);
        }

        if (count($this->ar_set) == 0) {
            if ($this->db_debug || WoniuLoader::$system['error_manage']) {
                return $this->display_error('db_must_use_set');
            }
            return FALSE;
        }

        if ($table == '') {
            if (!isset($this->ar_from[0])) {
                if ($this->db_debug || WoniuLoader::$system['error_manage']) {
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
     * @param        string        the table to replace data into
     * @param        array        an associative array of insert values
     * @return        object
     */
    public function replace($table = '', $set = NULL) {
        if (!is_null($set)) {
            $this->set($set);
        }

        if (count($this->ar_set) == 0) {
            if ($this->db_debug || WoniuLoader::$system['error_manage']) {
                return $this->display_error('db_must_use_set');
            }
            return FALSE;
        }

        if ($table == '') {
            if (!isset($this->ar_from[0])) {
                if ($this->db_debug || WoniuLoader::$system['error_manage']) {
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
     * @param        string        the table to retrieve the results from
     * @param        array        an associative array of update values
     * @param        mixed        the where clause
     * @return        object
     */
    public function update($table = '', $set = NULL, $where = NULL, $limit = NULL) {
// Combine any cached components with the current statements
        $this->_merge_cache();

        if (!is_null($set)) {
            $this->set($set);
        }

        if (count($this->ar_set) == 0) {
            if ($this->db_debug || WoniuLoader::$system['error_manage']) {
                return $this->display_error('db_must_use_set');
            }
            return FALSE;
        }

        if ($table == '') {
            if (!isset($this->ar_from[0])) {
                if ($this->db_debug || WoniuLoader::$system['error_manage']) {
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
     * @param        string        the table to retrieve the results from
     * @param        array        an associative array of update values
     * @param        string        the where key
     * @return        object
     */
    public function update_batch($table = '', $set = NULL, $index = NULL) {
// Combine any cached components with the current statements
        $this->_merge_cache();

        if (is_null($index)) {
            if ($this->db_debug || WoniuLoader::$system['error_manage']) {
                return $this->display_error('db_must_use_index');
            }

            return FALSE;
        }

        if (!is_null($set)) {
            $this->set_update_batch($set, $index);
        }

        if (count($this->ar_set) == 0) {
            if ($this->db_debug || WoniuLoader::$system['error_manage']) {
                return $this->display_error('db_must_use_set');
            }

            return FALSE;
        }

        if ($table == '') {
            if (!isset($this->ar_from[0])) {
                if ($this->db_debug || WoniuLoader::$system['error_manage']) {
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
     * @param        array
     * @param        string
     * @param        boolean
     * @return        object
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
                    $not[] = $k2 . '-' . $v2;
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
     * @param        string        the table to empty
     * @return        object
     */
    public function empty_table($table = '') {
        if ($table == '') {
            if (!isset($this->ar_from[0])) {
                if ($this->db_debug || WoniuLoader::$system['error_manage']) {
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
     * @param        string        the table to truncate
     * @return        object
     */
    public function truncate($table = '') {
        if ($table == '') {
            if (!isset($this->ar_from[0])) {
                if ($this->db_debug || WoniuLoader::$system['error_manage']) {
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
     * @param        mixed        the table(s) to delete from. String or array
     * @param        mixed        the where clause
     * @param        mixed        the limit clause
     * @param        boolean
     * @return        object
     */
    public function delete($table = '', $where = '', $limit = NULL, $reset_data = TRUE) {
// Combine any cached components with the current statements
        $this->_merge_cache();

        if ($table == '') {
            if (!isset($this->ar_from[0])) {
                if ($this->db_debug || WoniuLoader::$system['error_manage']) {
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
            if ($this->db_debug || WoniuLoader::$system['error_manage']) {
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
     * @param        string        the table
     * @return        string
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
     * @param        string        the prefix
     * @return        string
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
     * @param        string        The table to inspect
     * @return        string
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
     * @return        string
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
     * @param        object
     * @return        array
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
     * @param        object
     * @return        array
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
     * @return        void
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
     * @return        void
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
     * @access        public
     * @return        void
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
     * @return        void
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
     * @param        array        An array of fields to reset
     * @return        void
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
     * @return        void
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
     * @return        void
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

/* End of file db.php */

//####################modules/db-drivers/mysql.driver.php####################{


/**
 * MySQL Database Adapter Class
 *
 * Note: _DB is an extender class that the app controller
 * creates dynamically based on whether the active record
 * class is being used or not.
 *
 * @package                CodeIgniter
 * @subpackage        Drivers
 * @category        Database
 * @author                ExpressionEngine Dev Team
 * @link                http://codeigniter.com/user_guide/database/
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
     * @access        private called by the base class
     * @return        resource
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
     * @access        private called by the base class
     * @return        resource
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
     * @access        public
     * @return        void
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
     * @access        private called by the base class
     * @return        resource
     */
    function db_select() {
        return @mysql_select_db($this->database, $this->conn_id);
    }

// --------------------------------------------------------------------

    /**
     * Set client character set
     *
     * @access        public
     * @param        string
     * @param        string
     * @return        resource
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
     * @access        public
     * @return        string
     */
    function _version() {
        return "SELECT version() AS ver";
    }

// --------------------------------------------------------------------

    /**
     * Execute the query
     *
     * @access        private called by the base class
     * @param        string        an SQL query
     * @return        resource
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
     * @access        private called by execute()
     * @param        string        an SQL query
     * @return        string
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
     * @access        public
     * @return        bool
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
     * @access        public
     * @return        bool
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
     * @access        public
     * @return        bool
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
     * @access        public
     * @param        string
     * @param        bool        whether or not the string will be used in a LIKE condition
     * @return        string
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
        } elseif (function_exists('mysql_escape_string') && (version_compare(PHP_VERSION, '5.3.0','<'))) {
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
     * @access        public
     * @return        integer
     */
    function affected_rows() {
        return @mysql_affected_rows($this->conn_id);
    }

// --------------------------------------------------------------------

    /**
     * Insert ID
     *
     * @access        public
     * @return        integer
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
     * @access        public
     * @param        string
     * @return        string
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
     * @access        private
     * @param        boolean
     * @return        string
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
     * @access        public
     * @param        string        the table name
     * @return        string
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
     * @access        public
     * @param        string        the table name
     * @return        object
     */
    function _field_data($table) {
        return "DESCRIBE " . $table;
    }

// --------------------------------------------------------------------

    /**
     * The error message string
     *
     * @access        private
     * @return        string
     */
    function _error_message() {
        return mysql_error($this->conn_id);
    }

// --------------------------------------------------------------------

    /**
     * The error message number
     *
     * @access        private
     * @return        integer
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
     * @access        private
     * @param        string
     * @return        string
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
     * @access        public
     * @param        type
     * @return        type
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
     * @access        public
     * @param        string        the table name
     * @param        array        the insert keys
     * @param        array        the insert values
     * @return        string
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
     * @access        public
     * @param        string        the table name
     * @param        array        the insert keys
     * @param        array        the insert values
     * @return        string
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
     * @access        public
     * @param        string        the table name
     * @param        array        the insert keys
     * @param        array        the insert values
     * @return        string
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
     * @access        public
     * @param        string        the table name
     * @param        array        the update data
     * @param        array        the where clause
     * @param        array        the orderby clause
     * @param        array        the limit clause
     * @return        string
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
     * @access        public
     * @param        string        the table name
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
//####################modules/db-drivers/mysqli.driver.php####################{

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 2.2.7
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * MySQLi Database Adapter Class - MySQLi only works with PHP 5
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
class CI_DB_mysqli_driver extends CI_DB {

	var $dbdriver = 'mysqli';

	// The character used for escaping
	var $_escape_char = '`';

	// clause and character used for LIKE escape sequences - not used in MySQL
	var $_like_escape_str = '';
	var $_like_escape_chr = '';

	/**
	 * The syntax to count rows is slightly different across different
	 * database engines, so this string appears in each driver and is
	 * used for the count_all() and count_all_results() functions.
	 */
	var $_count_string = "SELECT COUNT(*) AS ";
	var $_random_keyword = ' RAND()'; // database specific random keyword

	/**
	 * Whether to use the MySQL "delete hack" which allows the number
	 * of affected rows to be shown. Uses a preg_replace when enabled,
	 * adding a bit more processing to all queries.
	 */
	var $delete_hack = TRUE;

	// whether SET NAMES must be used to set the character set
	var $use_set_names;
	
	// --------------------------------------------------------------------

	/**
	 * Non-persistent database connection
	 *
	 * @access	private called by the base class
	 * @return	resource
	 */
	function db_connect()
	{
		if ($this->port != '')
		{
			return @mysqli_connect($this->hostname, $this->username, $this->password, $this->database, $this->port);
		}
		else
		{
			return @mysqli_connect($this->hostname, $this->username, $this->password, $this->database);
		}

	}

	// --------------------------------------------------------------------

	/**
	 * Persistent database connection
	 *
	 * @access	private called by the base class
	 * @return	resource
	 */
	function db_pconnect()
	{
		return $this->db_connect();
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
	function reconnect()
	{
		if (mysqli_ping($this->conn_id) === FALSE)
		{
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
	function db_select()
	{
		return @mysqli_select_db($this->conn_id, $this->database);
	}

	// --------------------------------------------------------------------

	/**
	 * Set client character set
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @return	resource
	 */
	function _db_set_charset($charset, $collation)
	{
		if ( ! isset($this->use_set_names))
		{
			// mysqli_set_charset() requires MySQL >= 5.0.7, use SET NAMES as fallback
			$this->use_set_names = (version_compare(mysqli_get_server_info($this->conn_id), '5.0.7', '>=')) ? FALSE : TRUE;
		}

		if ($this->use_set_names === TRUE)
		{
			return @mysqli_query($this->conn_id, "SET NAMES '".$this->escape_str($charset)."' COLLATE '".$this->escape_str($collation)."'");
		}
		else
		{
			return @mysqli_set_charset($this->conn_id, $charset);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Version number query string
	 *
	 * @access	public
	 * @return	string
	 */
	function _version()
	{
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
	function _execute($sql)
	{
		$sql = $this->_prep_query($sql);
		$result = @mysqli_query($this->conn_id, $sql);
		return $result;
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
	function _prep_query($sql)
	{
		// "DELETE FROM TABLE" returns 0 affected rows This hack modifies
		// the query so that it returns the number of affected rows
		if ($this->delete_hack === TRUE)
		{
			if (preg_match('/^\s*DELETE\s+FROM\s+(\S+)\s*$/i', $sql))
			{
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
	function trans_begin($test_mode = FALSE)
	{
		if ( ! $this->trans_enabled)
		{
			return TRUE;
		}

		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ($this->_trans_depth > 0)
		{
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
	function trans_commit()
	{
		if ( ! $this->trans_enabled)
		{
			return TRUE;
		}

		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ($this->_trans_depth > 0)
		{
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
	function trans_rollback()
	{
		if ( ! $this->trans_enabled)
		{
			return TRUE;
		}

		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ($this->_trans_depth > 0)
		{
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
	function escape_str($str, $like = FALSE)
	{
		if (is_array($str))
		{
			foreach ($str as $key => $val)
			{
				$str[$key] = $this->escape_str($val, $like);
			}

			return $str;
		}

		if (function_exists('mysqli_real_escape_string') AND is_object($this->conn_id))
		{
			$str = mysqli_real_escape_string($this->conn_id, $str);
		}
		elseif (function_exists('mysql_escape_string'))
		{
			$str = mysql_escape_string($str);
		}
		else
		{
			$str = addslashes($str);
		}

		// escape LIKE condition wildcards
		if ($like === TRUE)
		{
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
	function affected_rows()
	{
		return @mysqli_affected_rows($this->conn_id);
	}

	// --------------------------------------------------------------------

	/**
	 * Insert ID
	 *
	 * @access	public
	 * @return	integer
	 */
	function insert_id()
	{
		return @mysqli_insert_id($this->conn_id);
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
	function count_all($table = '')
	{
		if ($table == '')
		{
			return 0;
		}

		$query = $this->query($this->_count_string . $this->_protect_identifiers('numrows') . " FROM " . $this->_protect_identifiers($table, TRUE, NULL, FALSE));

		if ($query->num_rows() == 0)
		{
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
	function _list_tables($prefix_limit = FALSE)
	{
		$sql = "SHOW TABLES FROM ".$this->_escape_char.$this->database.$this->_escape_char;

		if ($prefix_limit !== FALSE AND $this->dbprefix != '')
		{
			$sql .= " LIKE '".$this->escape_like_str($this->dbprefix)."%'";
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
	function _list_columns($table = '')
	{
		return "SHOW COLUMNS FROM ".$this->_protect_identifiers($table, TRUE, NULL, FALSE);
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
	function _field_data($table)
	{
		return "DESCRIBE ".$table;
	}

	// --------------------------------------------------------------------

	/**
	 * The error message string
	 *
	 * @access	private
	 * @return	string
	 */
	function _error_message()
	{
		return mysqli_error($this->conn_id);
	}

	// --------------------------------------------------------------------

	/**
	 * The error message number
	 *
	 * @access	private
	 * @return	integer
	 */
	function _error_number()
	{
		return mysqli_errno($this->conn_id);
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
	function _escape_identifiers($item)
	{
		if ($this->_escape_char == '')
		{
			return $item;
		}

		foreach ($this->_reserved_identifiers as $id)
		{
			if (strpos($item, '.'.$id) !== FALSE)
			{
				$str = $this->_escape_char. str_replace('.', $this->_escape_char.'.', $item);

				// remove duplicates if the user already included the escape
				return preg_replace('/['.$this->_escape_char.']+/', $this->_escape_char, $str);
			}
		}

		if (strpos($item, '.') !== FALSE)
		{
			$str = $this->_escape_char.str_replace('.', $this->_escape_char.'.'.$this->_escape_char, $item).$this->_escape_char;
		}
		else
		{
			$str = $this->_escape_char.$item.$this->_escape_char;
		}

		// remove duplicates if the user already included the escape
		return preg_replace('/['.$this->_escape_char.']+/', $this->_escape_char, $str);
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
	function _from_tables($tables)
	{
		if ( ! is_array($tables))
		{
			$tables = array($tables);
		}

		return '('.implode(', ', $tables).')';
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
	function _insert($table, $keys, $values)
	{
		return "INSERT INTO ".$table." (".implode(', ', $keys).") VALUES (".implode(', ', $values).")";
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
	function _insert_batch($table, $keys, $values)
	{
		return "INSERT INTO ".$table." (".implode(', ', $keys).") VALUES ".implode(', ', $values);
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
	function _replace($table, $keys, $values)
	{
		return "REPLACE INTO ".$table." (".implode(', ', $keys).") VALUES (".implode(', ', $values).")";
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
	function _update($table, $values, $where, $orderby = array(), $limit = FALSE)
	{
		foreach ($values as $key => $val)
		{
			$valstr[] = $key." = ".$val;
		}

		$limit = ( ! $limit) ? '' : ' LIMIT '.$limit;

		$orderby = (count($orderby) >= 1)?' ORDER BY '.implode(", ", $orderby):'';

		$sql = "UPDATE ".$table." SET ".implode(', ', $valstr);

		$sql .= ($where != '' AND count($where) >=1) ? " WHERE ".implode(" ", $where) : '';

		$sql .= $orderby.$limit;

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
	function _update_batch($table, $values, $index, $where = NULL)
	{
		$ids = array();
		$where = ($where != '' AND count($where) >=1) ? implode(" ", $where).' AND ' : '';

		foreach ($values as $key => $val)
		{
			$ids[] = $val[$index];

			foreach (array_keys($val) as $field)
			{
				if ($field != $index)
				{
					$final[$field][] =  'WHEN '.$index.' = '.$val[$index].' THEN '.$val[$field];
				}
			}
		}

		$sql = "UPDATE ".$table." SET ";
		$cases = '';

		foreach ($final as $k => $v)
		{
			$cases .= $k.' = CASE '."\n";
			foreach ($v as $row)
			{
				$cases .= $row."\n";
			}

			$cases .= 'ELSE '.$k.' END, ';
		}

		$sql .= substr($cases, 0, -2);

		$sql .= ' WHERE '.$where.$index.' IN ('.implode(',', $ids).')';

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
	function _truncate($table)
	{
		return "TRUNCATE ".$table;
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
	function _delete($table, $where = array(), $like = array(), $limit = FALSE)
	{
		$conditions = '';

		if (count($where) > 0 OR count($like) > 0)
		{
			$conditions = "\nWHERE ";
			$conditions .= implode("\n", $this->ar_where);

			if (count($where) > 0 && count($like) > 0)
			{
				$conditions .= " AND ";
			}
			$conditions .= implode("\n", $like);
		}

		$limit = ( ! $limit) ? '' : ' LIMIT '.$limit;

		return "DELETE FROM ".$table.$conditions.$limit;
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
	function _limit($sql, $limit, $offset)
	{
		$sql .= "LIMIT ".$limit;

		if ($offset > 0)
		{
			$sql .= " OFFSET ".$offset;
		}

		return $sql;
	}

	// --------------------------------------------------------------------

	/**
	 * Close DB Connection
	 *
	 * @access	public
	 * @param	resource
	 * @return	void
	 */
	function _close($conn_id)
	{
		@mysqli_close($conn_id);
	}


}


/* End of file mysqli_driver.php */
/* Location: ./system/database/drivers/mysqli/mysqli_driver.php */

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 2.2.7
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * MySQLi Result Class
 *
 * This class extends the parent result class: CI_DB_result
 *
 * @category	Database
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_mysqli_result extends CI_DB_result {

	/**
	 * Number of rows in the result set
	 *
	 * @access	public
	 * @return	integer
	 */
	function num_rows()
	{
		return @mysqli_num_rows($this->result_id);
	}

	// --------------------------------------------------------------------

	/**
	 * Number of fields in the result set
	 *
	 * @access	public
	 * @return	integer
	 */
	function num_fields()
	{
		return @mysqli_num_fields($this->result_id);
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
	function list_fields()
	{
		$field_names = array();
		while ($field = mysqli_fetch_field($this->result_id))
		{
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
	function field_data()
	{
		$retval = array();
		while ($field = mysqli_fetch_object($this->result_id))
		{
			preg_match('/([a-zA-Z]+)(\(\d+\))?/', $field->Type, $matches);

			$type = (array_key_exists(1, $matches)) ? $matches[1] : NULL;
			$length = (array_key_exists(2, $matches)) ? preg_replace('/[^\d]/', '', $matches[2]) : NULL;

			$F				= new stdClass();
			$F->name		= $field->Field;
			$F->type		= $type;
			$F->default		= $field->Default;
			$F->max_length	= $length;
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
	function free_result()
	{
		if (is_object($this->result_id))
		{
			mysqli_free_result($this->result_id);
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
	function _data_seek($n = 0)
	{
		return mysqli_data_seek($this->result_id, $n);
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
	function _fetch_assoc()
	{
		return mysqli_fetch_assoc($this->result_id);
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
	function _fetch_object()
	{
		return mysqli_fetch_object($this->result_id);
	}

}


/* End of file mysqli_result.php */
/* Location: ./system/database/drivers/mysqli/mysqli_result.php */
//####################modules/db-drivers/pdo.driver.php####################{


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
class CI_DB_pdo_driver extends CI_DB{

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

	function __construct($params)
	{
		parent::__construct($params);

		// clause and character used for LIKE escape sequences
		if (strpos($this->hostname, 'mysql') !== FALSE)
		{
			$this->_like_escape_str = '';
			$this->_like_escape_chr = '';

			//Prior to this version, the charset can't be set in the dsn
			if(is_php('5.3.6'))
			{
				$this->hostname .= ";charset={$this->char_set}";
			}

			//Set the charset with the connection options
			$this->options['PDO::MYSQL_ATTR_INIT_COMMAND'] = "SET NAMES {$this->char_set}";
		}
		elseif (strpos($this->hostname, 'odbc') !== FALSE)
		{
			$this->_like_escape_str = " {escape '%s'} ";
			$this->_like_escape_chr = '!';
		}
		else
		{
			$this->_like_escape_str = " ESCAPE '%s' ";
			$this->_like_escape_chr = '!';
		}

		empty($this->database) OR $this->hostname .= ';dbname='.$this->database;

		$this->trans_enabled = FALSE;

		$this->_random_keyword = ' RND('.time().')'; // database specific random keyword
	}

	/**
	 * Non-persistent database connection
	 *
	 * @access	private called by the base class
	 * @return	resource
	 */
	function db_connect()
	{
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
	function db_pconnect()
	{
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
	function reconnect()
	{
		if ($this->db->db_debug)
		{
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
	function db_select()
	{
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
	function _db_set_charset($charset, $collation)
	{
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
	function _version()
	{
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
	function _execute($sql)
	{
		$sql = $this->_prep_query($sql);
		$result_id = $this->conn_id->prepare($sql);
		$result_id->execute();
		
		if (is_object($result_id))
		{
			if (is_numeric(stripos($sql, 'SELECT')))
			{
				$this->affect_rows = count($result_id->fetchAll());
				$result_id->execute();
			}
			else
			{
				$this->affect_rows = $result_id->rowCount();
			}
		}
		else
		{
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
	function _prep_query($sql)
	{
		return $sql;
	}

	// --------------------------------------------------------------------

	/**
	 * Begin Transaction
	 *
	 * @access	public
	 * @return	bool
	 */
	function trans_begin($test_mode = FALSE)
	{
		if ( ! $this->trans_enabled)
		{
			return TRUE;
		}

		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ($this->_trans_depth > 0)
		{
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
	function trans_commit()
	{
		if ( ! $this->trans_enabled)
		{
			return TRUE;
		}

		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ($this->_trans_depth > 0)
		{
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
	function trans_rollback()
	{
		if ( ! $this->trans_enabled)
		{
			return TRUE;
		}

		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ($this->_trans_depth > 0)
		{
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
	function escape_str($str, $like = FALSE)
	{
		if (is_array($str))
		{
			foreach ($str as $key => $val)
			{
				$str[$key] = $this->escape_str($val, $like);
			}

			return $str;
		}
		
		//Escape the string
		$str = $this->conn_id->quote($str);
		
		//If there are duplicated quotes, trim them away
		if (strpos($str, "'") === 0)
		{
			$str = substr($str, 1, -1);
		}
		
		// escape LIKE condition wildcards
		if ($like === TRUE)
		{
			$str = str_replace(	array('%', '_', $this->_like_escape_chr),
								array($this->_like_escape_chr.'%', $this->_like_escape_chr.'_', $this->_like_escape_chr.$this->_like_escape_chr),
								$str);
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
	function affected_rows()
	{
		return $this->affect_rows;
	}

	// --------------------------------------------------------------------

	/**
	 * Insert ID
	 * 
	 * @access	public
	 * @return	integer
	 */
	function insert_id($name=NULL)
	{
		//Convenience method for postgres insertid
		if (strpos($this->hostname, 'pgsql') !== FALSE)
		{
			$v = $this->_version();

			$table	= func_num_args() > 0 ? func_get_arg(0) : NULL;

			if ($table == NULL && $v >= '8.1')
			{
				$sql='SELECT LASTVAL() as ins_id';
			}
			$query = $this->query($sql);
			$row = $query->row();
			return $row->ins_id;
		}
		else
		{
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
	function count_all($table = '')
	{
		if ($table == '')
		{
			return 0;
		}

		$query = $this->query($this->_count_string . $this->_protect_identifiers('numrows') . " FROM " . $this->_protect_identifiers($table, TRUE, NULL, FALSE));

		if ($query->num_rows() == 0)
		{
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
	function _list_tables($prefix_limit = FALSE)
	{
		$sql = "SHOW TABLES FROM `".$this->database."`";

		if ($prefix_limit !== FALSE AND $this->dbprefix != '')
		{
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
	function _list_columns($table = '')
	{
		return "SHOW COLUMNS FROM ".$table;
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
	function _field_data($table)
	{
		return "SELECT TOP 1 FROM ".$table;
	}

	// --------------------------------------------------------------------

	/**
	 * The error message string
	 *
	 * @access	private
	 * @return	string
	 */
	function _error_message()
	{
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
	function _error_number()
	{
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
	function _escape_identifiers($item)
	{
		if ($this->_escape_char == '')
		{
			return $item;
		}

		foreach ($this->_reserved_identifiers as $id)
		{
			if (strpos($item, '.'.$id) !== FALSE)
			{
				$str = $this->_escape_char. str_replace('.', $this->_escape_char.'.', $item);

				// remove duplicates if the user already included the escape
				return preg_replace('/['.$this->_escape_char.']+/', $this->_escape_char, $str);
			}
		}

		if (strpos($item, '.') !== FALSE)
		{
			$str = $this->_escape_char.str_replace('.', $this->_escape_char.'.'.$this->_escape_char, $item).$this->_escape_char;
			
		}
		else
		{
			$str = $this->_escape_char.$item.$this->_escape_char;
		}

		// remove duplicates if the user already included the escape
		return preg_replace('/['.$this->_escape_char.']+/', $this->_escape_char, $str);
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
	function _from_tables($tables)
	{
		if ( ! is_array($tables))
		{
			$tables = array($tables);
		}

		return (count($tables) == 1) ? $tables[0] : '('.implode(', ', $tables).')';
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
	function _insert($table, $keys, $values)
	{
		return "INSERT INTO ".$table." (".implode(', ', $keys).") VALUES (".implode(', ', $values).")";
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
	function _insert_batch($table, $keys, $values)
	{
		return "INSERT INTO ".$table." (".implode(', ', $keys).") VALUES ".implode(', ', $values);
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
	function _update($table, $values, $where, $orderby = array(), $limit = FALSE)
	{
		foreach ($values as $key => $val)
		{
			$valstr[] = $key." = ".$val;
		}

		$limit = ( ! $limit) ? '' : ' LIMIT '.$limit;

		$orderby = (count($orderby) >= 1)?' ORDER BY '.implode(", ", $orderby):'';

		$sql = "UPDATE ".$table." SET ".implode(', ', $valstr);

		$sql .= ($where != '' AND count($where) >=1) ? " WHERE ".implode(" ", $where) : '';

		$sql .= $orderby.$limit;

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
	function _update_batch($table, $values, $index, $where = NULL)
	{
		$ids = array();
		$where = ($where != '' AND count($where) >=1) ? implode(" ", $where).' AND ' : '';

		foreach ($values as $key => $val)
		{
			$ids[] = $val[$index];

			foreach (array_keys($val) as $field)
			{
				if ($field != $index)
				{
					$final[$field][] =  'WHEN '.$index.' = '.$val[$index].' THEN '.$val[$field];
				}
			}
		}

		$sql = "UPDATE ".$table." SET ";
		$cases = '';

		foreach ($final as $k => $v)
		{
			$cases .= $k.' = CASE '."\n";
			foreach ($v as $row)
			{
				$cases .= $row."\n";
			}

			$cases .= 'ELSE '.$k.' END, ';
		}

		$sql .= substr($cases, 0, -2);

		$sql .= ' WHERE '.$where.$index.' IN ('.implode(',', $ids).')';

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
	function _truncate($table)
	{
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
	function _delete($table, $where = array(), $like = array(), $limit = FALSE)
	{
		$conditions = '';

		if (count($where) > 0 OR count($like) > 0)
		{
			$conditions = "\nWHERE ";
			$conditions .= implode("\n", $this->ar_where);

			if (count($where) > 0 && count($like) > 0)
			{
				$conditions .= " AND ";
			}
			$conditions .= implode("\n", $like);
		}

		$limit = ( ! $limit) ? '' : ' LIMIT '.$limit;

		return "DELETE FROM ".$table.$conditions.$limit;
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
	function _limit($sql, $limit, $offset)
	{
		if (strpos($this->hostname, 'cubrid') !== FALSE || strpos($this->hostname, 'sqlite') !== FALSE)
		{
			if ($offset == 0)
			{
				$offset = '';
			}
			else
			{
				$offset .= ", ";
			}

			return $sql."LIMIT ".$offset.$limit;
		}
		else
		{
			$sql .= "LIMIT ".$limit;

			if ($offset > 0)
			{
				$sql .= " OFFSET ".$offset;
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
	function _close($conn_id)
	{
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
	public function num_rows()
	{
		if (is_int($this->num_rows))
		{
			return $this->num_rows;
		}
		elseif (($this->num_rows = $this->result_id->rowCount()) > 0)
		{
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
	function num_fields()
	{
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
	function list_fields()
	{
		if ($this->db->db_debug)
		{
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
	function field_data()
	{
		$data = array();
	
		try
		{
			for($i = 0; $i < $this->num_fields(); $i++)
			{
				$data[] = $this->result_id->getColumnMeta($i);
			}
			
			return $data;
		}
		catch (Exception $e)
		{
			if ($this->db->db_debug)
			{
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
	function free_result()
	{
		if (is_object($this->result_id))
		{
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
	function _data_seek($n = 0)
	{
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
	function _fetch_assoc()
	{
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
	function _fetch_object()
	{	
		return $this->result_id->fetchObject();
	}

}


/* End of file pdo_result.php */
//####################modules/db-drivers/sqlite3.driver.php####################{


/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		MicroPHP
 * @author		狂奔的蜗牛
 * @email		672308444@163.com
 * @copyright          Copyright (c) 2013 - 2014, 狂奔的蜗牛, Inc.
 * @link		http://git.oschina.net/snail/microphp
 * @since		Version 2.2.7
 * @createdtime       2014-05-13 11:39:58
 */
// SQLite3 PDO driver v.0.02 by Xintrea
// Tested on CodeIgniter 1.7.1
// Based on CI_DB_pdo_driver class v.0.1
// Warning! This PDO driver work with SQLite3 only!

/**
 * Code Igniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		CodeIgniter
 * @author		Rick Ellis
 * @copyright  Copyright (c) 2006, pMachine, Inc.
 * @license		http://www.codeignitor.com/user_guide/license.html
 * @link		http://www.codeigniter.com
 * @since		Version 2.2.7
 * @filesource
 */
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
 * @author		Dready
 * @link		http://dready.jexiste.fr/dotclear/
 */
class CI_DB_sqlite3_driver extends CI_DB {

// Added by Xi
    var $dbdriver = 'pdo';
    var $_escape_char = ''; // The character used to escape with - not needed for SQLite
    var $conn_id;
    var $_random_keyword = ' Random()'; // database specific random keyword
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
// whether SET NAMES must be used to set the character set
    var $use_set_names;

    /**
     * Non-persistent database connection
     *
     * @access	private called by the base class
     * @return	resource
     */
    function db_connect() {
        $conn_id = false;
        try {
            $conn_id = new PDO($this->database, $this->username, $this->password);
            log_message('debug', "PDO driver connecting " . $this->database);
        } catch (PDOException $e) {
            log_message('debug', 'merde');
            log_message('error', $e->getMessage());
            if ($this->db_debug) {
                $this->display_error($e->getMessage(), '', TRUE);
            }
        }
        log_message('debug', print_r($conn_id, true));
        if ($conn_id) {
            log_message('debug', 'PDO driver connection ok');
        }

        // Added by Xi
        $this->conn_id = $conn_id;

        return $conn_id;
    }

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
        return "PRAGMA table_info('" . $this->_protect_identifiers($table, TRUE, NULL, FALSE) . "') ";
    }

    // --------------------------------------------------------------------

    /**
     * Persistent database connection
     *
     * @access	private, called by the base class
     * @return	resource
     */
    function db_pconnect() {
        // For SQLite architecture can not enable persistent connection
        return $this->db_connect();

        /*
          $conn_id = false;
          try {
          $conn_id = new PDO ($this->database, $this->username, $this->password, array(PDO::ATTR_PERSISTENT => true) );
          } catch (PDOException $e) {
          log_message('error', $e->getMessage());
          if ($this->db_debug)
          {
          $this->display_error($e->getMessage(), '', TRUE);
          }
          }

          // Added by Xi
          $this->conn_id=$conn_id;

          return $conn_id;
         */
    }

    // --------------------------------------------------------------------

    /**
     * Select the database
     *
     * @access	private called by the base class
     * @return	resource
     */
    function db_select() {
        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Execute the query
     *
     * @access	private, called by the base class
     * @param	string	an SQL query
     * @return	resource
     */
    function _execute($sql) {
        $sql = $this->_prep_query($sql);
        log_message('debug', 'SQL : ' . $sql);
        return @$this->conn_id->query($sql);
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
    function &_prep_query($sql) {
        return $sql;
    }

// Modify by Xi
    /**
     * "Smart" Escape String
     *
     * Escapes data based on type
     * Sets boolean and null types
     *
     * @access	public
     * @param	string
     * @return	integer
     */
    function escape($str) {
        switch (gettype($str)) {
            case 'string' : $str = "'" . $this->escape_str($str) . "'";
                break;
            case 'boolean' : $str = ($str === FALSE) ? 0 : 1;
                break;
            default : $str = ($str === NULL) ? 'NULL' : $str;
                break;
        }

        return $str;
    }

    // --------------------------------------------------------------------

    /**
     * Escape String
     *
     * @access	public
     * @param	string
     * @return	string
     */
    /*
      function escape_str($str)
      {
      if (get_magic_quotes_gpc())
      {
      $str = stripslashes($str);
      }
      return $this->conn_id->quote($str);
      }
     */
    // --------------------------------------------------------------------
// Added by Xi
    /**
     * Escape String         
     *         
     * @access      public         
     * @param       string         
     * @return      string         
     */
    function escape_str($str) {
        return sqlite_escape_string($str);
    }

// Added by Xi
    /**     * Escape the SQL Identifiers * 
     * This function escapes column and table names * 
     * @accessprivate 
     * @paramstring 
     * @returnstring */
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

// Add by Xi
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

        $this->simple_query('BEGIN TRANSACTION');
        return TRUE;
    }

    // --------------------------------------------------------------------
// Add by Xi
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
        return TRUE;
    }

    // --------------------------------------------------------------------
// Add by Xi
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
        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Close DB Connection
     *
     * @access	public
     * @param	resource
     * @return	void
     */
    function destroy($conn_id) {
        $conn_id = null;
    }

    // --------------------------------------------------------------------

    /**
     * Insert ID
     *
     * @access	public
     * @return	integer
     */
    function insert_id() {
        return @$this->conn_id->lastInsertId();
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
        if ($table == '')
            return '0';

        $query = $this->query("SELECT COUNT(*) AS numrows FROM `" . $table . "`");

        if ($query->num_rows() == 0)
            return '0';

        $row = $query->row();
        return $row->numrows;
    }

    // --------------------------------------------------------------------

    /**
     * The error message string
     *
     * @access	private
     * @return	string
     */
    function _error_message() {
        $infos = $this->conn_id->errorInfo();
        return $infos[2];
    }

    // --------------------------------------------------------------------

    /**
     * The error message number
     *
     * @access	private
     * @return	integer
     */
    function _error_number() {
        $infos = $this->conn_id->errorInfo();
        return $infos[1];
    }

    // --------------------------------------------------------------------

    /**
     * Version number query string
     *
     * @access	public
     * @return	string
     */
    function version() {
        return $this->conn_id->getAttribute(constant("PDO::ATTR_SERVER_VERSION"));
    }

    // --------------------------------------------------------------------

    /**
     * Escape Table Name
     *
     * This function adds backticks if the table name has a period
     * in it. Some DBs will get cranky unless periods are escaped
     *
     * @access	public
     * @param	string	the table name
     * @return	string
     */
    function escape_table($table) {
        if (stristr($table, '.')) {
            $table = preg_replace("/\./", "`.`", $table);
        }

        return $table;
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
        $sql = "SELECT * FROM " . $this->escape_table($table) . " LIMIT 1";
        $query = $this->query($sql);
        return $query->field_data();
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
        return "INSERT INTO " . $this->escape_table($table) . " (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ")";
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
     * @return	string
     */
    function _update($table, $values, $where) {
        foreach ($values as $key => $val) {
            $valstr[] = $key . " = " . $val;
        }

        return "UPDATE " . $this->escape_table($table) . " SET " . implode(', ', $valstr) . " WHERE " . implode(" ", $where);
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
     * @return	string
     */
    function _delete($table, $where) {
        return "DELETE FROM " . $this->escape_table($table) . " WHERE " . implode(" ", $where);
    }

    // --------------------------------------------------------------------

    /**
     * Show table query
     *
     * Generates a platform-specific query string so that the table names can be fetched
     *
     * @access	public
     * @return	string
     */
    function _show_tables() {
        return "SELECT name from sqlite_master WHERE type='table'";
    }

    // --------------------------------------------------------------------

    /**
     * Show columnn query
     *
     * Generates a platform-specific query string so that the column names can be fetched
     *
     * @access	public
     * @param	string	the table name
     * @return	string
     */
    function _show_columns($table = '') {
        // Not supported
        return FALSE;
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

// Commented by Xi
    /**
     * COPY FROM sqlite_driver.php
     * Protect Identifiers ... contributed/requested by CodeIgniter user: quindo
     *
     * This function adds backticks if appropriate based on db type
     *
     * @access  private
     * @param   mixed   the item to escape
     * @param   boolean only affect the first word
     * @return  mixed   the item with backticks
     */
    /*
      function _protect_identifiers($item, $first_word_only = FALSE)
      {
      if (is_array($item))
      {
      $escaped_array = array();

      foreach($item as $k=>$v)
      {
      $escaped_array[$this->_protect_identifiers($k)] = $this->_protect_identifiers($v, $first_word_only);
      }

      return $escaped_array;
      }

      // This function may get "item1 item2" as a string, and so
      // we may need "item1 item2" and not "item1 item2"
      if (ctype_alnum($item) === FALSE)
      {
      if (strpos($item, '.') !== FALSE)
      {
      $aliased_tables = implode(".",$this->ar_aliased_tables).'.';
      $table_name =  substr($item, 0, strpos($item, '.')+1);
      $item = (strpos($aliased_tables, $table_name) !== FALSE) ? $item = $item : $this->dbprefix.$item;
      }

      // This function may get "field >= 1", and need it to return "field >= 1"
      $lbound = ($first_word_only === TRUE) ? '' : '|\s|\(';

      $item = preg_replace('/(^'.$lbound.')([\w\d\-\_]+?)(\s|\)|$)/iS', '$1$2$3', $item);
      }
      else
      {
      return "{$item}";
      }

      $exceptions = array('AS', '/', '-', '%', '+', '*');

      foreach ($exceptions as $exception)
      {
      if (stristr($item, " {$exception} ") !== FALSE)
      {
      $item = preg_replace('/ ('.preg_quote($exception).') /i', ' $1 ', $item);
      }
      }
      return $item;
      }
     */

    /**
     * From Tables ... contributed/requested by CodeIgniter user: quindo
     *
     * This function implicitly groups FROM tables so there is no confusion
     * about operator precedence in harmony with SQL standards
     *
     * @access  public
     * @param   type
     * @return  type
     */
    function _from_tables($tables) {
        if (!is_array($tables)) {
            $tables = array($tables);
        }

        return implode(', ', $tables);
    }

// --------------------------------------------------------------------

    /**
     * Set client character set
     * contributed/requested by CodeIgniter user:  jtiai
     *
     * @access    public
     * @param    string
     * @param    string
     * @return    resource
     */
    function db_set_charset($charset, $collation) {
        // TODO - add support if needed
        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Close DB Connection
     *
     * @access    public
     * @param    resource
     * @return    void
     */
    function _close($conn_id) {
        // Do nothing since PDO don't have close
    }

    /**
     * List table query    
     *    
     * Generates a platform-specific query string so that the table names can be fetched    
     *    
     * @access      private    
     * @param       boolean    
     * @return      string    
     */
    function _list_tables($prefix_limit = FALSE) {
        $sql = "SELECT name from sqlite_master WHERE type='table'";

        if ($prefix_limit !== FALSE AND $this->dbprefix != '') {
            $sql .= " AND 'name' LIKE '" . $this->dbprefix . "%'";
        }

        return $sql;
    }

}

/**
 * PDO Result Class
 *
 * This class extends the parent result class: CI_DB_result
 *
 * @category	Database
 * @author		Dready
 * @link			http://dready.jexiste.fr/dotclear/
 */
class CI_DB_sqlite3_result extends CI_DB_result {

    var $pdo_results = '';
    var $pdo_index = 0;

    /**
     * Number of rows in the result set
     *
     * pfff... that's ugly !!!!!!!
     *
     * PHP manual for PDO tell us about nom_rows :
     * "For most databases, PDOStatement::rowCount() does not return the number of rows affected by
     * a SELECT statement. Instead, use PDO::query() to issue a SELECT COUNT(*) statement with the
     * same predicates as your intended SELECT statement, then use PDOStatement::fetchColumn() to
     * retrieve the number of rows that will be returned.
     *
     * which means
     * 1/ select count(*) as c from table where $where
     * => numrows
     * 2/ select * from table where $where
     * => treatment
     *
     * Holy cow !
     *
     * @access	public
     * @return	integer
     */
    function num_rows() {
        if (!$this->pdo_results) {
            $this->pdo_results = $this->result_id->fetchAll(PDO::FETCH_ASSOC);
        }
        return sizeof($this->pdo_results);
    }

    // --------------------------------------------------------------------

    /**
     * Number of fields in the result set
     *
     * @access	public
     * @return	integer
     */
    function num_fields() {
        if (is_array($this->pdo_results)) {
            return sizeof($this->pdo_results[$this->pdo_index]);
        } else {
            return $this->result_id->columnCount();
        }
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
    /* 	function field_data()
      {
      $retval = array();
      for ($i = 0; $i < $this->num_fields(); $i++)
      {
      $F 				= new CI_DB_field();
      $F->name 		= sqlite_field_name($this->result_id, $i);
      $F->type 		= 'varchar';
      $F->max_length	= 0;
      $F->primary_key = 0;
      $F->default		= '';

      $retval[] = $F;
      }

      return $retval;
      } */

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
        if (is_array($this->pdo_results)) {
            $i = $this->pdo_index;
            $this->pdo_index++;
            if (isset($this->pdo_results[$i]))
                return $this->pdo_results[$i];
            return null;
        }
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
        if (is_array($this->pdo_results)) {
            $i = $this->pdo_index;
            $this->pdo_index++;
            if (isset($this->pdo_results[$i])) {
                $back = new stdClass();
                foreach ($this->pdo_results[$i] as $key => $val) {
                    $back->$key = $val;
                }
                return $back;
            }
            return null;
        }
        return $this->result_id->fetch(PDO::FETCH_OBJ);
    }

}

/* End of file sqlite3.php */

//####################modules/cache-drivers/driver.php####################{


/*
 * khoaofgod@yahoo.com
 * Website: http://www.phpfastcache.com
 * Example at our website, any bugs, problems, please visit http://www.codehelper.io
 */


interface phpfastcache_driver {
    /*
     * Check if this Cache driver is available for server or not
     */
     function __construct($option = array());

     function checkdriver();

    /*
     * SET
     * set a obj to cache
     */
     function driver_set($keyword, $value = "", $time = 300, $option = array() );

    /*
     * GET
     * return null or value of cache
     */
     function driver_get($keyword, $option = array());

    /*
     * Stats
     * Show stats of caching
     * Return array ("info","size","data")
     */
     function driver_stats($option = array());

    /*
     * Delete
     * Delete a cache
     */
     function driver_delete($keyword, $option = array());

    /*
     * clean
     * Clean up whole cache
     */
     function driver_clean($option = array());





}
//####################modules/cache-drivers/drivers/apc.php####################{


/*
 * khoaofgod@yahoo.com
 * Website: http://www.phpfastcache.com
 * Example at our website, any bugs, problems, please visit http://www.codehelper.io
 */


class phpfastcache_apc extends phpFastCache implements phpfastcache_driver {
    function checkdriver() {
        // Check apc
        if(extension_loaded('apc') && ini_get('apc.enabled'))
        {
            return true;
        } else {
            return false;
        }
    }

    function __construct($option = array()) {
        $this->setOption($option);
        if(!$this->checkdriver() && !isset($option['skipError'])) {
            throw new Exception("Can't use this driver for your website!");
        }
    }

    function driver_set($keyword, $value = "", $time = 300, $option = array() ) {
        if(isset($option['skipExisting']) && $option['skipExisting'] == true) {
            return apc_add($keyword,$value,$time);
        } else {
            return apc_store($keyword,$value,$time);
        }
    }

    function driver_get($keyword, $option = array()) {
        // return null if no caching
        // return value if in caching
        $data = apc_fetch($keyword,$bo);
        if($bo === false) {
            return null;
        }
        return $data;

    }

    function driver_delete($keyword, $option = array()) {
        return apc_delete($keyword);
    }

    function driver_stats($option = array()) {
        $res = array(
            "info" => "",
            "size"  => "",
            "data"  =>  "",
        );

        try {
            $res['data'] = apc_cache_info("user");
        } catch(Exception $e) {
            $res['data'] =  array();
        }

        return $res;
    }

    function driver_clean($option = array()) {
        @apc_clear_cache();
        @apc_clear_cache("user");
    }

    function driver_isExisting($keyword) {
        if(apc_exists($keyword)) {
            return true;
        } else {
            return false;
        }
    }





}
//####################modules/cache-drivers/drivers/files.php####################{

/*
 * khoaofgod@yahoo.com
 * Website: http://www.phpfastcache.com
 * Example at our website, any bugs, problems, please visit http://www.codehelper.io
 */

class phpfastcache_files extends  phpFastCache implements phpfastcache_driver  {

    function checkdriver() {
        if(is_writable($this->getPath())) {
            return true;
        } else {

        }
        return false;
    }

    /*
     * Init Cache Path
     */
    function __construct($option = array()) {

        $this->setOption($option);
        $this->getPath();

        if(!$this->checkdriver() && !isset($option['skipError'])) {
            throw new Exception("Can't use this driver for your website!");
        }

    }

    /*
     * Return $FILE FULL PATH
     */
    private function getFilePath($keyword, $skip = false) {
        $path = $this->getPath();
        $code = md5($keyword);
        $folder = substr($code,0,2);
        $path = $path."/".$folder;
        /*
         * Skip Create Sub Folders;
         */
        if($skip == false) {
            if(!file_exists($path)) {
                if(!@mkdir($path,0777)) {
                    throw new Exception("PLEASE CHMOD ".$this->getPath()." - 0777 OR ANY WRITABLE PERMISSION!",92);
                }

            } elseif(!is_writeable($path)) {
                @chmod($path,0777);
            }
        }

        $file_path = $path."/".$code.".txt";
        return $file_path;
    }


    function driver_set($keyword, $value = "", $time = 300, $option = array() ) {
        $file_path = $this->getFilePath($keyword);
      //  echo "<br>DEBUG SET: ".$keyword." - ".$value." - ".$time."<br>";
        $data = $this->encode($value);

        $toWrite = true;
        /*
         * Skip if Existing Caching in Options
         */
        if(isset($option['skipExisting']) && $option['skipExisting'] == true && file_exists($file_path)) {
            $content = $this->readfile($file_path);
            $old = $this->decode($content);
            $toWrite = false;
            if($this->isExpired($old)) {
                $toWrite = true;
            }
        }

        if($toWrite == true) {
                $f = fopen($file_path,"w+");
                fwrite($f,$data);
                fclose($f);
        }
    }




    function driver_get($keyword, $option = array()) {

        $file_path = $this->getFilePath($keyword);
        if(!file_exists($file_path)) {
            return null;
        }

        $content = $this->readfile($file_path);
        $object = $this->decode($content);
        if($this->isExpired($object)) {
            @unlink($file_path);
            $this->auto_clean_expired();
            return null;
        }

        return $object;
    }

    function driver_delete($keyword, $option = array()) {
        $file_path = $this->getFilePath($keyword,true);
        if(@unlink($file_path)) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * Return total cache size + auto removed expired files
     */
    function driver_stats($option = array()) {
        $res = array(
            "info"  =>  "",
            "size"  =>  "",
            "data"  =>  "",
        );

        $path = $this->getPath();
        $dir = @opendir($path);
        if(!$dir) {
            throw new Exception("Can't read PATH:".$path,94);
        }

        $total = 0;
        $removed = 0;
        while($file=readdir($dir)) {
            if($file!="." && $file!=".." && is_dir($path."/".$file)) {
                // read sub dir
                $subdir = @opendir($path."/".$file);
                if(!$subdir) {
                    throw new Exception("Can't read path:".$path."/".$file,93);
                }

                while($f = readdir($subdir)) {
                    if($f!="." && $f!="..") {
                        $file_path = $path."/".$file."/".$f;
                        $size = filesize($file_path);
                        $object = $this->decode($this->readfile($file_path));
                        if($this->isExpired($object)) {
                            unlink($file_path);
                            $removed = $removed + $size;
                        }
                        $total = $total + $size;
                    }
                } // end read subdir
            } // end if
       } // end while

       $res['size']  = $total - $removed;
       $res['info'] = array(
                "Total" => $total,
                "Removed"   => $removed,
                "Current"   => $res['size'],
       );
       return $res;
    }

    function auto_clean_expired() {
        $autoclean = $this->get("keyword_clean_up_driver_files");
        if($autoclean == null) {
            $this->set("keyword_clean_up_driver_files",3600*24);
            $res = $this->stats();
        }
    }

    function driver_clean($option = array()) {

        $path = $this->getPath();
        $dir = @opendir($path);
        if(!$dir) {
            throw new Exception("Can't read PATH:".$path,94);
        }

        while($file=readdir($dir)) {
            if($file!="." && $file!=".." && is_dir($path."/".$file)) {
                // read sub dir
                $subdir = @opendir($path."/".$file);
                if(!$subdir) {
                    throw new Exception("Can't read path:".$path."/".$file,93);
                }

                while($f = readdir($subdir)) {
                    if($f!="." && $f!="..") {
                        $file_path = $path."/".$file."/".$f;
                        unlink($file_path);
                    }
                } // end read subdir
            } // end if
        } // end while


    }


    function driver_isExisting($keyword) {
        $file_path = $this->getFilePath($keyword,true);
        if(!file_exists($file_path)) {
            return false;
        } else {
            // check expired or not
            $value = $this->get($keyword);
            if($value == null) {
                return false;
            } else {
                return true;
            }
        }
    }

    function isExpired($object) {

        if(isset($object['expired_time']) && @date("U") >= $object['expired_time']) {
            return true;
        } else {
            return false;
        }
    }




}
//####################modules/cache-drivers/drivers/memcache.php####################{



/*
 * khoaofgod@yahoo.com
 * Website: http://www.phpfastcache.com
 * Example at our website, any bugs, problems, please visit http://www.codehelper.io
 */


class phpfastcache_memcache extends phpFastCache implements phpfastcache_driver {

    var $instant;

    function checkdriver() {
        // Check memcache
        if(function_exists("memcache_connect")) {
            return true;
        }
        return false;
    }

    function __construct($option = array()) {
        $this->setOption($option);
        if(!$this->checkdriver() && !isset($option['skipError'])) {
            throw new Exception("Can't use this driver for your website!");
        }
        if ($this->checkdriver() && !is_object($this->instant)) {
            $this->instant = new Memcache();
        }
    }

    function connectServer() {
        $server = $this->option['server'];
        if(count($server) < 1) {
            $server = array(
                array("127.0.0.1",11211),
            );
        }

        foreach($server as $s) {
            $name = $s[0]."_".$s[1];
            if(!isset($this->checked[$name])) {
                $this->instant->addserver($s[0],$s[1]);
                $this->checked[$name] = 1;
            }

        }
    }

    function driver_set($keyword, $value = "", $time = 300, $option = array() ) {
        $this->connectServer();
        if(isset($option['skipExisting']) && $option['skipExisting'] == true) {
            return $this->instant->add($keyword, $value, false, $time );

        } else {
            return $this->instant->set($keyword, $value, false, $time );
        }

    }

    function driver_get($keyword, $option = array()) {
        $this->connectServer();
        // return null if no caching
        // return value if in caching
        $x = $this->instant->get($keyword);
        if($x == false) {
            return null;
        } else {
            return $x;
        }
    }

    function driver_delete($keyword, $option = array()) {
        $this->connectServer();
         $this->instant->delete($keyword);
    }

    function driver_stats($option = array()) {
        $this->connectServer();
        $res = array(
            "info"  => "",
            "size"  =>  "",
            "data"  => $this->instant->getStats(),
        );

        return $res;

    }

    function driver_clean($option = array()) {
        $this->connectServer();
        $this->instant->flush();
    }

    function driver_isExisting($keyword) {
        $this->connectServer();
        $x = $this->get($keyword);
        if($x == null) {
            return false;
        } else {
            return true;
        }
    }



}
//####################modules/cache-drivers/drivers/memcached.php####################{


/*
 * khoaofgod@yahoo.com
 * Website: http://www.phpfastcache.com
 * Example at our website, any bugs, problems, please visit http://www.codehelper.io
 */

class phpfastcache_memcached extends phpFastCache implements phpfastcache_driver {

    var $instant;

    function checkdriver() {
        if (class_exists("Memcached",FALSE)) {
            return true;
        }
        return false;
    }

    function __construct($option = array()) {
        $this->setOption($option);
        if (!$this->checkdriver() && !isset($option['skipError'])) {
            throw new Exception("Can't use this driver for your website!");
        }

        if ($this->checkdriver() && !is_object($this->instant)) {
            $this->instant = new Memcached();
        }
    }

    function connectServer() {
        $s = $this->option['server'];
        if (count($s) < 1) {
            $s = array(
                array("127.0.0.1", 11211, 100),
            );
        }

        foreach ($s as $server) {
            $name = isset($server[0]) ? $server[0] : "127.0.0.1";
            $port = isset($server[1]) ? $server[1] : 11211;
            $sharing = isset($server[2]) ? $server[2] : 0;
            $checked = $name . "_" . $port;
            if (!isset($this->checked[$checked])) {
                if ($sharing > 0) {
                    $this->instant->addServer($name, $port, $sharing);
                } else {
                    $this->instant->addServer($name, $port);
                }
                $this->checked[$checked] = 1;
            }
        }
    }

    function driver_set($keyword, $value = "", $time = 300, $option = array()) {
        $this->connectServer();
        if (isset($option['isExisting']) && $option['isExisting'] == true) {
            return $this->instant->add($keyword, $value, time() + $time);
        } else {
            return $this->instant->set($keyword, $value, time() + $time);
        }
    }

    function driver_get($keyword, $option = array()) {
        // return null if no caching
        // return value if in caching
        $this->connectServer();
        $x = $this->instant->get($keyword);
        if ($x == false) {
            return null;
        } else {
            return $x;
        }
    }

    function driver_delete($keyword, $option = array()) {
        $this->connectServer();
        $this->instant->delete($keyword);
    }

    function driver_stats($option = array()) {
        $this->connectServer();
        $res = array(
            "info" => "",
            "size" => "",
            "data" => $this->instant->getStats(),
        );

        return $res;
    }

    function driver_clean($option = array()) {
        $this->connectServer();
        $this->instant->flush();
    }

    function driver_isExisting($keyword) {
        $this->connectServer();
        $x = $this->get($keyword);
        if ($x == null) {
            return false;
        } else {
            return true;
        }
    }

}
//####################modules/cache-drivers/drivers/sqlite.php####################{


/*
 * khoaofgod@yahoo.com
 * Website: http://www.phpfastcache.com
 * Example at our website, any bugs, problems, please visit http://www.codehelper.io
 */

class phpfastcache_sqlite extends phpFastCache implements phpfastcache_driver {

    var $max_size = 10; // 10 mb
    var $instant = array();
    var $indexing = NULL;
    var $path = "";
    var $currentDB = 1;

    /*
     * INIT NEW DB
     */

    function initDB(PDO $db) {
        $db->exec('drop table if exists "caching"');
        $db->exec('CREATE TABLE "caching" ("id" INTEGER PRIMARY KEY AUTOINCREMENT, "keyword" VARCHAR UNIQUE, "object" BLOB, "exp" INTEGER)');
        $db->exec('CREATE UNIQUE INDEX "cleaup" ON "caching" ("keyword","exp")');
        $db->exec('CREATE INDEX "exp" ON "caching" ("exp")');
        $db->exec('CREATE UNIQUE INDEX "keyword" ON "caching" ("keyword")');
    }

    /*
     * INIT Indexing DB
     */

    function initIndexing(PDO $db) {

        // delete everything before reset indexing
        $dir = opendir($this->path);
        while ($file = readdir($dir)) {
            if ($file != "." && $file != ".." && $file != "indexing" && $file != "dbfastcache") {
                unlink($this->path . "/" . $file);
            }
        }

        $db->exec('drop table if exists "balancing"');
        $db->exec('CREATE TABLE "balancing" ("keyword" VARCHAR PRIMARY KEY NOT NULL UNIQUE, "db" INTEGER)');
        $db->exec('CREATE INDEX "db" ON "balancing" ("db")');
        $db->exec('CREATE UNIQUE INDEX "lookup" ON "balacing" ("keyword")');
    }

    /*
     * INIT Instant DB
     * Return Database of Keyword
     */

    function indexing($keyword) {
        if ($this->indexing == NULL) {
            $createTable = false;
            if (!file_exists($this->path . "/indexing")) {
                $createTable = true;
            }

            $PDO = new PDO("sqlite:" . $this->path . "/indexing");
            $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            if ($createTable == true) {
                $this->initIndexing($PDO);
            }
            $this->indexing = $PDO;
            unset($PDO);

            $stm = $this->indexing->prepare("SELECT MAX(`db`) as `db` FROM `balancing`");
            $stm->execute();
            $row = $stm->fetch(PDO::FETCH_ASSOC);
            if (!isset($row['db'])) {
                $db = 1;
            } elseif ($row['db'] <= 1) {
                $db = 1;
            } else {
                $db = $row['db'];
            }

            // check file size

            $size = file_exists($this->path . "/db" . $db) ? filesize($this->path . "/db" . $db) : 1;
            $size = round($size / 1024 / 1024, 1);


            if ($size > $this->max_size) {
                $db = $db + 1;
            }
            $this->currentDB = $db;
        }

        // look for keyword
        $stm = $this->indexing->prepare("SELECT * FROM `balancing` WHERE `keyword`=:keyword LIMIT 1");
        $stm->execute(array(
            ":keyword" => $keyword
        ));
        $row = $stm->fetch(PDO::FETCH_ASSOC);
        if (isset($row['db']) && $row['db'] != "") {
            $db = $row['db'];
        } else {
            /*
             * Insert new to Indexing
             */
            $db = $this->currentDB;
            $stm = $this->indexing->prepare("INSERT INTO `balancing` (`keyword`,`db`) VALUES(:keyword, :db)");
            $stm->execute(array(
                ":keyword" => $keyword,
                ":db" => $db,
            ));
        }

        return $db;
    }

    function db($keyword, $reset = false) {
        /*
         * Default is fastcache
         */
        $instant = $this->indexing($keyword);

        /*
         * init instant
         */
        if (!isset($this->instant[$instant])) {
            // check DB Files ready or not
            $createTable = false;
            if (!file_exists($this->path . "/db" . $instant) || $reset == true) {
                $createTable = true;
            }
            $PDO = new PDO("sqlite:" . $this->path . "/db" . $instant);
            $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            if ($createTable == true) {
                $this->initDB($PDO);
            }

            $this->instant[$instant] = $PDO;
            unset($PDO);
        }


        return $this->instant[$instant];
    }

    function checkdriver() {
        if (extension_loaded('pdo_sqlite') && is_writeable($this->getPath())) {
            return true;
        }
        return false;
    }

    /*
     * Init Main Database & Sub Database
     */

    function __construct($option = array()) {
        /*
         * init the path
         */
        $this->setOption($option);
        if (!$this->checkdriver() && !isset($option['skipError'])) {
            throw new Exception("Can't use this driver for your website!");
        }

        if ($option['storage'] == 'sqlite' && !file_exists($this->getPath() . "/sqlite")) {
            if (!@mkdir($this->getPath() . "/sqlite", 0777)) {
                die("Sorry, Please CHMOD 0777 for this path: " . $this->getPath());
            }
        }
        $this->path = $this->getPath() . "/sqlite";
    }

    function driver_set($keyword, $value = "", $time = 300, $option = array()) {
        $skipExisting = isset($option['skipExisting']) ? $option['skipExisting'] : false;
        $toWrite = true;

        // check in cache first
        $in_cache = $this->get($keyword, $option);

        if ($skipExisting == true) {
            if ($in_cache == null) {
                $toWrite = true;
            } else {
                $toWrite = false;
            }
        }

        if ($toWrite == true) {
            try {
                $stm = $this->db($keyword)->prepare("INSERT OR REPLACE INTO `caching` (`keyword`,`object`,`exp`) values(:keyword,:object,:exp)");
                $stm->execute(array(
                    ":keyword" => $keyword,
                    ":object" => $this->encode($value),
                    ":exp" => @date("U") + (Int) $time,
                ));

                return true;
            } catch (PDOException $e) {
                $stm = $this->db($keyword, true)->prepare("INSERT OR REPLACE INTO `caching` (`keyword`,`object`,`exp`) values(:keyword,:object,:exp)");
                $stm->execute(array(
                    ":keyword" => $keyword,
                    ":object" => $this->encode($value),
                    ":exp" => @date("U") + (Int) $time,
                ));
            }
        }

        return false;
    }

    function driver_get($keyword, $option = array()) {
        // return null if no caching
        // return value if in caching
        try {
            $stm = $this->db($keyword)->prepare("SELECT * FROM `caching` WHERE `keyword`=:keyword LIMIT 1");
            $stm->execute(array(
                ":keyword" => $keyword
            ));
            $row = $stm->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {

            $stm = $this->db($keyword, true)->prepare("SELECT * FROM `caching` WHERE `keyword`=:keyword LIMIT 1");
            $stm->execute(array(
                ":keyword" => $keyword
            ));
            $row = $stm->fetch(PDO::FETCH_ASSOC);
        }


        if ($this->isExpired($row)) {
            $this->deleteRow($row);
            return null;
        }



        if (isset($row['id'])) {
            $data = $this->decode($row['object']);
            return $data;
        }


        return null;
    }

    function isExpired($row) {
        if (isset($row['exp']) && @date("U") >= $row['exp']) {
            return true;
        }

        return false;
    }

    function deleteRow($row) {
        $stm = $this->db($row['keyword'])->prepare("DELETE FROM `caching` WHERE (`id`=:id) OR (`exp` <= :U) ");
        $stm->execute(array(
            ":id" => $row['id'],
            ":U" => @date("U"),
        ));
    }

    function driver_delete($keyword, $option = array()) {
        $stm = $this->db($keyword)->prepare("DELETE FROM `caching` WHERE (`keyword`=:keyword) OR (`exp` <= :U)");
        $stm->execute(array(
            ":keyword" => $keyword,
            ":U" => @date("U"),
        ));
    }

    function driver_stats($option = array()) {
        $res = array(
            "info" => "",
            "size" => "",
            "data" => "",
        );
        $total = 0;
        $optimized = 0;

        $dir = opendir($this->path);
        while ($file = readdir($dir)) {
            if ($file != "." && $file != "..") {
                $file_path = $this->path . "/" . $file;
                $size = filesize($file_path);
                $total = $total + $size;

                $PDO = new PDO("sqlite:" . $file_path);
                $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $stm = $PDO->prepare("DELETE FROM `caching` WHERE `exp` <= :U");
                $stm->execute(array(
                    ":U" => @date("U"),
                ));

                $PDO->exec("VACUUM;");
                $size = filesize($file_path);
                $optimized = $optimized + $size;
            }
        }
        $res['size'] = round($optimized / 1024 / 1024, 1);
        $res['info'] = array(
            "total" => round($total / 1024 / 1024, 1),
            "optimized" => round($optimized / 1024 / 1024, 1),
        );

        return $res;
    }

    function driver_clean($option = array()) {
        // delete everything before reset indexing
        $dir = opendir($this->path);
        while ($file = readdir($dir)) {
            if ($file != "." && $file != "..") {
                unlink($this->path . "/" . $file);
            }
        }
    }

    function driver_isExisting($keyword) {
        $stm = $this->db($keyword)->prepare("SELECT COUNT(`id`) as `total` FROM `caching` WHERE `keyword`=:keyword");
        $stm->execute(array(
            ":keyword" => $keyword
        ));
        $data = $stm->fetch(PDO::FETCH_ASSOC);
        if ($data['total'] >= 1) {
            return true;
        } else {
            return false;
        }
    }

}

//####################modules/cache-drivers/drivers/wincache.php####################{

/*
 * khoaofgod@yahoo.com
 * Website: http://www.phpfastcache.com
 * Example at our website, any bugs, problems, please visit http://www.codehelper.io
 */

class phpfastcache_wincache extends phpFastCache implements phpfastcache_driver  {

    function checkdriver() {
        if(extension_loaded('wincache') && function_exists("wincache_ucache_set"))
        {
            return true;
        }
        return false;
    }

    function __construct($option = array()) {
        $this->setOption($option);
        if(!$this->checkdriver() && !isset($option['skipError'])) {
            throw new Exception("Can't use this driver for your website!");
        }

    }

    function driver_set($keyword, $value = "", $time = 300, $option = array() ) {
        if(isset($option['skipExisting']) && $option['skipExisting'] == true) {
            return wincache_ucache_add($keyword, $value, $time);
        } else {
            return wincache_ucache_set($keyword, $value, $time);
        }
    }

    function driver_get($keyword, $option = array()) {
        // return null if no caching
        // return value if in caching

        $x = wincache_ucache_get($keyword,$suc);

        if($suc == false) {
            return null;
        } else {
            return $x;
        }
    }

    function driver_delete($keyword, $option = array()) {
        return wincache_ucache_delete($keyword);
    }

    function driver_stats($option = array()) {
        $res = array(
            "info"  =>  "",
            "size"  =>  "",
            "data"  =>  wincache_scache_info(),
        );
        return $res;
    }

    function driver_clean($option = array()) {
        wincache_ucache_clear();
        return true;
    }

    function driver_isExisting($keyword) {
        if(wincache_ucache_exists($keyword)) {
            return true;
        } else {
            return false;
        }
    }



}
//####################modules/cache-drivers/drivers/xcache.php####################{


/*
 * khoaofgod@yahoo.com
 * Website: http://www.phpfastcache.com
 * Example at our website, any bugs, problems, please visit http://www.codehelper.io
 */

class phpfastcache_xcache extends phpFastCache implements phpfastcache_driver  {

    function checkdriver() {
        // Check xcache
        if(extension_loaded('xcache') && function_exists("xcache_get"))
        {
           return true;
        }
        return false;

    }

    function __construct($option = array()) {
        $this->setOption($option);
        if(!$this->checkdriver() && !isset($option['skipError'])) {
            throw new Exception("Can't use this driver for your website!");
        }

    }

    function driver_set($keyword, $value = "", $time = 300, $option = array() ) {

        if(isset($option['skipExisting']) && $option['skipExisting'] == true) {
            if(!$this->isExisting($keyword)) {
                return xcache_set($keyword,$value,$time);
            }
        } else {
            return xcache_set($keyword,$value,$time);
        }
        return false;
    }

    function driver_get($keyword, $option = array()) {
        // return null if no caching
        // return value if in caching
        $data = xcache_get($keyword);
        if($data === false || $data == "") {
            return null;
        }
        return $data;
    }

    function driver_delete($keyword, $option = array()) {
        return xcache_unset($keyword);
    }

    function driver_stats($option = array()) {
        $res = array(
            "info"  =>  "",
            "size"  =>  "",
            "data"  =>  "",
        );

        try {
            $res['data'] = xcache_list(XC_TYPE_VAR,100);
        } catch(Exception $e) {
            $res['data'] = array();
        }
        return $res;
    }

    function driver_clean($option = array()) {
        $cnt = xcache_count(XC_TYPE_VAR);
        for ($i=0; $i < $cnt; $i++) {
            xcache_clear_cache(XC_TYPE_VAR, $i);
        }
        return true;
    }

    function driver_isExisting($keyword) {
        if(xcache_isset($keyword)) {
            return true;
        } else {
            return false;
        }
    }



}
//####################modules/cache-drivers/drivers/redis.php####################{


/*
 * khoaofgod@yahoo.com
 * Website: http://www.phpfastcache.com
 * Example at our website, any bugs, problems, please visit http://www.codehelper.io
 */

class phpfastcache_redis extends phpFastCache implements phpfastcache_driver {

    var $instant;

    function checkdriver() {
        // Check memcache
        if (class_exists("redis",FALSE)) {
            return true;
        }
        return false;
    }

    function __construct($option = array()) {
        $this->setOption($option);
        if (!$this->checkdriver() && !isset($option['skipError'])) {
            throw new Exception("Can't use this driver for your website!");
        }
        if ($this->checkdriver() && !is_object($this->instant)) {
            $this->instant = new Redis();
        }
    }

    function connectServer() {
        $config = $this->option['redis'];
        $this->instant = new Redis();
        if ($config['type'] == 'sock') {
            $this->instant->connect($config['sock']);
        } else {
            $this->instant->connect($config['host'], $config['port'], $config['timeout'], NULL, $config['retry']);
        }
        if (!is_null($config['password'])) {
            $this->instant->auth($config['password']);
        }
        if (!is_null($config['prefix'])) {
            if ($config['prefix']{strlen($config['prefix']) - 1} != ':') {
                $config['prefix'].=':';
            }
            $this->instant->setOption(Redis::OPT_PREFIX, $config['prefix']);
        }
    }

    function driver_set($keyword, $value = "", $time = 300, $option = array()) {
        $this->connectServer();
        $value = serialize($value);
        return ($time) ? $this->instant->setex($keyword, $time, $value) : $this->instant->set($keyword, $value);
    }

    function driver_get($keyword, $option = array()) {
        $this->connectServer();
        // return null if no caching
        // return value if in caching
        if (($data = $this->instant->get($keyword))) {
            return @unserialize($data);
        } else {
            return null;
        }
    }

    function driver_delete($keyword, $option = array()) {
        $this->connectServer();
        $this->instant->delete($keyword);
    }

    function driver_stats($option = array()) {
        $this->connectServer();
        $res = array(
            "info" => "",
            "size" => "",
            "data" => $this->instant->info(),
        );

        return $res;
    }

    function driver_clean($option = array()) {
        $this->connectServer();
        $this->instant->flushDB();
    }

    function driver_isExisting($keyword) {
        $this->connectServer();
        return $this->instant->exists($keyword);
    }

}

//####################modules/cache-drivers/phpfastcache.php####################{


/*
 * khoaofgod@yahoo.com
 * Website: http://www.phpfastcache.com
 * Example at our website, any bugs, problems, please visit http://www.codehelper.io
 */



// short function
if (!function_exists("__c")) {

    function __c($storage = "", $option = array()) {
        return phpfastcache($storage, $option);
    }

}

// main function
if (!function_exists("phpFastCache")) {

    function phpFastCache($storage = "", $option = array()) {
        if (!isset(phpFastCache_instances::$instances[$storage])) {
            phpFastCache_instances::$instances[$storage] = new phpFastCache($storage, $option);
        }
        return phpFastCache_instances::$instances[$storage];
    }

}

class phpFastCache_instances {

    public static $instances = array();

}

// main class
class phpFastCache {

    public static $storage = "auto";
    public static $config = array(
        "storage" => "auto",
        "fallback" => array(
            "example" => "files",
        ),
        "securityKey" => "",
        "htaccess" => true,
        "path" => "",
        "server" => array(
            array("127.0.0.1", 11211, 1),
        //  array("new.host.ip",11211,1),
        ),
        "extensions" => array(),
    );
    var $drivers = array('apc', 'sqlite', 'files', 'memcached', 'redis', 'wincache', 'xcache', 'memcache');
    var $tmp = array();
    var $checked = array(
        "path" => false,
        "fallback" => false,
        "hook" => false,
    );
    var $is_driver = false;
    var $driver = NULL;
    // default options, this will be merge to Driver's Options
    var $option = array(
        "path" => "", // path for cache folder
        "htaccess" => null, // auto create htaccess
        "securityKey" => '', // Key Folder, Setup Per Domain will good.
        "system" => array(),
        "storage" => "",
        "cachePath" => "",
    );

    /*
     * Basic Method
     */

    function set($keyword, $value = "", $time = 300, $option = array()) {
        $object = array(
            "value" => $value,
            "write_time" => @date("U"),
            "expired_in" => $time,
            "expired_time" => @date("U") + (Int) $time,
        );
        if ($this->is_driver == true) {
            return $this->driver_set($keyword, $object, $time, $option);
        } else {
            return $this->driver->driver_set($keyword, $object, $time, $option);
        }
    }

    function get($keyword, $option = array()) {
        if ($this->is_driver == true) {
            $object = $this->driver_get($keyword, $option);
        } else {
            $object = $this->driver->driver_get($keyword, $option);
        }

        if ($object == null) {
            return null;
        }
        return $object['value'];
    }

    function getInfo($keyword, $option = array()) {
        if ($this->is_driver == true) {
            $object = $this->driver_get($keyword, $option);
        } else {
            $object = $this->driver->driver_get($keyword, $option);
        }

        if ($object == null) {
            return null;
        }
        return $object;
    }

    function delete($keyword, $option = array()) {
        if ($this->is_driver == true) {
            return $this->driver_delete($keyword, $option);
        } else {
            return $this->driver->driver_delete($keyword, $option);
        }
    }

    function stats($option = array()) {
        if ($this->is_driver == true) {
            return $this->driver_stats($option);
        } else {
            return $this->driver->driver_stats($option);
        }
    }

    function clean($option = array()) {
        if ($this->is_driver == true) {
            return $this->driver_clean($option);
        } else {
            return $this->driver->driver_clean($option);
        }
    }

    function isExisting($keyword) {
        if ($this->is_driver == true) {
            if (method_exists($this, "driver_isExisting")) {
                return $this->driver_isExisting($keyword);
            }
        } else {
            if (method_exists($this->driver, "driver_isExisting")) {
                return $this->driver->driver_isExisting($keyword);
            }
        }

        $data = $this->get($keyword);
        if ($data == null) {
            return false;
        } else {
            return true;
        }
    }

    function increment($keyword, $step = 1, $option = array()) {
        $object = $this->get($keyword);
        if ($object == null) {
            return false;
        } else {
            $value = (Int) $object['value'] + (Int) $step;
            $time = $object['expired_time'] - @date("U");
            $this->set($keyword, $value, $time, $option);
            return true;
        }
    }

    function decrement($keyword, $step = 1, $option = array()) {
        $object = $this->get($keyword);
        if ($object == null) {
            return false;
        } else {
            $value = (Int) $object['value'] - (Int) $step;
            $time = $object['expired_time'] - @date("U");
            $this->set($keyword, $value, $time, $option);
            return true;
        }
    }

    /*
     * Extend more time
     */

    function touch($keyword, $time = 300, $option = array()) {
        $object = $this->get($keyword);
        if ($object == null) {
            return false;
        } else {
            $value = $object['value'];
            $time = $object['expired_time'] - @date("U") + $time;
            $this->set($keyword, $value, $time, $option);
            return true;
        }
    }

    /*
     * Other Functions Built-int for phpFastCache since 1.3
     */

    public function setMulti($list = array()) {
        foreach ($list as $array) {
            $this->set($array[0], isset($array[1]) ? $array[1] : 300, isset($array[2]) ? $array[2] : array());
        }
    }

    public function getMulti($list = array()) {
        $res = array();
        foreach ($list as $array) {
            $name = $array[0];
            $res[$name] = $this->get($name, isset($array[1]) ? $array[1] : array());
        }
        return $res;
    }

    public function getInfoMulti($list = array()) {
        $res = array();
        foreach ($list as $array) {
            $name = $array[0];
            $res[$name] = $this->getInfo($name, isset($array[1]) ? $array[1] : array());
        }
        return $res;
    }

    public function deleteMulti($list = array()) {
        foreach ($list as $array) {
            $this->delete($array[0], isset($array[1]) ? $array[1] : array());
        }
    }

    public function isExistingMulti($list = array()) {
        $res = array();
        foreach ($list as $array) {
            $name = $array[0];
            $res[$name] = $this->isExisting($name);
        }
        return $res;
    }

    public function incrementMulti($list = array()) {
        $res = array();
        foreach ($list as $array) {
            $name = $array[0];
            $res[$name] = $this->increment($name, $array[1], isset($array[2]) ? $array[2] : array());
        }
        return $res;
    }

    public function decrementMulti($list = array()) {
        $res = array();
        foreach ($list as $array) {
            $name = $array[0];
            $res[$name] = $this->decrement($name, $array[1], isset($array[2]) ? $array[2] : array());
        }
        return $res;
    }

    public function touchMulti($list = array()) {
        $res = array();
        foreach ($list as $array) {
            $name = $array[0];
            $res[$name] = $this->touch($name, $array[1], isset($array[2]) ? $array[2] : array());
        }
        return $res;
    }

    /*
     * Begin Parent Classes;
     */

    public static function setup($name, $value = "") {
        if (!is_array($name)) {
            if ($name == "storage") {
                self::$storage = $value;
            }

            self::$config[$name] = $value;
        } else {
            foreach ($name as $n => $value) {
                self::setup($n, $value);
            }
        }
    }

    function __construct($storage = "", $option = array()) {
        $this->option = array_merge($this->option, self::$config, $option);
        if (!$this->isExistingDriver($storage) && isset(self::$config['fallback'][$storage])) {
            $storage = self::$config['fallback'][$storage];
        }

        if ($storage == "") {
            $storage = self::$storage;
            self::option("storage", $storage);
        } else {
            self::$storage = $storage;
        }

        $this->tmp['storage'] = $storage;

        if ($storage != "auto" && $storage != "" && $this->isExistingDriver($storage)) {
            $driver = "phpfastcache_" . $storage;
        } else {
            $storage = $this->autoDriver();
            self::$storage = $storage;
            $driver = "phpfastcache_" . $storage;
        }

        $this->option("storage", $storage);

//        if ($this->option['securityKey'] == "auto" || $this->option['securityKey'] == "") {
//            $this->option['securityKey'] = "cache.storage." . (isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'');
//        }


        $this->driver = new $driver($this->option);
        $this->driver->is_driver = true;
    }

    /*
     * For Auto Driver
     *
     */

    function autoDriver() {
        foreach ($this->drivers as $namex) {
            $class = "phpfastcache_" . $namex;
            $option = $this->option;
            $option['skipError'] = true;
            $_driver = new $class($option);
            $_driver->option = $option;
            if ($_driver->checkdriver()) {
                return $namex;
            }
        }
        $system = WoniuLoader::$system;
        foreach ($system['cache_drivers'] as $filepath) {
            $file = pathinfo($filepath, PATHINFO_BASENAME);
            $namex = str_replace(".php", "", $file);
            $clazz = "phpfastcache_" . $namex;
            $option = $this->option;
            $option['skipError'] = true;
            $_driver = new $clazz($option);
            $_driver->option = $option;
            if ($_driver->checkdriver()) {
                return $namex;
            }
        }
        return "";
    }

    function option($name, $value = null) {
        if ($value == null) {
            if (isset($this->option[$name])) {
                return $this->option[$name];
            } else {
                return null;
            }
        } else {

            if ($name == "path") {
                $this->checked['path'] = false;
                $this->driver->checked['path'] = false;
            }

            self::$config[$name] = $value;
            $this->option[$name] = $value;
            $this->driver->option[$name] = $this->option[$name];

            return $this;
        }
    }

    public function setOption($option = array()) {
        $this->option = array_merge($this->option, self::$config, $option);
        $this->checked['path'] = false;
    }

    function __get($name) {
        $this->driver->option = $this->option;
        return $this->driver->get($name);
    }

    function __set($name, $v) {
        $this->driver->option = $this->option;
        if (isset($v[1]) && is_numeric($v[1])) {
            return $this->driver->set($name, $v[0], $v[1], isset($v[2]) ? $v[2] : array() );
        } else {
            throw new Exception("Example ->$name = array('VALUE', 300);", 98);
        }
    }

    /*
     * Only require_once for the class u use.
     * Not use autoload default of PHP and don't need to load all classes as default
     */

    private function isExistingDriver($class) {
        $class = strtolower($class);
        foreach ($this->drivers as $namex) {
            $clazz = "phpfastcache_" . $namex;
            $option = $this->option;
            $option['skipError'] = true;
            $_driver = new $clazz($option);
            $_driver->option = $option;
            if ($_driver->checkdriver() && $class == $namex) {
                return true;
            }
        }
        $system = WoniuLoader::$system;
        foreach ($system['cache_drivers'] as $filepath) {
            $file = pathinfo($filepath, PATHINFO_BASENAME);
            $namex = str_replace(".php", "", $file);
            $clazz = "phpfastcache_" . $namex;
            $option = $this->option;
            $option['skipError'] = true;
            $_driver = new $clazz($option);
            $_driver->option = $option;
            if ($_driver->checkdriver() && $class == $namex) {
                return true;
            }
        }
    }

    /*
     * return System Information
     */

    public function systemInfo() {
        if (count($this->option("system")) == 0) {


            $this->option['system']['driver'] = "files";

            $this->option['system']['drivers'] = array();

            $system = WoniuLoader::$system;

            foreach ($this->drivers as $namex) {
                $class = "phpfastcache_" . $namex;
                $this->option['skipError'] = true;
                $driver = new $class($this->option);
                $driver->option = $this->option;
                if ($driver->checkdriver()) {
                    $this->option['system']['drivers'][$namex] = true;
                    $this->option['system']['driver'] = $namex;
                } else {
                    $this->option['system']['drivers'][$namex] = false;
                }
            }

            foreach ($system['cache_drivers'] as $filepath) {
                $file = pathinfo($filepath, PATHINFO_BASENAME);
                $namex = str_replace(".php", "", $file);
                $class = "phpfastcache_" . $namex;
                $this->option['skipError'] = true;
                $driver = new $class($this->option);
                $driver->option = $this->option;
                if ($driver->checkdriver()) {
                    $this->option['system']['drivers'][$namex] = true;
                    $this->option['system']['driver'] = $namex;
                } else {
                    $this->option['system']['drivers'][$namex] = false;
                }
            }



            /*
             * PDO is highest priority with SQLite
             */
            if ($this->option['system']['drivers']['sqlite'] == true) {
                $this->option['system']['driver'] = "sqlite";
            }
        }
        $this->option("path", $this->getPath(TRUE));
        return $this->option;
    }

    public function getOS() {
        $os = array(
            "os" => PHP_OS,
            "php" => PHP_SAPI,
            "system" => php_uname(),
            "unique" => md5(php_uname() . PHP_OS . PHP_SAPI)
        );
        return $os;
    }

    /*
     * Object for Files & SQLite
     */

    public function encode($data) {
        return serialize($data);
    }

    public function decode($value) {
        $x = @unserialize($value);
        if ($x == false) {
            return $value;
        } else {
            return $x;
        }
    }

    /*
     * Auto Create .htaccess to protect cache folder
     */

    public function htaccessGen($path = "") {
//        if ($this->option("htaccess") == true) {
//
//            if (!file_exists($path . "/.htaccess")) {
//                //   echo "write me";
//                $html = "order deny, allow \r\n
//deny from all \r\n
//allow from 127.0.0.1";
//
//                $f = @fopen($path . "/.htaccess", "w+");
//                if (!$f) {
//                    throw new Exception("Can't create .htaccess", 97);
//                }
//                fwrite($f, $html);
//                fclose($f);
//            } else {
//                //   echo "got me";
//            }
//        }
    }

    /*
     * Check phpModules or CGI
     */

    public function isPHPModule() {
        if (PHP_SAPI == "apache2handler") {
            return true;
        } else {
            if (strpos(PHP_SAPI, "handler") !== false) {
                return true;
            }
        }
        return false;
    }

    /*
     * return PATH for Files & PDO only
     */

    public function getPath($create_path = false) {

        if ($this->option['path'] == "" && self::$config['path'] != "") {
            $this->option("path", self::$config['path']);
        }


        if ($this->option['path'] == '') {
            // revision 618
            if ($this->isPHPModule()) {
                $tmp_dir = ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir();
                $this->option("path", $tmp_dir);
            } else {
                $this->option("path", dirname(__FILE__));
            }

            if (self::$config['path'] == "") {
                self::$config['path'] = $this->option("path");
            }
        }


        $full_path = $this->option("path") . "/" ;//. $this->option("securityKey") . "/";

        if ($create_path==false && $this->checked['path'] == false) {

            if (!file_exists($full_path) || !is_writable($full_path)) {
                if (!file_exists($full_path)) {
                    @mkdir($full_path, 0777);
                }
                if (!is_writable($full_path)) {
                    @chmod($full_path, 0777);
                }
//                if (!file_exists($full_path) || !is_writable($full_path)) {
//                    throw new Exception("Sorry, Please create " . $this->option("path") . "/" . $this->option("securityKey") . "/ and SET Mode 0777 or any Writable Permission!", 100);
//                }
            }


            $this->checked['path'] = true;
            $this->htaccessGen($full_path);
        }

        $this->option['cachePath'] = $full_path;
        return $this->option['cachePath'];
    }

    /*
     * Read File
     * Use file_get_contents OR ALT read
     */

    function readfile($file) {
        if (function_exists("file_get_contents")) {
            return file_get_contents($file);
        } else {
            $string = "";

            $file_handle = @fopen($file, "r");
            if (!$file_handle) {
                throw new Exception("Can't Read File", 96);
            }
            while (!feof($file_handle)) {
                $line = fgets($file_handle);
                $string .= $line;
            }
            fclose($file_handle);

            return $string;
        }
    }

}

//####################modules/session_drivers/WoniuSessionHandle.php####################{


/**
 *
 * @author pm
 */
interface WoniuSessionHandle {
    
    public function start($config=array());

    /**
     * Open the session
     * @return bool
     */
    public function open($save_path, $session_name);

    /**
     * Close the session
     * @return bool
     */
    public function close();

    /**
     * Read the session
     * @param int session id
     * @return string string of the sessoin
     */
    public function read($id);

    /**
     * Write the session
     * @param int session id
     * @param string data of the session
     */
    public function write($id, $data);

    /**
     * Destoroy the session
     * @param int session id
     * @return bool
     */
    public function destroy($id);

    /**
     * Garbage Collector
     * @param int life time (sec.)
     * @return bool
     * @see session.gc_divisor      100
     * @see session.gc_maxlifetime 1440
     * @see session.gc_probability    1
     * @usage execution rate 1/100
     *        (session.gc_probability/session.gc_divisor)
     */
    public function gc($max=0);
}

//####################modules/session_drivers/MysqlSessionHandle.php####################{


/**
 * First you need to create a table in your database:

  CREATE TABLE `session_handler_table` (
  `id` varchar(255) NOT NULL,
  `data` mediumtext NOT NULL,
  `timestamp` int(255) NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 */

/**
 * A PHP session handler to keep session data within a MySQL database
 *
 * @author 	Manuel Reinhard <manu@sprain.ch>
 * @link		https://github.com/sprain/PHP-MySQL-Session-Handler
 */
class MysqlSessionHandle implements WoniuSessionHandle {

    private $_config;

    /**
     * a database MySQLi connection resource
     * @var resource
     */
    protected $dbConnection;

    /**
     * the name of the DB table which handles the sessions
     * @var string
     */
    protected $dbTable;

    public function connect() {
        $config = $this->_config;
        $dbHost = $config['host'];
        $dbPort = $config['port'];
        $dbUser = $config['user'];
        $dbPassword = $config['password'];
        $dbDatabase = $config['database'];
        $dbTable = $config['table'];
        //create db connection
        $this->dbConnection = new mysqli($dbHost, $dbUser, $dbPassword, $dbDatabase, $dbPort);
        $this->dbTable = $dbTable;
        //check connection
        if (mysqli_connect_error()) {
            throw new Exception('Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
        }//if
    }

    /**
     * Set db data if no connection is being injected
     * @param 	string	$dbHost	
     * @param	string	$dbUser
     * @param	string	$dbPassword
     * @param	string	$dbDatabase
     */
    public function start($config = array()) {
        $this->_config = $config = array_merge($config['common'], $config['mysql']);
        session_set_save_handler(array($this, 'open'), array($this, 'close'), array($this, 'read'), array($this, 'write'), array($this, 'destroy'), array($this, 'gc'));
        // set some important session vars
        ini_set('session.auto_start', 0);
        ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', 100);
        ini_set('session.gc_maxlifetime', $this->_config['lifetime']);
        ini_set('session.referer_check', '');
        ini_set('session.entropy_file', '/dev/urandom');
        ini_set('session.entropy_length', 16);
        ini_set('session.use_cookies', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.use_trans_sid', 0);
        ini_set('session.hash_function', 1);
        ini_set('session.hash_bits_per_character', 5);

        // disable client/proxy caching
        session_cache_limiter('nocache');

        // set the cookie parameters
        session_set_cookie_params(
                $this->_config['lifetime'], $this->_config['cookie_path'], $this->_config['cookie_domain']
        );
        // name the session
        session_name($this->_config['session_name']);

        register_shutdown_function('session_write_close');

        // start it up
        if ($config['autostart'] && !isset($_SESSION)) {
            if (!isset($_SESSION)) {
                session_start();
            }
        }
    }

    /**
     * Open the session
     * @return bool
     */
    public function open($save_path, $session_name) {
        if (!is_object($this->dbConnection)) {
            $this->connect();
        }
        return TRUE;
    }

    /**
     * Close the session
     * @return bool
     */
    public function close() {

        return $this->dbConnection->close();
    }

    /**
     * Read the session
     * @param int session id
     * @return string string of the sessoin
     */
    public function read($id) {

        $sql = sprintf("SELECT data FROM %s WHERE id = '%s'", $this->dbTable, $this->dbConnection->escape_string($id));
        if ($result = $this->dbConnection->query($sql)) {
            if ($result->num_rows && $result->num_rows > 0) {
                $record = $result->fetch_assoc();
                return $record['data'];
            } else {
                return false;
            }
        } else {
            return false;
        }
        return true;
    }

    /**
     * Write the session
     * @param int session id
     * @param string data of the session
     */
    public function write($id, $data) {

        $sql = sprintf("REPLACE INTO %s VALUES('%s', '%s', '%s')", $this->dbTable, $this->dbConnection->escape_string($id), $this->dbConnection->escape_string($data), time() + intval($this->_config['lifetime']));
        return $this->dbConnection->query($sql);
    }

    /**
     * Destoroy the session
     * @param int session id
     * @return bool
     */
    public function destroy($id) {
        unset($_SESSION);
        $sql = sprintf("DELETE FROM %s WHERE `id` = '%s'", $this->dbTable, $this->dbConnection->escape_string($id));
        return $this->dbConnection->query($sql);
    }

    /**
     * Garbage Collector
     * @param int life time (sec.)
     * @return bool
     * @see session.gc_divisor      100
     * @see session.gc_maxlifetime 1440
     * @see session.gc_probability    1
     * @usage execution rate 1/100
     *        (session.gc_probability/session.gc_divisor)
     */
    public function gc($max = 0) {
        $sql = sprintf("DELETE FROM %s WHERE `timestamp` < '%s'", $this->dbTable, time());
        return $this->dbConnection->query($sql);
    }

}

//class

//####################modules/session_drivers/MongodbSessionHandle.php####################{


/*
 * This MongoDB session handler is intended to store any data you see fit.
 * One interesting optimization to note is the setting of the active flag
 * to 0 when a session has expired. The intended purpose of this garbage
 * collection is to allow you to create a batch process for removal of
 * all expired sessions. This should most likely be implemented as a cronjob
 * script.
 *
 * @author		Corey Ballou
 * @copyright	Corey Ballou (2010)
 * @property MongoCollection __mongo_collection
 */

class MongodbSessionHandle implements WoniuSessionHandle {

    // default config with support for multiple servers
    // (helpful for sharding and replication setups)
    protected $_config;
    private $__mongo_collection = NULL;
    private $__current_session = NULL;

    public function connect() {
        $connection_string = sprintf('mongodb://%s:%s', $this->_config['host'], $this->_config['port']);
        if ($this->_config['user'] != null && $this->_config['password'] != null) {
            $connection_string = sprintf('mongodb://%s:%s@%s:%s/%s', $this->_config['user'], $this->_config['password'], $this->_config['host'], $this->_config['port'], $this->_config['database']);
        }
        // add immediate connection
        $opts = array('connect' => true);

        // support persistent connections
        if ($this->_config['persistent'] && !empty($this->_config['persistentId'])) {
            $opts['persist'] = $this->_config['persistentId'];
        }

        // support replica sets
        if ($this->_config['replicaSet']) {
            $opts['replicaSet'] = $this->_config['replicaSet'];
        }

        $object_conn = new Mongo($connection_string, $opts);
        $object_mongo = $object_conn->{$this->_config['database']};
        $this->__mongo_collection = $object_mongo->{$this->_config['collection']};
    }

    /**
     * Default constructor.
     *
     * @access  public
     * @param   array   $config
     */
    public function start($config = array()) {
        // initialize the database
        $config = array_merge($config['common'], $config['mongodb']);
        $this->_config = $config;
        // set object as the save handler
        session_set_save_handler(
                array(&$this, 'open'), array(&$this, 'close'), array(&$this, 'read'), array(&$this, 'write'), array(&$this, 'destroy'), array(&$this, 'gc')
        );
        // set some important session vars
        ini_set('session.auto_start', 0);
        ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', 100);
        ini_set('session.gc_maxlifetime', $this->_config['lifetime']);
        ini_set('session.referer_check', '');
        ini_set('session.entropy_file', '/dev/urandom');
        ini_set('session.entropy_length', 16);
        ini_set('session.use_cookies', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.use_trans_sid', 0);
        ini_set('session.hash_function', 1);
        ini_set('session.hash_bits_per_character', 5);

        // disable client/proxy caching
        session_cache_limiter('nocache');
        // set the cookie parameters
        session_set_cookie_params(
                $this->_config['lifetime'], $this->_config['cookie_path'], $this->_config['cookie_domain'], ($_SERVER['SERVER_PORT'] == 443), TRUE
        );

        // name the session
        session_name($this->_config['session_name']);

        register_shutdown_function('session_write_close');

        // start it up
        if ($config['autostart'] && !isset($_SESSION)) {
            if (!isset($_SESSION)) {
                session_start();
            }
        }
    }

    /**
     * 
     * check for collection object
     * @access public
     * @param string $session_path
     * @param string $session_name
     * @return boolean
     */
    public function open($session_path, $session_name) {
        if (!is_object($this->__mongo_collection)) {
            $this->connect();
        }
        $result = true;
        if ($this->__mongo_collection != NULL) {
            $result = false;
        }
        //echo 'open called'."\n";
        return true;
    }

    /**
     * 
     * doing noting
     * @access public
     * @return boolean
     */
    public function close() {
        return true;
    }

    /**
     * 
     * Reading session data based on id
     * @access public
     * @param string $session_id
     * @return mixed 
     */
    public function read($session_id) {
        //print "Read <br />";
        $result = NULL;
        $ret = '';
        $expiry = time();
        $query['_id'] = $session_id;
        $query['expiry'] = array('$gte' => $expiry);
//        print_r($query);
        $result = $this->__mongo_collection->findone($query);
        if ($result) {
            $this->__current_session = $result;
            $result['expiry'] = time() + $this->_config['lifetime'];
            $this->__mongo_collection->update(array("_id" => $session_id), $result);
            $ret = $result['data'];
        }
        //echo 'read called'."\n";
        return $ret;
    }

    /**
     * 
     * Writing session data
     * @access public
     * @param string $session_id
     * @param mixed $data
     * @return boolean
     */
    public function write($session_id, $data) {
        $result = true;
        $expiry = time() + $this->_config['lifetime'];
        $session_data = array();
        if (empty($this->__current_session)) {
            $session_id = $session_id;
            $session_data['_id'] = $session_id;
            $session_data['data'] = $data;
            $session_data['expiry'] = $expiry;
        } else {
            $session_data = (array) $this->__current_session;
            $session_data['data'] = $data;
            $session_data['expiry'] = $expiry;
        }
        $query['_id'] = $session_id;
        $record = $this->__mongo_collection->findOne($query);
        if ($record == null) {
            $this->__mongo_collection->insert($session_data);
            //var_dump('insert');
        } else {
            $record['data'] = $data;
            $record['expiry'] = $expiry;
            $this->__mongo_collection->save($record);
            //var_dump('save');
        }
        //echo 'write called'."\n";
        return true;
    }

    /**
     * 
     * remove session data
     * @access public
     * @param string $session_id
     * @return boolean
     */
    public function destroy($session_id) {
        unset($_SESSION);
        $query['_id'] = $session_id;
        $this->__mongo_collection->remove($query);
        //echo 'destory called'."\n";
        return true;
    }

    /**
     * 
     * Garbage collection
     * @access public
     * @return boolean
     */
    public function gc($max = 0) {
        $query = array();
        $query['expiry'] = array(':lt' => time());
        $this->__mongo_collection->remove($query, array('justOne' => false));
        return true;
    }

}

//####################modules/session_drivers/MemcacheSessionHandle.php####################{


/*
 * This MongoDB session handler is intended to store any data you see fit.
 * One interesting optimization to note is the setting of the active flag
 * to 0 when a session has expired. The intended purpose of this garbage
 * collection is to allow you to create a batch process for removal of
 * all expired sessions. This should most likely be implemented as a cronjob
 * script.
 *
 * @author		Corey Ballou
 * @copyright	Corey Ballou (2010)
 * @property MongoCollection __mongo_collection
 */

class MemcacheSessionHandle implements WoniuSessionHandle {

    /**
     * Default constructor.
     *
     * @access  public
     * @param   array   $config
     */
    public function start($config = array()) {
        $session_save_path = $config['memcache'];
        ini_set('session.save_handler', 'memcache');
        ini_set('session.save_path', $session_save_path);

        // set some important session vars
        ini_set('session.auto_start', 0);
        ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', 100);
        ini_set('session.gc_maxlifetime', $config['common']['lifetime']);
        ini_set('session.referer_check', '');
        ini_set('session.entropy_file', '/dev/urandom');
        ini_set('session.entropy_length', 16);
        ini_set('session.use_cookies', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.use_trans_sid', 0);
        ini_set('session.hash_function', 1);
        ini_set('session.hash_bits_per_character', 5);

        // disable client/proxy caching
        session_cache_limiter('nocache');
        // set the cookie parameters
        session_set_cookie_params(
                $config['common']['lifetime'], $config['common']['cookie_path'], $config['common']['cookie_domain'], ($_SERVER['SERVER_PORT'] == 443), TRUE
        );

        // name the session
        session_name($config['common']['session_name']);

        register_shutdown_function('session_write_close');

        // start it up
        if ($config['common']['autostart'] && !isset($_SESSION)) {
            if (!isset($_SESSION)) {
                session_start();
            }
        }
    }

    public function open($session_path, $session_name) {
        
    }

    public function close() {
        
    }

    public function read($session_id) {
        
    }

    public function write($session_id, $data) {
        
    }

    public function destroy($session_id) {
        
    }

    public function gc($max = 0) {
        
    }

}

//####################modules/session_drivers/RedisSessionHandle.php####################{


/*
 * This MongoDB session handler is intended to store any data you see fit.
 * One interesting optimization to note is the setting of the active flag
 * to 0 when a session has expired. The intended purpose of this garbage
 * collection is to allow you to create a batch process for removal of
 * all expired sessions. This should most likely be implemented as a cronjob
 * script.
 *
 * @author		Corey Ballou
 * @copyright	Corey Ballou (2010)
 * @property MongoCollection __mongo_collection
 */

class RedisSessionHandle implements WoniuSessionHandle {

    /**
     * Default constructor.
     *
     * @access  public
     * @param   array   $config
     */
    public function start($config = array()) {
        $session_save_path = $config['redis'];
        ini_set('session.save_handler', 'redis');
        ini_set('session.save_path', $session_save_path);
        
        // set some important session vars
        ini_set('session.auto_start', 0);
        ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', 100);
        ini_set('session.gc_maxlifetime', $config['common']['lifetime']);
        ini_set('session.referer_check', '');
        ini_set('session.entropy_file', '/dev/urandom');
        ini_set('session.entropy_length', 16);
        ini_set('session.use_cookies', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.use_trans_sid', 0);
        ini_set('session.hash_function', 1);
        ini_set('session.hash_bits_per_character', 5);

        // disable client/proxy caching
        session_cache_limiter('nocache');
        // set the cookie parameters
        session_set_cookie_params(
                $config['common']['lifetime'], $config['common']['cookie_path'], $config['common']['cookie_domain'], ($_SERVER['SERVER_PORT'] == 443), TRUE
        );

        // name the session
        session_name($config['common']['session_name']);

        register_shutdown_function('session_write_close');
        
        
        // start it up
        if ($config['common']['autostart'] && !isset($_SESSION)) {
            if (!isset($_SESSION)) {
                session_start();
            }
        }
    }

    public function open($session_path, $session_name) {
        
    }

    public function close() {
        
    }

    public function read($session_id) {
        
    }

    public function write($session_id, $data) {
        
    }

    public function destroy($session_id) {
        
    }

    public function gc($max = 0) {
        
    }

}
