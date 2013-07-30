<?php
date_default_timezone_set('PRC');
$ver = "Version 2.1.7";
$files = array('modules/WoniuRouter.php', 'modules/WoniuLoader.php',
    'modules/WoniuController.php', 'modules/WoniuModel.php',
    'modules/db-drivers/db.drivers.php', 'modules/db-drivers/mysql.driver.php', 
    'modules/db-drivers/pdo.driver.php', 'modules/db-drivers/sqlite3.driver.php',
    'modules/WoniuHelper.php',
    'modules/WoniuInput.class.php'
);
$core = '';
foreach ($files as $file) {
    $core.=str_replace("<?php", "\n//####################{$file}####################{\n", file_get_contents($file));
}
common_replace($core);
file_put_contents('MicroPHP.php', "<?php\n" . $core . "\nWoniuRouter::loadClass();");
file_put_contents('MicroPHP.php', php_strip_whitespace('MicroPHP.php').'　');
$index = file_get_contents('modules/index.php');
foreach ($files as $file) {
    $index = str_replace("include('" . str_replace('modules/','',$file) . "');", '', $index);
}
$index = str_replace("../app", 'application', $index);
$index = str_replace("WoniuRouter::loadClass();", '', $index);
common_replace($index);
file_put_contents('index.php', $index . "\ninclude('MicroPHP.php');");

#ver modify
file_put_contents('application/helper/config.php', "<?php\n\$myconfig['app']='" . $ver . "';");
file_put_contents('docs/index.html', str_replace('{version}',$ver,  file_get_contents('docs/index_ver.html')));



file_put_contents('MicroPHP.plugin.php',"<?php \n" . $core);
file_put_contents('MicroPHP.plugin.php', $index.str_replace("<?php", "\n", php_strip_whitespace('MicroPHP.plugin.php')).'　');
echo 'done';

function common_replace(&$str) {
    global $ver;
    $str = str_replace("Version 1.0", $ver, $str);
    $str = str_replace('{createdtime}', date('Y-m-d H:i:s'), $str);
    $str = str_replace("Copyright (c) 2013 - 2013,", 'Copyright (c) 2013 - ' . date('Y') . ',', $str);
}

