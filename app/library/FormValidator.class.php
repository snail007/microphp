<?php

/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * 表单验证类库
 * 
 * @package		MicroPHP
 * @author		狂奔的蜗牛
 * @email		672308444@163.com
 * @copyright	        Copyright (c) 2008 - 2013, 狂奔的蜗牛, Inc.
 * @link		https://bitbucket.org/snail/microphp/
 * @since		Version 1.0
 * @filesource
 */
class FormValidator {

    private $current_val;
    public $error;

    public function getMappedData(Array $map, Array $source = null) {
        if (empty($source)) {
            $source = $_POST;
        }
        foreach ($map as $key => $val) {
            $map[$key] = isset($source[$val]) ? $source[$val] : '';
        }
        return $map;
    }

    /**
     * $data=array(username=>array('rule'=>'','tip'=>'','func'=>''))
     * type:reg,func
     * @param array $data
     * @return boolean
     */
    public function check(Array $rules, $data = null) {
        if(empty($data)){
            $data=$_POST;
        }
        foreach ($rules as $key => $value) {
            $this->current_val = empty($data[$key]) ? '' : $data[$key];
            //正则验证 
            if (isset($value['reg'])) { 
                if (!eval('return $this->reg("' . str_replace('"', '\"', $value['reg']).'");')) {
                    $this->error=  empty($value['tip'])?'no tip of '.$key:$value['tip'];
                    return false;
                }
                //自定义函数或者方法验证
            } elseif (isset($value['func'])) {
                if (!eval('return ' . $value['reg'].'(\''.$this->current_val.'\',\''.$this->current_val.'\');')) {
                    return false;
                }
            }
        }
        return true;
    }

    //验证是否为指定长度
    private function len($min, $max) {
        return (preg_match("/^.*{" . $min . "," . $max . "}$/", $this->current_val)) ? true : false;
    }

    //验证邮件地址
    private function email() {
        return (preg_match('/^[_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,4}$/', $this->current_val)) ? true : false;
    }

    //验证是否为指定长度的字母、数字和下划线的组合
    private function ln_len($min, $max) {
        return (preg_match("/^[A-Za-z0-9_]{" . $min . "," . $max . "}$/", $this->current_val)) ? true : false;
    }

    //验证是否为指定长度数字
    private function n_len($min, $max) {
        return (preg_match("/^[0-9]{" . $min . "," . $max . "}$/i", $this->current_val)) ? true : false;
    }

    //验证是否为指定长度汉字
    private function chs_len($min, $max) {
        return (preg_match("/^([\x81-\xfe][\x40-\xfe]){" . $min . "," . $max . "}$/", $this->current_val)) ? true : false;
    }

    //验证是否为指定长度汉字
    private function reg($reg) {
        return (preg_match($reg, $this->current_val)) ? true : false;
    }

}

/* End of file FormValidator.php */
