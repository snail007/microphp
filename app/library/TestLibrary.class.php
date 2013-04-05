<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TestLibrary
 *
 * @author Administrator
 */
class TestLibrary {
    public static $txt='fuck';
    public function testController(){
        var_dump(Controller::getInstance());
    }
}
 