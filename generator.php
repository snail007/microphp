<?php

$files = array('modules/index.php', 'modules/Router.php', 'modules/Loader.php', 'modules/Controller.php', 'modules/Model.php', 'modules/DB_driver.php', 'modules/Helper.php');
$content = '';
foreach ($files as $file) {
    $content.=str_replace("<?php", "\n//####################{$file}####################{\n", file_get_contents($file));
}
foreach ($files as $file) {
    $content=str_replace("include('" . basename($file) . "');", '', $content);
}
$content=str_replace("../app", 'app', $content);

file_put_contents('index.php', "<?php\n" . $content);
echo 'done';
