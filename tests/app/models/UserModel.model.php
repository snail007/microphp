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
class UserModel extends MpModel {

    public function test() {
        $this->model('UserModel');
        $this->model('test/SubUserModel');
        return $this->model('test/SubUserModel','subUser');
    }
    public function plus($a,$b){
        return $a+$b;
    }
}
