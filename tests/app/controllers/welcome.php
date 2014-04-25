<?php

/**
 * Description of index
 *
 * @author Administrator
 */
class Welcome extends WoniuController {

    public function __construct() {
        parent::__construct();
        $this->view_vars['vars'] = 'aaaaa';
//        $this->helper('html.helper');
    }

    public function doForm() { 
        $validator = new FormValidator();
        $data = array('user' => '111', 'pass' => 'bbbb');
        $rules = array('user' => array('rule' => "range(1,1000)"));
        var_dump($validator->check($rules, $data), $validator->error);
    }

    static function check($val) {
        var_dump($val);
        return false;
    }

    public function doIndex($name = '') {
        
        $this->view("welcome", array('msg' => $name, 'ver' => $this->config('myconfig', 'app')));
    }

    public function a__output($html) {
        echo '__output' . $html;
    }

    public function doAjax($arg = null) {

        $this->ajax_echo(200, 'tip', $arg);
    }
    
}
