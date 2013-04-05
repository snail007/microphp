<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of index
 *
 * @author Administrator
 */
class Welcome extends Controller{
    public function doIndex(){
//        $this->model('test\User');
//        $this->model->user->sayHello('woniu');
//        $t=new TestLibrary();
//        $t->testController();
//        var_dump($this,'-----------------------------');
        $data1=$this->view('welcome',array('name'=>'fuck youyou'),true);
//        var_dump($this,'-----------------------------');
//        $data2=$this->view('common/footer',null,true);
//        var_dump($this,'-----------------------------');
        echo $data1;
    }
    public function do__output($html){
        echo '__output'.$html;
    }
}

