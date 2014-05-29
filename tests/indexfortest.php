<?php
if(file_exists('release')){
    define('release', true);
}
include dirname(__FILE__).'/pluginfortest.php';
WoniuRouter::loadClass();