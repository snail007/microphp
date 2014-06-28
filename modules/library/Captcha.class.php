<?php

/**
 * @version 1.0
 * @author   bolted snail
 * @date 2011-10-15
 * @email 672308444@163.com
 * @PHP验证码类
 * 使用方法：
 * $image=new Captcha();
 * $image->config('宽度','高度','字符个数','验证码session索引');
 * $image->create();//这样就会向浏览器输出一张图片
 * //所有参数都可以省略，
 * 默认是：宽80 高20 字符数4 验证码session索引captcha_code
 * 第四个参数即把验证码存到$_SESSION['captcha_code']
 * 最简单使用示例:
 * $image=new Captcha();
 * $image->create();//这样就会向浏览器输出一张图片
 */
class Captcha {

    private $width = 80, $height = 20, $codenum = 4;
    public $checkcode;     //产生的验证码
    private $checkimage;    //验证码图片 
    private $session_flag = 'captcha_code'; //存到session中的索引

//尝试开始session

    function __construct() {
        if (!isset($_SESSION)) {
            session_start();
        }
    }

    /*
     * 参数：（宽度，高度，字符个数）
     */

    function config($width = '80', $height = '20', $codenum = '4', $session_flag = 'captcha_code') {
        $this->width = $width;
        $this->height = $height;
        $this->codenum = $codenum;
        $this->session_flag = $session_flag;
    }

    function create() {
        //输出头
        $this->outFileHeader();
        //产生验证码
        $this->createCode();
        //产生图片
        $this->createImage();
        //画干扰线
        $this->wirteSinLine();
        //往图片上写验证码
        $this->writeCheckCodeToImage();
        imagepng($this->checkimage);
        imagedestroy($this->checkimage);
        $_SESSION[$this->session_flag] = $this->checkcode;
    }

    /*
     * @brief 输出头
     */

    private function outFileHeader() {
        header("Content-type: image/png");
    }

    /**
     * 产生验证码
     */
    private function createCode() {
        $this->checkcode = strtoupper(substr(md5(rand()), 0, $this->codenum));
    }

    /**
     * 产生验证码图片
     */
    private function createImage() {
        $this->checkimage = @imagecreate($this->width, $this->height);
        $back = imagecolorallocate($this->checkimage, 255, 255, 255);
        $border = imagecolorallocate($this->checkimage, 0, 0, 0);
        imagefilledrectangle($this->checkimage, 0, 0, $this->width - 1, $this->height - 1, $back); // 白色底
        imagerectangle($this->checkimage, 0, 0, $this->width - 1, $this->height - 1, $border);   // 黑色边框
    }

    //画正弦干扰线
    private function wirteSinLine() {
        $count = 2;
        for ($index = 0; $index < $count; $index++) {
            $w = rand(floor($this->width / rand(2, 5)), $this->width * rand(1, 3));
            $img = $this->checkimage;
            $color = imagecolorallocate($this->checkimage, rand(0, 255), rand(0, 255), rand(0, 255));
            $h = $this->height;
            $h1 = rand(-5, 5);
            $h2 = rand(-1, 1);
            $w2 = rand(10, 15);
            $h3 = rand(4, 6);
            $type = rand(0, 1);
            for ($i = -$w / 2; $i < $w / 2; $i = $i + 0.1) {
                $v = array(cos($i / $w2), sin($i / $w2));
                $y = $h / $h3 * $v[$type] + $h / 2 + $h1;
                imagesetpixel($img, $i + $w / 2, $y, $color);
                $h2 != 0 ? imagesetpixel($img, $i + $w / 2, $y + $h2, $color) : null;
            }
        }
    }

    /**
     *
     * 在验证码图片上逐个画上验证码
     *
     */
    private function writeCheckCodeToImage() {
        for ($i = 0; $i < $this->codenum; $i++) {
            $bg_color = imagecolorallocate($this->checkimage, rand(0, 255), rand(0, 128), rand(0, 255));
            $x = floor($this->width / $this->codenum) * $i;
            $y = rand(0, $this->height - 15);
            imagechar($this->checkimage, rand(5, 8), $x + 5, $y, $this->checkcode[$i], $bg_color);
        }
    }

    function __destruct() {
        unset($this->width, $this->height, $this->codenum, $this->session_flag);
    }

}
