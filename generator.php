<?php

date_default_timezone_set('PRC');
$ver = "Version 2.2.2";
$header = '<?php
/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		MicroPHP
 * @author		狂奔的蜗牛
 * @email		672308444@163.com
 * @copyright           Copyright (c) 2013 - 2013, 狂奔的蜗牛, Inc.
 * @link		http://git.oschina.net/snail/microphp
 * @since		' . $ver . '
 * @createdtime         ' . date('Y-m-d H:i:s') . '
 */
 ';
$files = array(
    'modules/WoniuRouter.php',
    'modules/WoniuLoader.php',
    'modules/WoniuController.php',
    'modules/WoniuModel.php',
    'modules/db-drivers/db.drivers.php',
    'modules/db-drivers/mysql.driver.php',
    'modules/db-drivers/mysqli.driver.php',
    'modules/db-drivers/pdo.driver.php',
    'modules/db-drivers/sqlite3.driver.php',
    'modules/cache-drivers/driver.php',
    'modules/cache-drivers/drivers/apc.php',
    'modules/cache-drivers/drivers/files.php',
    'modules/cache-drivers/drivers/memcache.php',
    'modules/cache-drivers/drivers/memcached.php',
    'modules/cache-drivers/drivers/sqlite.php',
    'modules/cache-drivers/drivers/wincache.php',
    'modules/cache-drivers/drivers/xcache.php',
    'modules/cache-drivers/drivers/redis.php',
    'modules/cache-drivers/phpfastcache.php',
    'modules/session_drivers/WoniuSessionHandle.php',
    'modules/session_drivers/MysqlSessionHandle.php',
    'modules/session_drivers/MongodbSessionHandle.php',
    'modules/session_drivers/MemcacheSessionHandle.php',
    'modules/session_drivers/RedisSessionHandle.php',
    'modules/WoniuHelper.php',
    'modules/WoniuInput.class.php'
);
$core = '';
foreach ($files as $file) {
    $core.=str_replace("<?php", "\n//####################{$file}####################{\n", file_get_contents($file));
}
common_replace($core);
file_put_contents('MicroPHP.php', "<?php\n" . $core);
$content = php_strip_whitespace('MicroPHP.php');
$content = str_replace("class WoniuLoader", "\n /**
 * @property CI_DB_active_record \$db
 * @property phpFastCache        \$cache
 * @property WoniuInput          \$input
 **/
 class WoniuLoader", $content);
file_put_contents('MicroPHP.php', $header ."\n\n". $core);
file_put_contents('MicroPHP.min.php', str_replace('<?php', $header, $content));

$index = file_get_contents('modules/plugin.php');
foreach ($files as $file) {
    $index = str_replace("include('" . str_replace('modules/', '', $file) . "');\n", '', $index);
}
$index = str_replace("../application", 'application', $index);
$index = str_replace(array("WoniuRouter::setConfig(\$system);","WoniuRouter::loadClass();"), '', $index);
common_replace($index);
file_put_contents('index.php', $index . "\ninclude('MicroPHP.min.php');\nWoniuRouter::setConfig(\$system);\nWoniuRouter::loadClass();");

#ver modify
file_put_contents('application/helper/config.php', "<?php\n\$myconfig['app']='" . $ver . "';");


$content = $index."\ninclude('MicroPHP.min.php');\nWoniuRouter::setConfig(\$system);";
file_put_contents('plugin.php', $content);
echo 'done';

function common_replace(&$str) {
    global $ver;
    $str = str_replace("Version 1.0", $ver, $str);
    $str = str_replace('{createdtime}', date('Y-m-d H:i:s'), $str);
    $str = str_replace("Copyright (c) 2013 - 2013,", 'Copyright (c) 2013 - ' . date('Y') . ',', $str);
}
