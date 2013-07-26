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

function trigger500($msg = '<h1>Server Error</h1>') {
    global $system;
    header('HTTP/1.1 500 Server Error');
    if (!empty($system['error_page_50x']) && file_exists(dirname(__FILE__) . '/' . $system['error_page_50x'])) {
        include dirname(__FILE__) . '/' . $system['error_page_50x'];
    } else {
        echo $msg;
    }
    exit();
}

function woniuException($exception) {
    $errno= $exception->getCode();
    $errfile = pathinfo($exception->getFile(), PATHINFO_FILENAME);
    $errline = $exception->getLine();
    $errstr = $exception->getMessage();
    @ob_clean();
    trigger500(format_error($errno, $errstr, $errfile, $errline));
}

function fatal_handler() {
    $errfile = "unknown file";
    $errstr = "shutdown";
    $errno = E_CORE_ERROR;
    $errline = 0;
    $error = error_get_last();
    if ($error !== NULL && isset($error["type"]) && ($error["type"] === E_ERROR || ($error['type'] === E_USER_ERROR))) {
        $errno = $error["type"];
        $errfile = pathinfo($error["file"], PATHINFO_FILENAME);
        $errline = $error["line"];
        $errstr = $error["message"];
        @ob_clean();
        trigger500(format_error($errno, $errstr, $errfile, $errline));
    }
}

function format_error($errno, $errstr, $errfile, $errline) {
//    $trace = print_r(debug_backtrace(false), true);
    $content = "<table><tbody>";
    $content .= "<tr valign='top'><td><b>Error</b></td><td>:" . nl2br($errstr) . "</td></tr>";
    $content .= "<tr valign='top'><td><b>Errno</b></td><td>:$errno</td></tr>";
    $content .= "<tr valign='top'><td><b>File</b></td><td>:$errfile</td></tr>";
    $content .= "<tr valign='top'><td><b>Line</b></td><td>:$errline</td></tr>";
//    $content .= "<tr valign='top'><td><b>Trace</b></td><td><pre>$trace</pre></td></tr>";
    $content .= '</tbody></table>';
    return $content;
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
function force_download($filename = '', $data = '') {
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

/* End of file Helper.php */
 