<?php
/**
 * Description of index
 *
 * @author Administrator
 */
class Welcome extends Controller{
    public function __construct() {
        parent::__construct();
    }

    public function doIndex($name=''){
        $this->helper('config');
        $this->view("welcome",array('msg'=>$name,'ver'=> $this->config('myconfig', 'app')));
    }
    public function do__output($html){
        echo '__output'.$html;
    }
}

