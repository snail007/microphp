<?php
$ver="Version 1.1"; 
$files = array( 'modules/Router.php', 'modules/Loader.php', 'modules/Controller.php', 'modules/Model.php', 'modules/DB_driver.php', 'modules/Helper.php');
$core = '';
foreach ($files as $file) {
    $core.=str_replace("<?php", "\n//####################{$file}####################{\n", file_get_contents($file));
}

file_put_contents('MicroPHP.php', "<?php\n" . $core."\nRouter::loadClass();");

$index=file_get_contents('modules/index.php');
foreach ($files as $file) {
    $index=str_replace("include('" . basename($file) . "');", '', $index);
}
$index=str_replace("../app", 'app', $index);
$index=str_replace("Router::loadClass();", '', $index);
$index=str_replace("Version 1.0;", $ver, $index);
file_put_contents('index.php', $index."\ninclude('MicroPHP.php');");
echo 'done';
