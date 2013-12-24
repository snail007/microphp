<?php

/*
 * Copyright 2013 pm.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * MicroPHP
 * Description of test
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		MicroPHP
 * @author		狂奔的蜗牛
 * @email		672308444@163.com
 * @copyright	        Copyright (c) 2013 - 2013, 狂奔的蜗牛, Inc.
 * @link		http://git.oschina.net/snail/microphp
 * @createdtime         2013-12-4 10:05:45
 */

/**
 * Description of FileUploader
 *
 * @author pm
 */
class FileUploader {

    private $size, $ext, $file_formfield_name = 'file';
    public $error = array('code' => '', 'info' => '');

    /**
     * 设置表单文件域name名称
     * @param type $field_name
     */
    public function setFormField($field_name) {
        $this->file_formfield_name = $field_name;
    }

    /**
     * 设置文件最大大小，单位KB
     * @param type $s
     */
    public function setMaxSize($s) {
        $this->size = $s;
    }

    /**
     * 设置允许的文件拓展名列表，数组的形式，
     * 比如：array('jpg','bmp'),不区分大小写
     * @param array $e
     */
    public function setExt(Array $e) {
        $this->ext = $e;
    }

    public function saveFile($save_name = null, $dir = null) {
        if (empty($_FILES[$this->file_formfield_name])) {
            $this->setError(404, '请先上传文件');
            return FALSE;
        }
        $server_error = array(1 => '文件大小超过了PHP.ini中的文件限制', 2 => '文件大小超过了浏览器限制', 3 => '文件部分被上传', 4 => '没有找到要上传的文件', 5 => '服务器临时文件夹丢失', 6 => '文件写入到临时文件夹出错');
        $error_code = $_FILES[$this->file_formfield_name]['error'];
        if ($error_code > 0) {
            $this->setError(500, isset($server_error[$error_code]) ? $server_error[$error_code] : '未知错误');
            return false;
        }
        if (!$this->checkExt($this->ext, $this->file_formfield_name)) {
            return FALSE;
        }
        if (!$this->checkSize($this->size, $this->file_formfield_name)) {
            return FALSE;
        }
        $src_file = realpath($_FILES[$this->file_formfield_name]['tmp_name']);
        if (empty($save_name)) {
            $file_ext = strtolower(pathinfo($_FILES[$this->file_formfield_name]['name'], PATHINFO_EXTENSION));
            $save_name = md5(sha1_file($_FILES[$this->file_formfield_name]['tmp_name'])) . '.' . $file_ext;
        }
        if (!empty($dir)) {
            $subfix = $dir{strlen($dir) - 1};
            $_dir = ($subfix == '/' || $subfix == "\\" ? $dir : $dir . '/');
            $dir = pathinfo($_dir . $save_name, PATHINFO_DIRNAME);
        } else {
            $dir = pathinfo($save_name, PATHINFO_DIRNAME);
        }
        if (!is_dir($dir)) {
            mkdir($dir, 0777, TRUE);
        }
        $save_name = ($dir ? $this->truepath($dir) . '/' : '' ) . $save_name;
        move_uploaded_file($src_file, $save_name);
        if (file_exists($save_name)) {
            return $this->truepath($save_name);
        } else {
            $this->setError(501, '移动临时文件到目标文件失败,请检查目标目录是否有写权限.');
            return FALSE;
        }
    }

    //check file size , unit is KB
    private function checkSize() {
        $max_size = $this->size;
        $size_range = 1024 * $max_size;
        if ($_FILES[$this->file_formfield_name]['size'] > $size_range || !$_FILES[$this->file_formfield_name]['size']) {
            $this->setError(401, '文件大小错误！最大：' . ( $max_size < 1024 ? $max_size . 'KB' : sprintf('%.1f', $max_size / 1024) . 'MB'));
            return FALSE;
        }
        return TRUE;
    }

    //check file extension 
    private function checkExt() {
        $ext = $this->ext;
        foreach ($ext as &$v) {
            $v = strtolower($v);
        }
        $file_ext = strtolower(pathinfo($_FILES[$this->file_formfield_name]['name'], PATHINFO_EXTENSION));
        if (!in_array($file_ext, $ext)) {
            $this->setError(402, '文件类型错误！只允许：' . implode(',', $ext));
            return FALSE;
        }
        return TRUE;
    }

    public function getError() {
        return $this->error;
    }

    private function setError($code, $info) {
        $this->error['code'] = $code;
        $this->error['error'] = $info;
    }

    public function getFileExt() {
        return strtolower(pathinfo($_FILES[$this->file_formfield_name]['name'], PATHINFO_EXTENSION));
    }

    public function getFileRawName() {
        return strtolower(pathinfo($_FILES[$this->file_formfield_name]['name'], PATHINFO_FILENAME));
    }

    public function getTmpFilePath() {
        return $_FILES[$this->file_formfield_name]['tmp_name'];
    }
    private function truepath($path) {
        // whether $path is unix or not
        $unipath = strlen($path) == 0 || $path{0} != '/';
        // attempts to detect if path is relative in which case, add cwd
        if (strpos($path, ':') === false && $unipath)
            $path = getcwd() . DIRECTORY_SEPARATOR . $path;
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
        $path = implode(DIRECTORY_SEPARATOR, $absolutes);
        // resolve any symlinks
        if (function_exists('linkinfo')&&function_exists('readlink')&&file_exists($path) && linkinfo($path) > 0){
            $path = readlink($path);
        }
        // put initial separator that could have been lost
        $path = !$unipath ? '/' . $path : $path;
        $path = str_replace(array('/', '\\'), '/', $path);
        return $path;
    }
}
