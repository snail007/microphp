<?php

/**
 * Description of index
 *
 * @author Administrator
 */
class Welcome extends WoniuController {

    public function doIndex($name = '') {
        $this->helper('config');
        $this->view("welcome", array('msg' => $name, 'ver' => $this->config('myconfig', 'app')));
    }

}
