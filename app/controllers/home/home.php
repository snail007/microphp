<?php

/**
 * Description of index
 *
 * @author Administrator
 */
class Home extends Controller {

    public function __construct() {
        parent::__construct();
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
        Controller::instance('home.Welcome')->doAjax('snail');
        Controller::instance('home.Welcome')->doAjax('snail');
//        Model::instance('User2')->sayHello('snail');
    }

    public function do__output($html) {
        echo '__output' . $html;
    }

    public function doAjax() {
        $this->ajax_echo(200, 'tip', array('a', 'b'));
    }

}

