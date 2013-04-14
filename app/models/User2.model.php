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
class User2 extends ModelHook {

    public function sayHello($name) {
        $this->printHook();
        echo 'hello2:' . $name;
    }

}
