<?php
/*
  _oo0oo_
  o8888888o
  88" . "88
  (| ^_^ |)
  0\  =  /0
  ___/`---'\___
  .' \\|     | '.
  / \\|||  :  ||| \
  / _||||| -:- |||||- \
  |   | \\\  -  / |   |
  | \_|  ''\---/''  |_/ |
  \  .-\__  '-'  ___/-. /
  ___'. .'  /--.--\  `. .'___
  ."" '<  `.___\_<|>_/___.' >' "".
  | | :  `- \`.;`\ _ /`;.`/ - ` : | |
  \  \ `_.   \_ __\ /__ _/   .-` /  /
  =====`-.____`.___ \_____/___.-`___.-'=====
  `=---='


  ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

  佛祖保佑         永无BUG
 */
date_default_timezone_set('PRC');
$ver = "Version 2.2.8";
$header = '<?php
/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.2.0 or newer
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
    'modules/WoniuHelper.php',
    'modules/WoniuInput.class.php',
    'modules/WoniuRouter.php',
    'modules/WoniuLoader.php',
    'modules/WoniuController.php',
    'modules/WoniuModel.php',
    'modules/db-drivers/db.drivers.php',
    'mysql' => 'modules/db-drivers/mysql.driver.php',
    'mysqli' => 'modules/db-drivers/mysqli.driver.php',
    'pdo' => 'modules/db-drivers/pdo.driver.php',
    'sqlite3' => 'modules/db-drivers/sqlite3.driver.php',
    'modules/cache-drivers/driver.php',
    'apc' => 'modules/cache-drivers/drivers/apc.php',
    'files' => 'modules/cache-drivers/drivers/files.php',
    'memcache' => 'modules/cache-drivers/drivers/memcache.php',
    'memcached' => 'modules/cache-drivers/drivers/memcached.php',
    'sqlite' => 'modules/cache-drivers/drivers/sqlite.php',
    'xcache' => 'modules/cache-drivers/drivers/wincache.php',
    'apc' => 'modules/cache-drivers/drivers/xcache.php',
    'redis' => 'modules/cache-drivers/drivers/redis.php',
    'modules/cache-drivers/phpfastcache.php',
    'modules/session_drivers/WoniuSessionHandle.php',
    'MysqlSession' => 'modules/session_drivers/MysqlSessionHandle.php',
    'MongodbSession' => 'modules/session_drivers/MongodbSessionHandle.php',
    'MemcacheSession' => 'modules/session_drivers/MemcacheSessionHandle.php',
    'RedisSession' => 'modules/session_drivers/RedisSessionHandle.php',
);
if (php_sapi_name() == 'cli') {
    $core = '';
    foreach ($files as $file) {
        $core.=str_replace("<?php", "\n//####################{$file}####################{\n", file_get_contents($file));
    }
    common_replace($core);
    file_put_contents('MicroPHP.php', "<?php\n" . $core);
    $content = php_strip_whitespace('MicroPHP.php');
    file_put_contents('MicroPHP.php', $header . "\n\n" . $core);
    file_put_contents('MicroPHP.min.php', str_replace('<?php', $header, $content));

    $index = file_get_contents('modules/plugin.php');
    foreach ($files as $file) {
        $index = str_replace("include('" . str_replace('modules/', '', $file) . "');\n", '', $index);
    }
    $index = str_replace("../application", 'application', $index);
    $index = str_replace(array("WoniuRouter::setConfig(\$system);", "WoniuRouter::loadClass();"), '', $index);
    common_replace($index);
    file_put_contents('index.php', $index . "\ninclude('MicroPHP.min.php');\nWoniuRouter::setConfig(\$system);\nWoniuRouter::loadClass();");

#ver modify
    file_put_contents('application/helper/config.php', "<?php\n\$myconfig['app']='" . $ver . "';");


    $content = $index . "\ninclude('MicroPHP.min.php');\nWoniuRouter::setConfig(\$system);";
    file_put_contents('plugin.php', $content);
}

function common_replace(&$str) {
    global $ver;
    $str = str_replace("Version 1.0", $ver, $str);
    $str = str_replace('{createdtime}', date('Y-m-d H:i:s'), $str);
    $str = str_replace("Copyright (c) 2013 - 2013,", 'Copyright (c) 2013 - ' . date('Y') . ',', $str);
}
?><?php if (php_sapi_name() != 'cli') { ?>
    <!doctype html>
    <html>
        <head>
            <title>MicroPHP定制版生成器_定制属于你的MicroPHP</title>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        </head>
        <body>
            <table border="1"  cellpadding="0" cellspacing="0" align="center" >
                <thead>
                    <tr>
                        <th width="100">功能</th>
                        <th>可选内容</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align: right;">数据库驱动</td>
                        <td>
                            <label><input type="checkbox" name="keys[mysql]" checked />mysql</label>
                            <label><input type="checkbox" name="keys[mysqli]" />mysqli</label>
                            <label><input type="checkbox" name="keys[pdo]" />pdo</label>
                            <label><input type="checkbox" name="keys[sqlite3]" />sqlite3</label>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: right;">缓存驱动</td>
                        <td>
                            <label><input type="checkbox" name="keys[files]" checked />files</label>
                            <label><input type="checkbox" name="keys[memcache]" />memcache</label>
                            <label><input type="checkbox" name="keys[memcached]" />memcached</label>
                            <label><input type="checkbox" name="keys[sqlite]" />sqlite</label>
                            <label><input type="checkbox" name="keys[xcache]" />xcache</label>
                            <label><input type="checkbox" name="keys[apc]" />apc</label>
                            <label><input type="checkbox" name="keys[redis]" />redis</label>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: right;">SESSION驱动</td>
                        <td>
                            <label><input type="checkbox" name="keys[MysqlSession]" checked />Mysql</label>
                            <label><input type="checkbox" name="keys[MongodbSession]" />Mongodb</label>
                            <label><input type="checkbox" name="keys[MemcacheSession]" />Memcache</label>
                            <label><input type="checkbox" name="keys[RedisSession]" />Redis</label>
                        </td>
                    </tr>
                </tbody>
            </table>
        </body>
    </html>
    <?php
}