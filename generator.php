<?php
$ver="Version 1.1"; 
$files = array( 'modules/Router.php', 'modules/Loader.php', 'modules/Controller.php', 'modules/Model.php', 'modules/DB_driver.php', 'modules/Helper.php');
$core = '';
foreach ($files as $file) {
    $core.=str_replace("<?php", "\n//####################{$file}####################{\n", file_get_contents($file));
}
common_replace($core);
file_put_contents('MicroPHP.php', "<?php\n" . $core."\nRouter::loadClass();");

$index=file_get_contents('modules/index.php');
foreach ($files as $file) {
    $index=str_replace("include('" . basename($file) . "');", '', $index);
}
$index=str_replace("../app", 'app', $index);
$index=str_replace("Router::loadClass();", '', $index);
common_replace($index);
file_put_contents('index.php', $index."\ninclude('MicroPHP.php');");

#ver modify
file_put_contents('app/helper/config.php', "<?php\n\$myconfig['app']='".$ver."';");



file_put_contents('MicroPHP.plugin.php', $index."\n".$core);

echo 'done';
function common_replace(&$str){
    global $ver;
    $str=str_replace("Version 1.0", $ver, $str);
    $str=str_replace('{createdtime}', date('Y-m-d H:i:s'), $str);
    $str=str_replace("Copyright (c) 2013 - 2013,", 'Copyright (c) 2013 - '.date('Y').',', $str);
}

