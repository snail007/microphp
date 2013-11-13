<?php

include('plugin.php');


WoniuModel::instance('User2')->sayHello('snail');
WoniuModel::instance('User')->sayHello('');
WoniuController::instance('welcome')->doIndex();
WoniuController::instance('welcome')->doIndex();