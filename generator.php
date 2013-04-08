<?php

$files = array( 'modules/Router.php', 'modules/Loader.php', 'modules/Controller.php', 'modules/Model.php', 'modules/DB_driver.php', 'modules/Helper.php');
$core = '';
foreach ($files as $file) {
    $core.=str_replace("<?php", "\n//####################{$file}####################{\n", file_get_contents($file));
}
file_put_contents('core.php', "<?php\n" . $core."\nRouter::loadClass();");
$index=file_get_contents('modules/index.php');
foreach ($files as $file) {
    $content=str_replace("include('" . basename($file) . "');", '', $content);
}
$index=str_replace("../app", 'app', $index);
$index=str_replace("Router::loadClass();", '', $content);
file_put_contents('index.php', $index."\ninclude('core.php');");
echo 'done';
