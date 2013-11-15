<?php

function testFunction($param) {
    echo $param;
}

function errorHandle() {
    var_dump(func_get_args());
}
