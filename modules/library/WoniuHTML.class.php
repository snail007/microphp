<?php

/**
 * MicroPHP
 * Description of WoniuHTML
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		MicroPHP
 * @author		狂奔的蜗牛
 * @email		672308444@163.com
 * @copyright	        Copyright (c) 2013 - 2013, 狂奔的蜗牛, Inc.
 * @link		https://bitbucket.org/snail/microphp/
 * @since		Version 1.0
 * @createdtime       {createdtime}
 */
class WoniuHTML {

    public function enableSelectDefault($return = false) {
        $js = '<script>
                var func0797986876; 
                if(typeof(window.onload)=="function"){
                  func0797986876=window.onload;
                }
                window.onload=function(){
                    func0797986876?func0797986876():null;
                    var selects=document.getElementsByTagName("select");
                    for(var k=0;k<selects.length;k++){
                        var s=selects[k];
                        var defaultv=s.attributes["default"]?s.attributes["default"].value:null;
                        if(defaultv){
                            for(var i=0;i<s.length;i++){
                            console.log(s[i].value);
                                if(s[i].value==defaultv){
                                s[i].selected=true;
                                break;
                                }
                            }
                        }
                    }
                }
            </script>';
        if ($return) {
            return $js;
        } else {
            echo $js;
        }
    }

}

/* End of file WoniuHTML.php */
