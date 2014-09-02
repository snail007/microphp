<?php
/**
 * MicroPHP
 * Description of test
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		MicroPHP
 * @author		狂奔的蜗牛
 * @email		672308444@163.com
 * @copyright	        Copyright (c) 2013 - 2013, 狂奔的蜗牛, Inc.
 * @link		http://git.oschina.net/snail/microphp
 * @createdtime         2014-5-29 9:09:37
 */

/**
 * 表单规则助手类，再不用记忆规则名称
 */
class WoniuRule {

    /**
     * 规则说明：<br/>
     * 如果元素为空，则返回FALSE<br/><br/><br/>
     */
    public static function required() {
        return 'required';
    }

    /**
     * 规则说明：<br/>
     * 当没有post对应字段的值或者值为空的时候那么就会使用默认规则的值作为该字段的值。<br/>
     * 然后用这个值继续 后面的规则进行验证。<br/>
     * @param string $val 默认值<br/><br/><br/>
     */
    public static function defaultVal($val = '') {
        return 'default[' . $val . ']';
    }

    /**
     * 规则说明：<br/>
     * 可以为空规则。例如user字段规则中有optional,当没有传递字段user的值或者值是空的时候，<br/> 
     * user验证会通过(忽略其它规则即使有required规则)， <br/>
     * 提示： <br/>
     * $this->checkData($rule, $_POST, $ret_data)返回的数据$ret_data， <br/>
     * 如果传递了user字段$ret_data就有user字段，反之没有user字段. <br/>
     * 如果user传递有值，那么就会用这个值继续后面的规则进行验证。<br/><br/><br/>
     */
    public static function optional() {
        return 'optional';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素的值与参数中对应的表单字段的值不相等，则返回FALSE<br/>
     * @param string $field_name 表单字段名称<br/><br/><br/>
     */
    public static function match($field_name) {
        return 'match[' . $field_name . ']';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素的值不与指定的值相等，则返回FALSE<br/>
     * @param string $val 指定的值<br/><br/><br/>
     */
    public static function equal($val) {
        return 'equal[' . $val . ']';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值不在指定的几个值中，则返回FALSE<br/>
     * @param string $val 规则内容,多个值用逗号分割，或者用第个参数指定的分割符<br/>
     * @param string $delimiter 规则内容的分割符，比如：# ，默认为空即可<br/><br/><br/>
     */
    public static function enum($val, $delimiter = '') {
        return 'enum[' . $val . ']' . $delimiter;
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素的值与指定数据表栏位有重复，则返回False<br/>
     * 比如unique[user.email]，那么验证类会去查找user表中email字段有没有与表单元素一样的值，<br/>
     * 如存重复，则返回false，这样开发者就不必另写callback验证代码。<br/>
     * 如果指定了id:1,那么除了id为1之外的记录的email字段不能与表单元素一样，<br/>
     * 如果一样返回false<br/>
     * @param string $val 规则内容，比如：1、table.field 2、table.field,id:1<br/>
     * @param string $delimiter 规则内容的分割符，比如：# ，默认为空即可<br/><br/><br/>
     */
    public static function unique($val, $delimiter = '') {
        return 'unique[' . $val . ']' . $delimiter;
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素的值在指定数据表的字段中不存在则返回false，如果存在返回true<br/>
     * 比如exists[cat.cid]，那么验证类会去查找cat表中cid字段有没有与表单元素一样的值<br/>
     * cat.cid后面还可以指定附加的where条件<br/>
     * 比如：exists[users.uname,user_id:2,...] 可以多个条件，逗号分割。<br/>
     * 上面的规测生成的where就是array('uname'=>$value,'user_id'=>2,....)<br/>
     * @param string $val 规则内容，比如：1、table.field 2、table.field,id:1<br/>
     * @param string $delimiter 规则内容的分割符，比如：# ，默认为空即可<br/><br/><br/>
     */
    public static function exists($val, $delimiter = '') {
        return 'exists[' . $val . ']' . $delimiter;
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值的字符长度小于参数定义的值，则返回FALSE<br/>
     * @param int $val 长度数值<br/><br/><br/>
     */
    public static function min_len($val) {
        return 'min_len[' . $val . ']';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值的字符长度小于参数定义的值，则返回FALSE<br/>
     * @param int $val 长度数值<br/><br/><br/>
     */
    public static function max_len($val) {
        return 'min_len[' . $val . ']';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值的字符长度不在指定的范围，则返回FALSE<br/>
     * @param int $min_len 最小长度数值<br/>
     * @param int $max_len 最大长度数值<br/><br/><br/>
     */
    public static function range_len($min_len, $max_len) {
        return 'range_len[' . $min_len . ',' . $max_len . ']';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值的字符长度不是指定的长度，则返回FALSE<br/>
     * @param int $val 长度数值<br/><br/><br/>
     */
    public static function len($val) {
        return 'len[' . $val . ']';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值不是数字或者小于指定的值，则返回FALSE<br/>
     * @param int $val 数值<br/><br/><br/>
     */
    public static function min($val) {
        return 'min[' . $val . ']';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值不是数字或者大于指定的值，则返回FALSE<br/>
     * @param int $val 数值<br/><br/><br/>
     */
    public static function max($val) {
        return 'max[' . $val . ']';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值不是数字或者大小不在指定的范围内，则返回 FALSE<br/>
     * @param int $min 最小数值<br/>
     * @param int $max 最大数值<br/><br/><br/>
     */
    public static function range($min, $max) {
        return 'range[' . $min . ',' . $max . ']';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素中包含除字母以外的字符，则返回FALSE<br/><br/><br/>
     */
    public static function alpha() {
        return 'alpha';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素中包含除字母和数字以外的字符，则返回FALSE<br/><br/><br/>
     */
    public static function alpha_num() {
        return 'alpha_num';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值中包含除字母/数字/下划线/破折号以外的其他字符，则返回FALSE<br/><br/><br/>
     */
    public static function alpha_dash() {
        return 'alpha_dash';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素中不是字母开头，则返回FALSE<br/><br/><br/>
     */
    public static function alpha_start() {
        return 'alpha_start';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素中不是纯数字，则返回FALSE<br/><br/><br/>
     */
    public static function num() {
        return 'num';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素中不是整数，则返回FALSE<br/><br/><br/>
     */
    public static function int() {
        return 'int';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素中不是小数，则返回FALSE<br/><br/><br/>
     */
    public static function float() {
        return 'float';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素中不是一个数，则返回FALSE<br/><br/><br/>
     */
    public static function numeric() {
        return 'numeric';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值中包含了非自然数的其他数值 （其他数值不包括零），则返回FALSE。<br/><br/><br/>
     * 自然数形如：0,1,2,3....等等。
     */
    public static function natural() {
        return 'natural';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值包含了非自然数的其他数值 （其他数值包括零），则返回FALSE。<br/><br/><br/>
     * 非零的自然数：1,2,3.....等等。
     */
    public static function natural_no_zero() {
        return 'natural_no_zero';
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值不是一个网址，则返回FALSE<br/>
     * @param boolean $can_empty 是否允许为空。true:允许 false:不允许。默认：false<br/><br/><br/>
     */
    public static function url($can_empty = false) {
        return self::can_empty_rule('qq', $can_empty);
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值包含不合法的email地址，则返回FALSE<br/>
     * @param boolean $can_empty 是否允许为空。true:允许 false:不允许。默认：false<br/><br/><br/>
     */
    public static function email($can_empty = false) {
        return self::can_empty_rule('email', $can_empty);
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值不是一个QQ号，则返回FALSE<br/>
     * @param boolean $can_empty 是否允许为空。true:允许 false:不允许。默认：false<br/><br/><br/>
     */
    public static function qq($can_empty = false) {
        return self::can_empty_rule('qq', $can_empty);
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值不是一个电话号码，则返回FALSE<br/>
     * @param boolean $can_empty 是否允许为空。true:允许 false:不允许。默认：false<br/><br/><br/>
     */
    public static function phone($can_empty = false) {
        return self::can_empty_rule('phone', $can_empty);
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值不是一个手机号，则返回FALSE<br/>
     * @param boolean $can_empty 是否允许为空。true:允许 false:不允许。默认：false<br/><br/><br/>
     */
    public static function mobile($can_empty = false) {
        return self::can_empty_rule('mobile', $can_empty);
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值不是一个邮政编码，则返回FALSE<br/>
     * @param boolean $can_empty 是否允许为空。true:允许 false:不允许。默认：false<br/><br/><br/>
     */
    public static function zipcode($can_empty = false) {
        return self::can_empty_rule('zipcode', $can_empty);
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值不是一个身份证号，则返回FALSE<br/>
     * @param boolean $can_empty 是否允许为空。true:允许 false:不允许。默认：false<br/><br/><br/>
     */
    public static function idcard($can_empty = false) {
        return self::can_empty_rule('idcard', $can_empty);
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值不是一个合法的IPv4地址，则返回FALSE。<br/>
     * @param boolean $can_empty 是否允许为空。true:允许 false:不允许。默认：false<br/><br/><br/>
     */
    public static function ip($can_empty = false) {
        return self::can_empty_rule('ip', $can_empty);
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值不是汉字，或者不是指定的长度，则返回FALSE<br/>
     * 规则示例：<br/>
     * 1.规则内容：false    描述：必须是汉字，不能为空<br/>
     * 2.规则内容：true     描述：必须是汉字，可以为空<br/>
     * 3.规则内容：false,2  描述：必须是2个汉字，不能为空<br/>
     * 4.规则内容：true,2   描述：必须是2个汉字，可以为空<br/>
     * 5.规则内容：true,2,3 描述：必须是2-3个汉字，可以为空<br/>
     * 6.规则内容：false,2, 描述：必须是2个以上汉字，不能为空<br/>
     * @param boolean $val 规则内容。默认为空，即规则：必须是汉字不能为空<br/>
     * @param string $delimiter 规则内容的分割符，比如：# ，默认为空即可<br/><br/><br/>
     */
    public static function chs($val = '', $delimiter = '') {
        return 'chs' . ($val ? '[' . $val . ']' . $delimiter : '');
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值不是正确的日期格式YYYY-MM-DD，则返回FALSE<br/>
     * @param boolean $can_empty 是否允许为空。true:允许 false:不允许。默认：false<br/><br/><br/>
     */
    public static function date($can_empty = false) {
        return self::can_empty_rule('date', $can_empty);
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值不是正确的日期时间格式YYYY-MM-DD HH:MM:SS，则返回FALSE<br/>
     * @param boolean $can_empty 是否允许为空。true:允许 false:不允许。默认：false<br/><br/><br/>
     */
    public static function datetime($can_empty = false) {
        return self::can_empty_rule('datetime', $can_empty);
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值不是正确的时间格式HH:MM:SS，则返回FALSE<br/>
     * @param boolean $can_empty 是否允许为空。true:允许 false:不允许。默认：false<br/><br/><br/>
     */
    public static function time($can_empty = false) {
        return self::can_empty_rule('time', $can_empty);
    }

    /**
     * 规则说明：<br/>
     * 如果表单元素值不匹配指定的正则表达式，则返回FALSE<br/>
     * @param string $val 正则表达式。比如：1./^[]]$/ 2./^A$/i<br/>
     * 模式修正符说明:<br/>
     * i 表示在和模式进行匹配进不区分大小写<br/>
     * m 将模式视为多行，使用^和$表示任何一行都可以以正则表达式开始或结束<br/>
     * s 如果没有使用这个模式修正符号，元字符中的"."默认不能表示换行符号,将字符串视为单行<br/>
     * x 表示模式中的空白忽略不计<br/>
     * e 正则表达式必须使用在preg_replace替换字符串的函数中时才可以使用(讲这个函数时再说)<br/>
     * A 以模式字符串开头，相当于元字符^<br/>
     * Z 以模式字符串结尾，相当于元字符$<br/>
     * U 正则表达式的特点：就是比较“贪婪”，使用该模式修正符可以取消贪婪模式<br/><br/><br/>
     */
    public static function reg($val) {
        return 'reg[' . $val . ']';
    }

    /**
     * 规则说明：<br/>
     * 数据在验证之前处理数据的规则，数据在验证的时候验证的是处理过的数据<br/>
     * 注意：<br/>
     * set和set_post后面是一个或者多个函数或者方法，多个逗号分割<br/>
     * 1.无论是函数或者方法都必须有一个字符串返回<br/>
     * 2.如果是系统函数，系统会传递当前值给系统函数，因此系统函数必须是至少接受一个字符串参数<br/>
     * 3.如果是自定义的函数，系统会传递当前值和全部数据给自定义的函数，因此自定义函数可以接收两个参数第一个是值，第二个是全部数据$data<br/>
     * 4.如果是类的方法写法是：类名称::方法名 （方法静态动态都可以，public，private，都可以）<br/>
     * @param string $val 规则内容。比如：trim<br/>
     * @param string $delimiter 规则内容的分割符，比如：# ，默认为空即可<br/><br/><br/>
     */
    public static function set($val, $delimiter = '') {
        return 'set[' . $val . ']' . $delimiter;
    }

    /**
     * 规则说明：<br/>
     * 数据在验证通过之后处理数据的规则，$this->checkData()第三个变量接收的就是set和set_post处理过的数据<br/>
     * 注意：<br/>
     * set和set_post后面是一个或者多个函数或者方法，多个逗号分割<br/>
     * 1.无论是函数或者方法都必须有一个字符串返回<br/>
     * 2.如果是系统函数，系统会传递当前值给系统函数，因此系统函数必须是至少接受一个字符串参数<br/>
     * 3.如果是自定义的函数，系统会传递当前值和全部数据给自定义的函数，因此自定义函数可以接收两个参数第一个是值，第二个是全部数据$data<br/>
     * 4.如果是类的方法写法是：类名称::方法名 （方法静态动态都可以，public，private，都可以）<br/>
     * @param string $val 规则内容。比如：sha1,md5<br/>
     * @param string $delimiter 规则内容的分割符，比如：# ，默认为空即可<br/><br/><br/>
     */
    public static function set_post($val, $delimiter = '') {
        return 'set_post[' . $val . ']' . $delimiter;
    }

    private static function can_empty_rule($rule_name, $can_empty) {
        return $rule_name . ($can_empty ? '[true]' : '');
    }

}