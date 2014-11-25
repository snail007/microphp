<?php

/*
 * Copyright 2013 Snail.
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
 * @package                MicroPHP
 * @author                 狂奔的蜗牛
 * @email                  672308444@163.com
 * @copyright              Copyright (c) 2013 - 2013, 狂奔的蜗牛, Inc.
 * @link                   http://git.oschina.net/snail/microphp
 * @since                  Version 1.0
 * @createdtime            2013-12-8 10:28:53
 */
class MpDebuger {

    private $logFile = './logs/time_debug.log';
    private $times = array();
    private $maxLogFileSize = 100; //KB

    /**
     * 设置日志文件最大大小，单位KB 
     * @param type $maxLogFileSize
     */

    public function setMaxLogFileSize($maxLogFileSize) {
	$this->maxLogFileSize = $maxLogFileSize;
    }

    /**
     * 设置日志文件路径
     * @param type $logFile
     */
    public function setLogFile($logFile) {
	$this->logFile = $logFile;
    }

    /**
     * 清空所有mark的时间点，用于重新开始测试
     */
    public function reset() {
	$this->times = array();
    }

    /**
     * 设置一个时间标记点
     * @param type $flag
     */
    public function mark($flag) {
	$this->times[] = array('flag' => $flag, 'time' => $this->getMillisecond());
    }

    /**
     * 获取格式化过的时间信息内容
     * @param type $is_br 换行符是否使用&lt;br/&gt;
     * @return string
     */
    public function getOutput($is_br = false) {

	if (count($this->times) >= 2) {
	    $max = 0;
	    for ($i = 1; $i < count($this->times); $i++) {
		$cost=$this->times[$i]['time'] - $this->times[$i - 1]['time'];
		$max < $cost ? $max = $cost : null;
	    }
	    for ($i = 1; $i < count($this->times); $i++) {
		$cost = $this->times[$i]['time'] - $this->times[$i - 1]['time'];
		$str_arr[] = ($is_br && $cost >= $max ? '<font color="red">' : "") . "{$i}. {$this->times[$i - 1]['flag']} -> {$this->times[$i]['flag']} : "
			. $this->formatTime($cost) . ($is_br && $cost >= $max ? '</font>' : "");
	    }
	    $total = isset($this->times[0]['time']) ? $this->times[count($this->times) - 1]['time'] - $this->times[0]['time'] : 0;
	    $str_arr[] = 'Total : ' . $total . ' ms';
	    return implode(($is_br ? '<br/>' : "\n"), $str_arr);
	}
	return '';
    }

    /**
     * 显示格式化过的时间信息
     * @param type $is_html 是否使用html格式输出，true:html false:纯文本
     */
    public function show($is_html = false) {
	if ($is_html) {
	    echo "<pre>" . $this->getOutput(true) . "</pre>";
	} else {
	    echo $this->getOutput();
	}
    }

    /**
     * 把格式化过的时间信息写到日志文件
     * @param type $filename 文件路径
     */
    public function showToFile($filename = null) {
	$content = $this->getOutput();
	$content = $content . "\n" . $this->getUrl() . "\nIsAjax:" . (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' ? 'true' : 'false') . ""
		. "\nIP:" . (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '')
		. "\n" . (!empty($_POST) ? 'Post Data:' . var_export($_POST, TRUE) . "\n" : '') . "TimeInfo:\n" . $content;
	$content = date('Y-m-d H:i:s') . $content . "\n\n";
	$this->writeLog($content, $filename);
    }

    private function writeLog($content, $filename = null) {
	$filename = $filename ? $filename : $this->logFile;
	$dir = dirname($filename);
	if (!is_dir($dir)) {
	    mkdir($dir, 0755, true);
	}
	$filesize = @intval(filesize($filename));
	$filesize = $filesize / 1024;
	if ($filesize > $this->maxLogFileSize) {
	    @unlink($filename);
	}
	@file_put_contents($filename, $content, FILE_APPEND | LOCK_EX);
    }

    private function formatTime($milliseconds) {
	$useTime = $milliseconds;
	$seconds = floor($useTime / 1000);
	$ms = ($useTime - $seconds * 1000);
	$timeInfo = ($seconds ? $seconds . ' s' : '') . ($ms ? ($seconds ? ',' : '') . $ms . ' ms' : ($seconds ? '' : '0 ms'));

	return $timeInfo;
    }

    //取得当前时间的毫秒
    private function getMillisecond() {
	list($s1, $s2) = explode(' ', microtime());
	return (float) sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

    private function getUrl() {
	return (empty($_SERVER['REQUEST_METHOD']) ? 'URL' : strtoupper($_SERVER['REQUEST_METHOD'])) . ':http://' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '');
    }

}
