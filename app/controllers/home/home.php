<?php

/**
 * Description of index
 *
 * @author Administrator
 */
class Home extends WoniuController {

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
        testFunction('aaaaaa');
        var_dump($this->lib->image);
        exit();
        WoniuController::instance('home.TestHook')->doTest();
        echo '<br/>';
        WoniuModel::instance('User2')->printHook();
        echo '<br/>';
        WoniuModel::instance('test.User')->sayHello('snail'); 
    }

    public function do__output($html) {
        echo '__output' . $html;
    }

    public function doAjax() {
        $this->ajax_echo(200, 'tip', array('a', 'b'));
    }

}

