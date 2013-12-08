<?php

/**
 * Description of index
 *
 * @author Administrator
 */
class Welcome extends WoniuController {

    public function __construct() {
        parent::__construct();
    }

    public function doForm() {
        $validator = new FormValidator();
        $data = array('user' => '111', 'pass' => 'bbbb');
        $rules = array('user' =>array('rule'=>"range(1,1000)"));
        var_dump($validator->check($rules, $data),$validator->error);
    }
    static function check($val){ 
        return false;
    }
    public function doIndex2($name = '') {
        var_dump('xxxxx');
    }
    public function doAjax($arg=null) {
        
        $this->ajax_echo(200, 'tip',$arg);
    }

}

