<?php

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
 * @copyright          Copyright (c) 2008 - 2013, 狂奔的蜗牛, Inc.
 * @link                http://git.oschina.net/snail/microphp
 * @since                Version 1.0
 * @filesource
 */
class ControllerHook extends WoniuController{
    protected $hook_id='879887';
    public function printHook() {
        print_r("\n".$this->view_path('common/footer')."\n");
        print_r($this->hook_id)."\n";
    }
}

/* End of file ControllerHook.php */
