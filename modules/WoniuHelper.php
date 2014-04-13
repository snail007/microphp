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
 * @link                http://git.oschina.net/snail/microphp
 * @since                Version 1.0
 * @createdtime       {createdtime}
 */
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
            return WoniuLoader::$system['db'][$system['db']['active_group']];
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
        $js = '<script>
                var func0797986876; 
                if(typeof(window.onload)=="function"){
                  func0797986876=window.onload;
                }
                window.onload=function(){
                    func0797986876?func0797986876():null;
                    var selects=document.getElementsByTagName("select");
                    for(var k=0;k<selects.length;k++){
                        var s=selects[k];
                        var defaultv=s.attributes["default"]?s.attributes["default"].value:null;
                        if(defaultv){
                            for(var i=0;i<s.length;i++){
                            console.log(s[i].value);
                                if(s[i].value==defaultv){
                                s[i].selected=true;
                                break;
                                }
                            }
                        }
                    }
                };
            </script>';
        if ($return) {
            return $js;
        } else {
            echo $js;
        }
    }

}

/* End of file Helper.php */
 
