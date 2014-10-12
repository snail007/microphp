<?php
/**
 * MicroPHP标准化适配文件
 * 
 * MicroPHP version 2.2.14开始：
 * 控制器类、模型、输入类、规则类使用标准名称：MpController、MpModel、MpInput、MpRule
 * 以前的WoniuController、WoniuModel、WoniuInput、WoniuRule可以继续使用，兼容以前版本。
 */
class MpController extends WoniuController{}
class MpModel extends WoniuModel{}
class MpInput extends WoniuInput{}
class MpRule extends WoniuRule{}

