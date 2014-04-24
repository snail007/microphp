<?php

/**
 * Description of index
 *
 * @author Administrator
 */
class Home extends WoniuController {

    public function doIndex($name = '') {
        $this->view("welcome", array('msg' => $name, 'ver' => $this->config('myconfig', 'app')));
    }

    public function doTestView() {
        $this->view('common/lv1');
    }

    public function doTestViewSame() {
        $this->view('common/lv2');
    }

    public function doHmvc() {
        return 'okay';
    }

}
