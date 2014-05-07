<?php

function testFunction($param) {
    return $param;
}

function errorHandle() {
    var_dump(func_get_args());
}

/**
 * 检测IP段内IP段地址<br>
 * $ip_addr格式：192.168.1.10/24<br>
 * ip后面24是子网掩码地址长度<br>
 * 传入Ip地址对Ip段地址进行处理得到相关的信息
 * 
 */

function ip_info($ip_addr) {
    $ip_addr = str_replace(" ", "", $ip_addr);    //去除字符串中的空格
    $arr = explode('/', $ip_addr);               //对IP段进行解剖
    $ip_addr = $arr[0];                         //得到IP地址
    $ip_addr_arr=  explode('.', $ip_addr);
    foreach ($ip_addr_arr as &$v) {
        $v= intval($v);//去掉192.023.20.01其中的023的0
    }
    $ip_addr=  implode('.', $ip_addr_arr);//修正后的ip地址
    $netbits = intval($arr[1]);                 //得到掩码位

    $subnet_mask = long2ip(ip2long("255.255.255.255") << (32 - $netbits));
    $ip = ip2long($ip_addr);
    $nm = ip2long($subnet_mask);
    $nw = ($ip & $nm);
    $bc = $nw | (~$nm);

    $ips = array();
    $ips['netmask'] = long2ip($nm);     //子网掩码
    $ips['count'] = ($bc - $nw - 1);      //可用IP数目
    if ($ips['count'] <= 0) {
        $ips['count'] += 4294967296;
    }
    if ($ips['count'] < 0) {
        $ips['count'] = 0;      //当$netbits是32的时候可用数目是-1，这里修正为1
        $ips['start'] = long2ip($ip);    //可用IP开始
        $ips['end'] = long2ip($ip);      //可用IP结束
    } else {
        $ips['start'] = long2ip($nw + 1);    //可用IP开始
        $ips['end'] = long2ip($bc - 1);      //可用IP结束
    }
    $bc = sprintf('%u', $bc);    //或者采用此方法转换成无符号的，修复32位操作系统中long2ip后会出现负数
    $nw = sprintf('%u', $nw);
    $ips['netaddress'] = long2ip($nw);              //子网地址
    $ips['broadcast'] = long2ip($bc);              //广播地址

    return $ips;
}
