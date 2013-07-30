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
class User extends WoniuModel {

    public function __construct() {
        parent::__construct();
    }

    public function sayHello($name) {
        echo 'hello:' . $name;
    }
}
