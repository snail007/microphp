<?php
/*
//
//                       _oo0oo_
//                      o8888888o
//                      88" . "88
//                      (| -_- |)
//                      0\  =  /0
//                    ___/`---'\___
//                  .' \\|     |// '.
//                 / \\|||  :  |||// \
//                / _||||| -:- |||||- \
//               |   | \\\  -  /// |   |
//               | \_|  ''\---/''  |_/ |
//               \  .-\__  '-'  ___/-. /
//             ___'. .'  /--.--\  `. .'___
//          ."" '<  `.___\_<|>_/___.' >' "".
//         | | :  `- \`.;`\ _ /`;.`/ - ` : | |
//         \  \ `_.   \_ __\ /__ _/   .-` /  /
//     =====`-.____`.___ \_____/___.-`___.-'=====
//                       `=---='
//
//
//     ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//

                   佛祖保佑         永无BUG
 */
date_default_timezone_set('PRC');
$ver = "Version 2.3.0";
$header = '<?php
/*
 * Copyright 2014 pm.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.2.0 or newer
 *
 * @package       MicroPHP
 * @author        狂奔的蜗牛
 * @email         672308444@163.com
 * @copyright     Copyright (c) 2013 - '.date('Y').', 狂奔的蜗牛, Inc.
 * @link          http://git.oschina.net/snail/microphp
 * @since         ' . $ver . '
 * @createdtime   ' . date('Y-m-d H:i:s') . '
 */
 ';

$files = array(
    //core
    'modules/WoniuHelper.php',
    'modules/WoniuInput.class.php',
    'modules/WoniuRouter.php',
    'modules/WoniuLoader.php',
    'modules/WoniuController.php',
    'modules/WoniuModel.php',
    'modules/standard.php',
    //optional_core
    'rule' => 'modules/WoniuRule.class.php',
    //db
    'db' => 'modules/db-drivers/db.drivers.php',
    'mysql' => 'modules/db-drivers/mysql.driver.php',
    'mysqli' => 'modules/db-drivers/mysqli.driver.php',
    'pdo' => 'modules/db-drivers/pdo.driver.php',
    'sqlite3' => 'modules/db-drivers/sqlite3.driver.php',
    //cache
    'cache_driver' => 'modules/cache-drivers/driver.php',
    'apc' => 'modules/cache-drivers/drivers/apc.php',
    'files' => 'modules/cache-drivers/drivers/files.php',
    'memcache' => 'modules/cache-drivers/drivers/memcache.php',
    'memcached' => 'modules/cache-drivers/drivers/memcached.php',
    'sqlite' => 'modules/cache-drivers/drivers/sqlite.php',
    'wincache' => 'modules/cache-drivers/drivers/wincache.php',
    'xcache' => 'modules/cache-drivers/drivers/xcache.php',
    'redis' => 'modules/cache-drivers/drivers/redis.php',
    'phpfastcache' => 'modules/cache-drivers/phpfastcache.php',
    //session
    'WoniuSession' => 'modules/session_drivers/WoniuSessionHandle.php',
    'MysqlSession' => 'modules/session_drivers/MysqlSessionHandle.php',
    'MongodbSession' => 'modules/session_drivers/MongodbSessionHandle.php',
    'MemcacheSession' => 'modules/session_drivers/MemcacheSessionHandle.php',
    'RedisSession' => 'modules/session_drivers/RedisSessionHandle.php',
);


