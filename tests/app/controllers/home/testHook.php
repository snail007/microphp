<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.2.0 or newer
 *
 * @package                MicroPHP
 * @author                狂奔的蜗牛
 * @email                672308444@163.com
 * @copyright          Copyright (c) 2008 - 2013, 狂奔的蜗牛, Inc.
 * @link                http://git.oschina.net/snail/microphp
 * @since                Version 1.0
 * @filesource
 */
class TestHook extends ControllerHook{
    public function doTestA() {
        if(empty($_SERVER['HTTP_IF_NONE_MATCH'])){
            //第一次请求
            //
            //.....投票处理代码.......
            //
            
            header('Etag: vote_yes');
            echo '投票成功';
        }elseif($_SERVER['HTTP_IF_NONE_MATCH']=='vote_yes'){
            //第二次请求，设置为重复投票标志
            header('Etag: vote_okay');
            echo '已经投票!';
        }elseif($_SERVER['HTTP_IF_NONE_MATCH']=='vote_okay'){
            //第三次及以后的请求直接使用第二次的内容。
            header('HTTP/1.1 304 NotModify');
        }else{
            //非法的etag
            //直接使用第二次的内容。
            header('HTTP/1.1 304 NotModify');
        }
        //$this->printHook();
    } 
}

/* End of file testHook.php */
