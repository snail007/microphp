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
class UserModel2 extends WoniuModel {

    public function test() {
        $this->model('UserModel');
        $this->model('test/SubUserModel');
        return $this->model('test/SubUserModel','subUser');
    }
    public function plus($a,$b){
        return $a+$b;
    }
}
