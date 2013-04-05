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
           $this->view("welcome",array('msg'=>$name));
    }
    public function do__output($html){
        echo '__output'.$html;
    }
}

