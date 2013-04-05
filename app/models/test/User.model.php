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
class User extends Model {

    public function sayHello($name) {
        $this->model('User2');
        $this->model->user2->sayHello('fuck');
        
        echo 'hello:' . $name;
    }

}
