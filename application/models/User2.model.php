<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author Administrator
 */
class User2 extends WoniuModel {

    public function __construct() {
        parent::__construct();
    }

    public function sayHello($name) {
        var_dump(array_keys(WoniuModelLoader::$model_files),'------------');
        $this->model('User');
        $this->model('User','cccc');
        $this->model('User2');
        echo 'User2 say: hello:' . $name."\n<br/>";
    }
}