if (php_sapi_name() == 'cli' || !empty($_POST)) {
    //定制
    if (!empty($_POST)) {
        session_start();
        if (empty($_SESSION['gen_token']) || $_SESSION['gen_token'] != @$_POST['token']) {
            exit('<script>alert("页面已过期，请刷新");</script>');
        } else {
            unset($_SESSION['gen_token']);
        }
        $diy = empty($_POST['keys']) ? array() : $_POST['keys'];

        $db_keys = array('mysql', 'mysqli', 'pdo', 'sqlite3');
        $cache_keys = array('apc', 'files', 'memcache', 'memcached', 'sqlite', 'wincache', 'xcache', 'redis');
        $session_keys = array('MysqlSession', 'MongodbSession', 'MemcacheSession', 'RedisSession');
        $not_selected_all = array();

        $not_selected = array_diff($db_keys, $diy);
        if (count($not_selected) == count($db_keys)) {
            unset($files['db']);
        }
        $not_selected_all = array_merge($not_selected_all, $not_selected);

        $not_selected = array_diff($cache_keys, $diy);
        if (count($not_selected) == count($cache_keys)) {
            unset($files['cache_driver']);
            unset($files['phpfastcache']);
        }
        $not_selected_all = array_merge($not_selected_all, $not_selected);

        $not_selected = array_diff($session_keys, $diy);
        if (count($not_selected) == count($session_keys)) {
            unset($files['WoniuSession']);
        }
        $not_selected_all = array_merge($not_selected_all, $not_selected);

        $core_keys = array('rule');
        $not_selected = array_diff($core_keys, $diy);
        $not_selected_all = array_merge($not_selected_all, $not_selected);

        foreach ($not_selected_all as $key) {
            unset($files[$key]);
        }
        $core = '';
        foreach ($files as $file) {
            $core.=str_replace("<?php", "", file_get_contents($file));
        }
        common_replace($core);
        $donw_name = 'MicroPHP.php';
        $content = "<?php\n" . $core;
        if ($_POST['type'] == 'min') {
            $donw_name = 'MicroPHP.min.php';
            $content = compress_php_src($content);
        }
        $content = str_replace('<?php', "{$header}", $content);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $donw_name . '"');
        header('Content-Transfer-Encoding: binary');
        exit($content);
    }
    //命令行
    $core = '';
    foreach ($files as $file) {
        $file_content = file_get_contents($file);
        $core.=$file_content;
    }
    common_replace($core);
    file_put_contents('MicroPHP.php', "<?php\n" . $core);
    $content = php_strip_whitespace('MicroPHP.php');
    file_put_contents('MicroPHP.php', $header . $core);
    file_put_contents('MicroPHP.min.php', str_replace('<?php', $header, $content));

    $index = file_get_contents('modules/plugin.php');
    foreach ($files as $file) {
        $index = str_replace("include('" . str_replace('modules/', '', $file) . "');\n", '', $index);
    }
    $index = str_replace("../application", 'application', $index);
    $index = str_replace(array("WoniuRouter::setConfig(\$system);", "WoniuRouter::loadClass();"), '', $index);
    common_replace($index);
    $index=$header.$index;
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
    $str = str_replace('http://git.oschina.net/snail/microphp', '', $str);
    $str = preg_replace('|^ *// *[\w].*$\n|m', '', $str);//去掉英文单行注释
    $str = preg_replace('|^ *$\n|m', '', $str);//去掉空行
    $str = preg_replace('| +$|m', '', $str);//去掉行尾空格
    $str = preg_replace_callback('|^ +|m', "space2tab", $str);//行首空格缩进转为制表符缩进
    $str = preg_replace('|/\*\*[^/]*MicroPHP[^/]*\*/|sm', '', $str);//去掉文件头版权注释块
    $str = str_replace("<?php", "", $str);
}
//行首空格缩进转为制表符缩进
function space2tab($arr){
    $tab_count=4;
    $space=$arr[0];
    $len=strlen($space);
    $left=$len%$tab_count;
    $c= floor($len/$tab_count);
    $str='';
    for($i=0;$i<$c;$i++){
        $str.="\t";
    }
    for($i=0;$i<$left;$i++){
        $str.=" ";
    }
    return $str;
}
function compress_php_src($src, $is_file = false) {
    // Whitespaces left and right from this signs can be ignored
    static $IW = array(
        T_CONCAT_EQUAL, // .=
        T_DOUBLE_ARROW, // =>
        T_BOOLEAN_AND, // &&
        T_BOOLEAN_OR, // ||
        T_IS_EQUAL, // ==
        T_IS_NOT_EQUAL, // != or <>
        T_IS_SMALLER_OR_EQUAL, // <=
        T_IS_GREATER_OR_EQUAL, // >=
        T_INC, // ++
        T_DEC, // --
        T_PLUS_EQUAL, // +=
        T_MINUS_EQUAL, // -=
        T_MUL_EQUAL, // *=
        T_DIV_EQUAL, // /=
        T_IS_IDENTICAL, // ===
        T_IS_NOT_IDENTICAL, // !==
        T_DOUBLE_COLON, // ::
        T_PAAMAYIM_NEKUDOTAYIM, // ::
        T_OBJECT_OPERATOR, // ->
        T_DOLLAR_OPEN_CURLY_BRACES, // ${
        T_AND_EQUAL, // &=
        T_MOD_EQUAL, // %=
        T_XOR_EQUAL, // ^=
        T_OR_EQUAL, // |=
        T_SL, // <<
        T_SR, // >>
        T_SL_EQUAL, // <<=
        T_SR_EQUAL, // >>=
    );
    if ($is_file) {
        if (!$src = file_get_contents($src)) {
            return false;
        }
    }
    $tokens = token_get_all($src);

    $new = "";
    $c = sizeof($tokens);
    $iw = false; // ignore whitespace
    $ih = false; // in HEREDOC
    $ls = "";    // last sign
    $ot = null;  // open tag
    for ($i = 0; $i < $c; $i++) {
        $token = $tokens[$i];
        if (is_array($token)) {
            list($tn, $ts) = $token; // tokens: number, string, line
            $tname = token_name($tn);
            if ($tn == T_INLINE_HTML) {
                $new .= $ts;
                $iw = false;
            } else {
                if ($tn == T_OPEN_TAG) {
                    if (strpos($ts, " ") || strpos($ts, "\n") || strpos($ts, "\t") || strpos($ts, "\r")) {
                        $ts = rtrim($ts);
                    }
                    $ts .= " ";
                    $new .= $ts;
                    $ot = T_OPEN_TAG;
                    $iw = true;
                } elseif ($tn == T_OPEN_TAG_WITH_ECHO) {
                    $new .= $ts;
                    $ot = T_OPEN_TAG_WITH_ECHO;
                    $iw = true;
                } elseif ($tn == T_CLOSE_TAG) {
                    if ($ot == T_OPEN_TAG_WITH_ECHO) {
                        $new = rtrim($new, "; ");
                    } else {
                        $ts = " " . $ts;
                    }
                    $new .= $ts;
                    $ot = null;
                    $iw = false;
                } elseif (in_array($tn, $IW)) {
                    $new .= $ts;
                    $iw = true;
                } elseif ($tn == T_CONSTANT_ENCAPSED_STRING || $tn == T_ENCAPSED_AND_WHITESPACE) {
                    if ($ts[0] == '"') {
                        $ts = addcslashes($ts, "\n\t\r");
                    }
                    $new .= $ts;
                    $iw = true;
                } elseif ($tn == T_WHITESPACE) {
                    $nt = @$tokens[$i + 1];
                    if (!$iw && (!is_string($nt) || $nt == '$') && !in_array($nt[0], $IW)) {
                        $new .= " ";
                    }
                    $iw = false;
                } elseif ($tn == T_START_HEREDOC) {
                    $new .= "<<<S\n";
                    $iw = false;
                    $ih = true; // in HEREDOC
                } elseif ($tn == T_END_HEREDOC) {
                    $new .= "S;";
                    $iw = true;
                    $ih = false; // in HEREDOC
                    for ($j = $i + 1; $j < $c; $j++) {
                        if (is_string($tokens[$j]) && $tokens[$j] == ";") {
                            $i = $j;
                            break;
                        } else if ($tokens[$j][0] == T_CLOSE_TAG) {
                            break;
                        }
                    }
                } elseif ($tn == T_COMMENT || $tn == T_DOC_COMMENT) {
                    $iw = true;
                } else {
                    if (!$ih) {
                        $ts = strtolower($ts);
                    }
                    $new .= $ts;
                    $iw = false;
                }
            }
            $ls = "";
        } else {
            if (($token != ";" && $token != ":") || $ls != $token) {
                $new .= $token;
                $ls = $token;
            }
            $iw = true;
        }
    }
    return $new;
}
?><?php
if (php_sapi_name() != 'cli') {
    session_start();
    $_SESSION['gen_token'] = $token = md5(time());
    ?>
    <!doctype html>
    <html>
        <head>
            <title>MicroPHP定制版生成器_定制属于你的MicroPHP</title>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <style>
                td,th{
                    padding: 15px;
                    border-right: 1px #cccccc solid;
                    border-top: 1px #cccccc solid;
                }
                table{
                    border-left: 1px #cccccc solid;
                    border-bottom: 1px #cccccc solid;
                    border-radius: 5px;
                }
                caption{
                    padding:15px;
                    font-size: 2em;
                    font-weight: bold;
                }
                pre{
                    padding:0;
                    margin:0;
                    line-height: 1.5em;
                    font-size: 14px;
                    color:#111;
                }
            </style>
        </head>
        <body>
            <form action="?" target="down" name="mpform" method="POST">
                <input type="hidden" name="token" value="<?php echo $token; ?>">
                <table border="0"  cellpadding="0" cellspacing="0" align="center" >
                    <caption>MicroPHP定制版生成器<br/><small><?php echo $ver; ?></small></caption>
                    <thead>
                        <tr>
                            <th width="130"  style="text-align: right;">功能</th>
                            <th width="500">可选内容</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="text-align: right;">数据库驱动</td>
                            <td>
                                <label><input type="checkbox" name="keys[]" value="mysql" checked />mysql</label>
                                <label><input type="checkbox" name="keys[]" value="mysqli" />mysqli</label>
                                <label><input type="checkbox" name="keys[]" value="pdo" />pdo</label>
                                <label><input type="checkbox" name="keys[]" value="sqlite3" />sqlite3</label>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right;">缓存驱动</td>
                            <td>
                                <label><input type="checkbox" name="keys[]" value="files" checked />files</label>
                                <label><input type="checkbox" name="keys[]" value="memcache" />memcache</label>
                                <label><input type="checkbox" name="keys[]" value="memcached" />memcached</label>
                                <label><input type="checkbox" name="keys[]" value="apc" />apc</label>
                                <label><input type="checkbox" name="keys[]" value="redis" />redis</label>
                                <label><input type="checkbox" name="keys[]" value="sqlite" />sqlite</label>
                                <label><input type="checkbox" name="keys[]" value="xcache" />xcache</label>
                                <label><input type="checkbox" name="keys[]" value="wincache" />wincache</label>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right;">SESSION驱动</td>
                            <td>
                                <label><input type="checkbox" name="keys[]" value="MysqlSession"  />mysql</label>
                                <label><input type="checkbox" name="keys[]" value="MongodbSession" />mongodb</label>
                                <label><input type="checkbox" name="keys[]" value="MemcacheSession" />memcache</label>
                                <label><input type="checkbox" name="keys[]" value="RedisSession" />redis</label>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right;">可选核心类</td>
                            <td>
                                <label><input type="checkbox" name="keys[]" value="rule"  />WoniuRule(生成表单验证规则助手类)</label>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right;">提示</td>
                            <td>
                                <pre>
            如果相应的功能内容都没有选择，那么生成的核心文件将不支持相应的功能和配置。
            1.没有选择session驱动，那么session功能和相应的配置将不再起作用。
            2.没有选择缓存驱动，那么$this->cache将是null。
              如果只选择了files那么系统配置里面缓存类型将只支持files。
            3.没有选择数据库驱动，那么$this->db将是null，$this->database()不能使用。
              如果只选择了mysql那么系统配置里面数据库驱动类型将只支持mysql。
            4.没有选择可选核心类，那么对应的类的相关方法将不能再使用。
                                </pre>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right;"></td>
                            <td>
                                <input type="hidden" id="type" name="type" value="" />
                                <input type="button" onclick="document.getElementById('type').value = '';
                                        document.mpform.submit();" value="生成原版" />
                                <input type="button" onclick="document.getElementById('type').value = 'min';
                                        document.mpform.submit();" value="生成压缩版" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </form>
            <iframe style="display: none;" name="down"></iframe>
            <div style="display: none;"><script type="text/javascript">
                var _bdhmProtocol = (("https:" == document.location.protocol) ? " https://" : " http://");
                document.write(unescape("%3Cscript src='" + _bdhmProtocol + "hm.baidu.com/h.js%3F25dc3c6d2187c5da81c6269c209aa726' type='text/javascript'%3E%3C/script%3E"));
                </script>
            </div>
        </body>
    </html>
    <?php
}