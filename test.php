<?php

include('MicroPHP.plugin.php');


WoniuModel::instance('User2')->sayHello('snail');

var_dump(array_keys(WoniuModelLoader::$model_files));
WoniuModel::instance('User')->sayHello('');


