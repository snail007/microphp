<?php
    include('MicroPHP.plugin.php');
WoniuController::instance('home.TestHook')->doTest();
        echo '<br/>';
        WoniuModel::instance('User2')->printHook();
        echo '<br/>';
        WoniuModel::instance('test.User')->sayHello('snail'); 
        WoniuLoader::instance()->view('snail');