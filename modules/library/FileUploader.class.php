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

    private $size, $ext, $file_formfield_name = 'file',
            $is_zoom = false, $zoom_percent = 1, $max_zoom_width = 0, $max_zoom_height = 0,
            $is_compress = false, $compress_percent = 0.6, $min_compress_size = 0;
    public $error = array('code' => '', 'info' => '');

    public function getError() {
        return $this->error;
    }

    public function getErrorMsg() {
        return $this->error['error'];
    }

    public function getErrorCode() {
        return $this->error['code'];
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

    public function getIsZoom() {
        return $this->is_zoom;
    }

    public function getCompressPercent() {
        return $this->zoom_percent;
    }

    public function getZoomPercent() {
        return $this->zoom_percent;
    }

    public function getIsCompress() {
        return $this->is_compress;
    }

    public function getMinCompressSize() {
        return $this->min_compress_size;
    }

    public function getMaxZoomWidth() {
        return $this->max_zoom_width;
    }

    public function getMaxZoomHeight() {
        return $this->max_zoom_height;
    }

    /**
     * 获取允许的最大文件大小，单位byte字节
     * @return type
     */
    public function getMaxSize() {
        return $this->size;
    }

    /**
     * 获取格式化过的允许的最大文件大小
     * 比如：1MB
     * @return type
     */
    public function getFormatedMaxSize() {
        return $this->size_format($this->size);
    }

    /**
     * 设置表单文件域name名称
     * @param type $field_name
     */
    public function setFormField($field_name) {
        $this->file_formfield_name = $field_name;
        return $this;
    }

    /**
     * 设置文件最大大小<br/>
     * 比如：<br/>
     * 1. 100（100字节）<br/>
     * 2. 1KB<br/>
     * 3. 1MB<br/>
     * 4. 1.5GB<br/>
     * 5. 3.1TB<br/>
     * @param type $s
     * @return FileUploader
     */
    public function setMaxSize($s) {
        $this->size = $this->size2byte($s);
        return $this;
    }

    /**
     * 缩放百分比，比如：0.5,缩放到50%
     * @param type $zoom_percent  默认1,不缩放
     * @return FileUploader
     */
    public function setZoomPercent($zoom_percent) {
        $this->zoom_percent = $zoom_percent;
        return $this;
    }

    /**
     * 压缩百分比：<br/>
     * 1.可以是一个0-1的小数，比如0.6是60%，那么压缩后的图片清晰度是原来的60%<br/>
     * 2.可以是一个数组，第一个元素是宽度，第二个是元素的高度<br/>
     *   比如：<br/>
     *   a.array(100)或者array(100,0)  这个会把图片等比缩放，宽度是100<br/>
     *   b.array(0,200)               这个会把图片等比缩放，高度是100<br/>
     *   c.array(100,200)             这个会把图片强制拉伸或者缩放到宽度是100,高度是200<br/>
     *   d.array(0,0)                 宽度高度无效，那图片不会被缩放<br/>
     * @param type $zoom_percent 默认1
     * @return FileUploader
     */
    public function setCompressPercent($zoom_percent) {
        $this->zoom_percent = $zoom_percent;
        return $this;
    }

    /**
     * 设置是否压缩
     * @param type $is_compress  true：压缩，false：不压缩，默认false
     * @return FileUploader
     */
    public function setCompress($is_compress) {
        $this->is_compress = $is_compress;
        return $this;
    }

    /**
     * 设置是否缩放图片
     * @param type $is_zoom   true：否缩，false：不否缩，默认false
     * @return FileUploader
     */
    public function setZoom($is_zoom) {
        $this->is_zoom = $is_zoom;
        return $this;
    }

    /**
     * 设置要压缩的图片的最小值，如果图片体积小于该大小，就不压缩图片<br/>
     * 比如：1KB,1MB,1GB
     * @param type $min_compress_size
     * @return FileUploader
     */
    public function setMinCompressSize($min_compress_size) {
        $this->min_compress_size = $this->size2byte($min_compress_size);
        return $this;
    }

    /**
     * 设置最大的图片像素宽度，只有当图片的宽度大于这个数值的时候才会被缩放<br/>
     * 比如：500，为0的时候不限制宽度
     * @param type $max_zoom_width
     * @return FileUploader
     */
    public function setMaxZoomWidth($max_zoom_width) {
        $this->max_zoom_width = $max_zoom_width;
        return $this;
    }

    /**
     * 设置最大的图片像素高度，只有当图片的高度大于这个数值的时候才会被缩放<br/>
      比如：500，为0的时候不限制高度
     * 
     * @param type $max_zoom_height
     * @return FileUploader
     */
    public function setMaxZoomHeight($max_zoom_height) {
        $this->max_zoom_height = $max_zoom_height;
        return $this;
    }

    /**
     * 设置允许的文件拓展名列表，数组的形式，
     * 比如：array('jpg','bmp'),不区分大小写
     * @param array $e
     * @return FileUploader
     */
    public function setExt(Array $e) {
        $this->ext = $e;
        return $this;
    }

    /**
     * 保存文件<br/>
     * 提示：<br/>
     * 1.文件最终保存路径$file_path是：$file_path=$dir.$save_name<br/>
     * 2.如果文件夹不存在会自动创建<br/>
     * @param string $save_name 文件名称，可以带上相对路径部分，如果为空则使用md5(sha1_file($file))作为文件的名称
     * @param type $dir         文件保存文件夹路径，该路径会附件在文件名称之前
     * @return boolean
     */
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
            $dir = pathinfo(rtrim($dir, "/\\") . $save_name, PATHINFO_DIRNAME);
        } else {
            $dir = pathinfo($save_name, PATHINFO_DIRNAME);
        }
        if (!is_dir($dir)) {
            mkdir($dir, 0777, TRUE);
        }
        move_uploaded_file($src_file, $save_name);
        if (file_exists($save_name)) {
            $filepath = realpath($save_name);
            $this->resize_image($filepath, $filepath);
            return $filepath;
        } else {
            $this->setError(501, '移动临时文件到目标文件失败,请检查目标目录是否有写权限.');
            return FALSE;
        }
    }

    //--------------private methods------------------
    /**
     * 将体积字符串转换为字节大小
     * @param type $size_str
     * @return type
     */
    private function size2byte($s) {
        $s = rtrim(strtoupper($s), 'B');
        $type = array('K' => 1024, 'M' => 1024 * 1024, 'G' => 1024 * 1024 * 1024, 'T' => 1024 * 1024 * 1024 * 1024);
        if (isset($type[$t = $s{strlen($s) - 1}])) {
            $s = rtrim($s, $t) * $type[$t];
        }
        return $s;
    }

    private function setError($code, $info) {
        $this->error['code'] = $code;
        $this->error['error'] = $info;
    }

    private function checkSize() {
        $max_size = $this->size;
        $size_range = $max_size;
        if ($_FILES[$this->file_formfield_name]['size'] > $size_range || !$_FILES[$this->file_formfield_name]['size']) {
            $this->setError(401, '文件大小错误!最大:' . $this->size_format($max_size));
            return FALSE;
        }
        return TRUE;
    }

    private function size_format($bit) {
        $type = array('B', 'KB', 'MB', 'GB', 'TB');
        for ($i = 0; $bit >= 1024; $i++) {//单位每增大1024，则单位数组向后移动一位表示相应的单位
            $bit/=1024;
        }
        return (floor($bit * 100) / 100) . $type[$i]; //floor是取整函数，为了防止出现一串的小数，这里取了两位小数
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

    /*
     * title ：resize_image 压缩图片
     * param ：$dst_image 压缩后的路径 绝对
     * param ：$src_image 压缩前的路径 绝对
     * return：string 压缩后的路径
     */

    private function resize_image($src_image, $dst_image) {
        if (!$this->is_compress && !$this->is_zoom) {
            return;
        }
        $thumb = $dst_image;
        $image = $src_image;
        list($imagewidth, $imageheight, $imageType) = @getimagesize($image);
        if (!$imageType) {
            return;
        }
        $imageType = image_type_to_mime_type($imageType);
        $$newImageWidth = $newImageHeight = 0;
        //图片不需要缩放判断
        if ($this->max_zoom_height || $this->max_zoom_width) {
            $need_zoom = ($this->max_zoom_height && $imageheight >= $this->max_zoom_height) || ($this->max_zoom_width && $imagewidth >= $this->max_zoom_width);
        } else {
            $need_zoom = true;
        }
        //需要缩放
        if ($need_zoom && $this->is_zoom) {
            //指定了宽高进行缩放
            if (is_array($this->zoom_percent)) {
                if (empty($this->zoom_percent)) {
                    //非法宽高，那么就不缩放
                    $newImageWidth = $imagewidth;
                    $newImageHeight = $imageheight;
                } else {
                    $w = $this->zoom_percent[0] ? $this->zoom_percent[0] : 0;
                    $h = isset($this->zoom_percent[1]) ? $this->zoom_percent[1] : 0;
                    if ($w == 0 && $h > 0) {
                        //按着高度等比缩放
                        $newImageHeight = $h;
                        $newImageWidth = ceil(($newImageHeight * $imagewidth) / $imageheight);
                    } elseif ($w > 0 && $h == 0) {
                        //按着宽度等比缩放
                        $newImageWidth = $w;
                        $newImageHeight = ceil(($newImageWidth * $imageheight) / $imagewidth);
                    } elseif ($w > 0 && $h > 0) {
                        //按着宽高强制拉伸缩放
                        $newImageWidth = $w;
                        $newImageHeight = $h;
                    } else {
                        //非法宽高都是0，那么就不缩放
                        $newImageWidth = $imagewidth;
                        $newImageHeight = $imageheight;
                    }
                }
            } else {
                //小数等比缩放
                $scale = $this->zoom_percent;
                $newImageWidth = ceil($imagewidth * $scale);
                $newImageHeight = ceil($imageheight * $scale);
            }
        } else {
            //不需要缩放
            $newImageWidth = $imagewidth;
            $newImageHeight = $imageheight;
        }

        switch ($imageType) {
            case "image/gif":
                $source = imagecreatefromgif($image);
                break;
            case "image/pjpeg":
            case "image/jpeg":
            case "image/jpg":
                $source = imagecreatefromjpeg($image);
                break;
            case "image/png":
            case "image/x-png":
                $source = imagecreatefrompng($image);
                break;
            default :
                return;
        }
        $newImage = imagecreatetruecolor($newImageWidth, $newImageHeight);
        imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newImageWidth, $newImageHeight, $imagewidth, $imageheight);
        //$compress_quality:0-100
        $compress_quality = (( $this->is_compress && filesize($src_image) >= $this->min_compress_size ) ? $this->compress_percent * 100 : 100);
        switch ($imageType) {
            case "image/gif":
                if ($compress_quality < 100) {
                    //gif格式转换为jpg方便压缩
                    imagejpeg($newImage, $thumb, $compress_quality);
                } else {
                    imagegif($newImage, $thumb);
                }
                break;
            case "image/pjpeg":
            case "image/jpeg":
            case "image/jpg":
                imagejpeg($newImage, $thumb, $compress_quality);
                break;
            case "image/png":
            case "image/x-png":
                $pngQuality = ($compress_quality - 100) / 11.111111;
                $pngQuality = round(abs($pngQuality));
                imagepng($newImage, $thumb, $pngQuality);
                break;
        }
        return $thumb;
    }

}
