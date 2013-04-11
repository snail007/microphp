<?php

/**
 * Description of index
 *
 * @author Administrator
 */
class Welcome extends Controller {

    public function __construct() {
        parent::__construct();
    }

    public function doForm() {
        $validator = new FormValidator();
        $data = array('user' => 'aaa', 'pass' => 'bbbb');
        $rules = array('user' =>array('func'=>"Welcome::"));
        var_dump($validator->check($rules, $data),$validator->error);
    }
    static function check($val){
        return '123';
    }
    public function doIndex($name = '') {
        $this->helper('config');
        $this->view("welcome", array('msg' => $name, 'ver' => $this->config('myconfig', 'app')));
    }

    public function do__output($html) {
        echo '__output' . $html;
    }

    public function doAjax() {
        $this->ajax_echo(200, 'tip', array('a', 'b'));
    }

}

