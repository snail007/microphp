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
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package                MicroPHP
 * @author                 狂奔的蜗牛
 * @email                  672308444@163.com
 * @copyright              Copyright (c) 2013 - 2013, 狂奔的蜗牛, Inc.
 * @link                   http://git.oschina.net/snail/microphp
 * @since                  Version 1.0
 * @createdtime            2013-12-8 10:28:53
 */
class WoniuDebuger {

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

    public function getLogFile() {
        return $this->logFile;
    }

    public function setLogFile($logFile) {
        $this->logFile = $logFile;
    }

    public function mark($flag) {
        $this->times[] = array('flag' => $flag, 'time' => $this->getMillisecond());
    }

    public function getOutput($is_br = false) {
        if (count($this->times) >= 2) {
            $str_arr = array();
            for ($i = 1; $i < count($this->times); $i++) {
                $str_arr[] = "{$i}. {$this->times[$i]['flag']}->{$this->times[$i - 1]['flag']} : "
                        . $this->formatTime($this->times[$i]['time'] - $this->times[$i - 1]['time']);
            }
            $this->reset();

            return implode(($is_br ? '<br/>' : "\n"), $str_arr);
        }
        return '';
    }

    public function show($is_html = false) {
        if ($is_html) {
            echo "<pre>" . $this->getOutput(true) . "</pre>";
        } else {
            echo $this->getOutput();
        }
    }

    public function showToFile($filename = null) {
        $this->writeLog($this->getOutput(), $filename);
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
        $timeInfo = ($seconds ? $seconds . 's' : '') . ($ms ? ($seconds ? ',' : '') . $ms . 'ms' : '');

        return $timeInfo;
    }

    public function reset() {
        $this->times = array();
    }

    //取得当前时间的毫秒
    private function getMillisecond() {
        list($s1, $s2) = explode(' ', microtime());
        return (float) sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

}
