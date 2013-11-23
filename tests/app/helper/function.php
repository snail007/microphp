<?php

function testFunction($param) {
    return $param;
}

function errorHandle() {
    var_dump(func_get_args());
}
