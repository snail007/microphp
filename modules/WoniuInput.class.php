<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package                MicroPHP
 * @author                狂奔的蜗牛
 * @email                672308444@163.com
 * @copyright          Copyright (c) 2013 - 2013, 狂奔的蜗牛, Inc.
 * @link                http://git.oschina.net/snail/microphp
 * @since                Version 1.0
 * @createdtime       {createdtime}
 */
class WoniuInput {

    public static $router;

    public static function get_post($key = null, $default = null) {
        $get = self::gpcs('_GET', $key, $default);
        return $get === null ? self::gpcs('_POST', $key, $default) : $get;
    }

    public static function get($key = null, $default = null) {
        return self::gpcs('_GET', $key, $default);
    }

    public static function post($key = null, $default = null) {
        return self::gpcs('_POST', $key, $default);
    }

    public static function cookie($key = null, $default = null) {
        return self::gpcs('_COOKIE', $key, $default);
    }

    public static function session($key = null, $default = null) {
        return self::gpcs('_SESSION', $key, $default);
    }

    public static function server($key = null, $default = null) {
        $key = strtoupper($key);
        return self::gpcs('_SERVER', $key, $default);
    }

    private static function gpcs($range, $key, $default) {
        global $$range;
        if ($key === null) {
            return $$range;
        } else {
            $range = $$range;
            return isset($range[$key]) ? $range[$key] : ( $default !== null ? $default : null);
        }
    }

    public static function isCli() {
        return php_sapi_name() == 'cli';
    }

}

/*
 * khoaofgod@yahoo.com
 * Website: http://www.phpfastcache.com
 * Example at our website, any bugs, problems, please visit http://www.codehelper.io
 */


interface phpfastcache_driver {
    /*
     * Check if this Cache driver is available for server or not
     */
     function __construct($option = array());

     function checkdriver();

    /*
     * SET
     * set a obj to cache
     */
     function driver_set($keyword, $value = "", $time = 300, $option = array() );

    /*
     * GET
     * return null or value of cache
     */
     function driver_get($keyword, $option = array());

    /*
     * Stats
     * Show stats of caching
     * Return array ("info","size","data")
     */
     function driver_stats($option = array());

    /*
     * Delete
     * Delete a cache
     */
     function driver_delete($keyword, $option = array());

    /*
     * clean
     * Clean up whole cache
     */
     function driver_clean($option = array());





}



/*
 * khoaofgod@yahoo.com
 * Website: http://www.phpfastcache.com
 * Example at our website, any bugs, problems, please visit http://www.codehelper.io
 */


class phpfastcache_memcache extends phpFastCache implements phpfastcache_driver {

    var $instant;

    function checkdriver() {
        // Check memcache
        if(function_exists("memcache_connect")) {
            return true;
        }
        return false;
    }

    function __construct($option = array()) {
        $this->setOption($option);
        if(!$this->checkdriver() && !isset($option['skipError'])) {
            throw new Exception("Can't use this driver for your website!");
        }
        if ($this->checkdriver() && !is_object($this->instant)) {
            $this->instant = new Memcache();
        }
    }

    function connectServer() {
        $server = $this->option['server'];
        if(count($server) < 1) {
            $server = array(
                array("127.0.0.1",11211),
            );
        }

        foreach($server as $s) {
            $name = $s[0]."_".$s[1];
            if(!isset($this->checked[$name])) {
                $this->instant->addserver($s[0],$s[1]);
                $this->checked[$name] = 1;
            }

        }
    }

    function driver_set($keyword, $value = "", $time = 300, $option = array() ) {
        $this->connectServer();
        if(isset($option['skipExisting']) && $option['skipExisting'] == true) {
            return $this->instant->add($keyword, $value, false, $time );

        } else {
            return $this->instant->set($keyword, $value, false, $time );
        }

    }

    function driver_get($keyword, $option = array()) {
        $this->connectServer();
        // return null if no caching
        // return value if in caching
        $x = $this->instant->get($keyword);
        if($x == false) {
            return null;
        } else {
            return $x;
        }
    }

    function driver_delete($keyword, $option = array()) {
        $this->connectServer();
         $this->instant->delete($keyword);
    }

    function driver_stats($option = array()) {
        $this->connectServer();
        $res = array(
            "info"  => "",
            "size"  =>  "",
            "data"  => $this->instant->getStats(),
        );

        return $res;

    }

    function driver_clean($option = array()) {
        $this->connectServer();
        $this->instant->flush();
    }

    function driver_isExisting($keyword) {
        $this->connectServer();
        $x = $this->get($keyword);
        if($x == null) {
            return false;
        } else {
            return true;
        }
    }



}

/*
 * khoaofgod@yahoo.com
 * Website: http://www.phpfastcache.com
 * Example at our website, any bugs, problems, please visit http://www.codehelper.io
 */

class phpfastcache_wincache extends phpFastCache implements phpfastcache_driver  {

    function checkdriver() {
        if(extension_loaded('wincache') && function_exists("wincache_ucache_set"))
        {
            return true;
        }
        return false;
    }

    function __construct($option = array()) {
        $this->setOption($option);
        if(!$this->checkdriver() && !isset($option['skipError'])) {
            throw new Exception("Can't use this driver for your website!");
        }

    }

    function driver_set($keyword, $value = "", $time = 300, $option = array() ) {
        if(isset($option['skipExisting']) && $option['skipExisting'] == true) {
            return wincache_ucache_add($keyword, $value, $time);
        } else {
            return wincache_ucache_set($keyword, $value, $time);
        }
    }

    function driver_get($keyword, $option = array()) {
        // return null if no caching
        // return value if in caching

        $x = wincache_ucache_get($keyword,$suc);

        if($suc == false) {
            return null;
        } else {
            return $x;
        }
    }

    function driver_delete($keyword, $option = array()) {
        return wincache_ucache_delete($keyword);
    }

    function driver_stats($option = array()) {
        $res = array(
            "info"  =>  "",
            "size"  =>  "",
            "data"  =>  wincache_scache_info(),
        );
        return $res;
    }

    function driver_clean($option = array()) {
        wincache_ucache_clear();
        return true;
    }

    function driver_isExisting($keyword) {
        if(wincache_ucache_exists($keyword)) {
            return true;
        } else {
            return false;
        }
    }



}


/*
 * khoaofgod@yahoo.com
 * Website: http://www.phpfastcache.com
 * Example at our website, any bugs, problems, please visit http://www.codehelper.io
 */


class phpfastcache_apc extends phpFastCache implements phpfastcache_driver {
    function checkdriver() {
        // Check apc
        if(extension_loaded('apc') && ini_get('apc.enabled'))
        {
            return true;
        } else {
            return false;
        }
    }

    function __construct($option = array()) {
        $this->setOption($option);
        if(!$this->checkdriver() && !isset($option['skipError'])) {
            throw new Exception("Can't use this driver for your website!");
        }
    }

    function driver_set($keyword, $value = "", $time = 300, $option = array() ) {
        if(isset($option['skipExisting']) && $option['skipExisting'] == true) {
            return apc_add($keyword,$value,$time);
        } else {
            return apc_store($keyword,$value,$time);
        }
    }

    function driver_get($keyword, $option = array()) {
        // return null if no caching
        // return value if in caching

        $data = apc_fetch($keyword,$bo);
        if($bo === false) {
            return null;
        }
        return $data;

    }

    function driver_delete($keyword, $option = array()) {
        return apc_delete($keyword);
    }

    function driver_stats($option = array()) {
        $res = array(
            "info" => "",
            "size"  => "",
            "data"  =>  "",
        );

        try {
            $res['data'] = apc_cache_info("user");
        } catch(Exception $e) {
            $res['data'] =  array();
        }

        return $res;
    }

    function driver_clean($option = array()) {
        @apc_clear_cache();
        @apc_clear_cache("user");
    }

    function driver_isExisting($keyword) {
        if(apc_exists($keyword)) {
            return true;
        } else {
            return false;
        }
    }





}


/*
 * khoaofgod@yahoo.com
 * Website: http://www.phpfastcache.com
 * Example at our website, any bugs, problems, please visit http://www.codehelper.io
 */

class phpfastcache_xcache extends phpFastCache implements phpfastcache_driver  {

    function checkdriver() {
        // Check xcache
        if(extension_loaded('xcache') && function_exists("xcache_get"))
        {
           return true;
        }
        return false;

    }

    function __construct($option = array()) {
        $this->setOption($option);
        if(!$this->checkdriver() && !isset($option['skipError'])) {
            throw new Exception("Can't use this driver for your website!");
        }

    }

    function driver_set($keyword, $value = "", $time = 300, $option = array() ) {

        if(isset($option['skipExisting']) && $option['skipExisting'] == true) {
            if(!$this->isExisting($keyword)) {
                return xcache_set($keyword,$value,$time);
            }
        } else {
            return xcache_set($keyword,$value,$time);
        }
        return false;
    }

    function driver_get($keyword, $option = array()) {
        // return null if no caching
        // return value if in caching
        $data = xcache_get($keyword);
        if($data === false || $data == "") {
            return null;
        }
        return $data;
    }

    function driver_delete($keyword, $option = array()) {
        return xcache_unset($keyword);
    }

    function driver_stats($option = array()) {
        $res = array(
            "info"  =>  "",
            "size"  =>  "",
            "data"  =>  "",
        );

        try {
            $res['data'] = xcache_list(XC_TYPE_VAR,100);
        } catch(Exception $e) {
            $res['data'] = array();
        }
        return $res;
    }

    function driver_clean($option = array()) {
        $cnt = xcache_count(XC_TYPE_VAR);
        for ($i=0; $i < $cnt; $i++) {
            xcache_clear_cache(XC_TYPE_VAR, $i);
        }
        return true;
    }

    function driver_isExisting($keyword) {
        if(xcache_isset($keyword)) {
            return true;
        } else {
            return false;
        }
    }



}


/*
 * khoaofgod@yahoo.com
 * Website: http://www.phpfastcache.com
 * Example at our website, any bugs, problems, please visit http://www.codehelper.io
 */


class phpfastcache_sqlite extends phpFastCache implements phpfastcache_driver  {
    var $max_size = 10; // 10 mb

    var $instant = array();
    var $indexing = NULL;
    var $path = "";

    var $currentDB = 1;

    /*
     * INIT NEW DB
     */
    function initDB(PDO $db) {
        $db->exec('drop table if exists "caching"');
        $db->exec('CREATE TABLE "caching" ("id" INTEGER PRIMARY KEY AUTOINCREMENT, "keyword" VARCHAR UNIQUE, "object" BLOB, "exp" INTEGER)');
        $db->exec('CREATE UNIQUE INDEX "cleaup" ON "caching" ("keyword","exp")');
        $db->exec('CREATE INDEX "exp" ON "caching" ("exp")');
        $db->exec('CREATE UNIQUE INDEX "keyword" ON "caching" ("keyword")');
    }

    /*
     * INIT Indexing DB
     */
    function initIndexing(PDO $db) {

        // delete everything before reset indexing
        $dir = opendir($this->path);
        while($file = readdir($dir)) {
            if($file != "." && $file!=".." && $file != "indexing" && $file!="dbfastcache") {
                unlink($this->path."/".$file);
            }
        }

        $db->exec('drop table if exists "balancing"');
        $db->exec('CREATE TABLE "balancing" ("keyword" VARCHAR PRIMARY KEY NOT NULL UNIQUE, "db" INTEGER)');
        $db->exec('CREATE INDEX "db" ON "balancing" ("db")');
        $db->exec('CREATE UNIQUE INDEX "lookup" ON "balacing" ("keyword")');

    }

    /*
     * INIT Instant DB
     * Return Database of Keyword
     */
    function indexing($keyword) {
        if($this->indexing == NULL) {
            $createTable = false;
            if(!file_exists($this->path."/indexing")) {
                $createTable = true;
            }

            $PDO = new PDO("sqlite:".$this->path."/indexing");
            $PDO->setAttribute(PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION);

            if($createTable == true) {
                $this->initIndexing($PDO);
            }
            $this->indexing = $PDO;
            unset($PDO);

            $stm = $this->indexing->prepare("SELECT MAX(`db`) as `db` FROM `balancing`");
            $stm->execute();
            $row = $stm->fetch(PDO::FETCH_ASSOC);
            if(!isset($row['db'])) {
                $db = 1;
            } elseif($row['db'] <=1 ) {
                $db = 1;
            } else {
                $db = $row['db'];
            }

            // check file size

            $size = file_exists($this->path."/db".$db) ? filesize($this->path."/db".$db) : 1;
            $size = round($size / 1024 / 1024,1);


            if($size > $this->max_size) {
                $db = $db + 1;
            }
            $this->currentDB = $db;

        }

        // look for keyword
        $stm = $this->indexing->prepare("SELECT * FROM `balancing` WHERE `keyword`=:keyword LIMIT 1");
        $stm->execute(array(
             ":keyword"  => $keyword
        ));
        $row = $stm->fetch(PDO::FETCH_ASSOC);
        if(isset($row['db']) && $row['db'] != "") {
            $db = $row['db'];
        } else {
            /*
             * Insert new to Indexing
             */
            $db = $this->currentDB;
            $stm = $this->indexing->prepare("INSERT INTO `balancing` (`keyword`,`db`) VALUES(:keyword, :db)");
            $stm->execute(array(
                ":keyword"  => $keyword,
                ":db"       =>  $db,
            ));
        }

        return $db;
    }



    function db($keyword, $reset = false) {
        /*
         * Default is fastcache
         */
        $instant = $this->indexing($keyword);

        /*
         * init instant
         */
        if(!isset($this->instant[$instant])) {
            // check DB Files ready or not
            $createTable = false;
            if(!file_exists($this->path."/db".$instant) || $reset == true) {
                $createTable = true;
            }
            $PDO = new PDO("sqlite:".$this->path."/db".$instant);
            $PDO->setAttribute(PDO::ATTR_ERRMODE,
                               PDO::ERRMODE_EXCEPTION);

            if($createTable == true) {
                $this->initDB($PDO);
            }

            $this->instant[$instant] = $PDO;
            unset($PDO);

        }


        return $this->instant[$instant];
    }



    function checkdriver() {
        if(extension_loaded('pdo_sqlite') && is_writeable($this->getPath())) {
           return true;
        }
        return false;
    }

    /*
     * Init Main Database & Sub Database
     */
    function __construct($option = array()) {
        /*
         * init the path
         */
        $this->setOption($option);
        if(!$this->checkdriver() && !isset($option['skipError'])) {
            throw new Exception("Can't use this driver for your website!");
        }

        if(!file_exists($this->getPath()."/sqlite")) {
            if(!@mkdir($this->getPath()."/sqlite",0777)) {
                die("Sorry, Please CHMOD 0777 for this path: ".$this->getPath());
            }
        }
        $this->path = $this->getPath()."/sqlite";
    }


    function driver_set($keyword, $value = "", $time = 300, $option = array() ) {
        $skipExisting = isset($option['skipExisting']) ? $option['skipExisting'] : false;
        $toWrite = true;

        // check in cache first
        $in_cache = $this->get($keyword,$option);

        if($skipExisting == true) {
            if($in_cache == null) {
                $toWrite = true;
            } else {
                $toWrite = false;
            }
        }

        if($toWrite == true) {
            try {
                $stm = $this->db($keyword)->prepare("INSERT OR REPLACE INTO `caching` (`keyword`,`object`,`exp`) values(:keyword,:object,:exp)");
                $stm->execute(array(
                    ":keyword"  => $keyword,
                    ":object"   =>  $this->encode($value),
                    ":exp"      => @date("U") + (Int)$time,
                ));

                return true;
            } catch(PDOException $e) {
                $stm = $this->db($keyword,true)->prepare("INSERT OR REPLACE INTO `caching` (`keyword`,`object`,`exp`) values(:keyword,:object,:exp)");
                $stm->execute(array(
                    ":keyword"  => $keyword,
                    ":object"   =>  $this->encode($value),
                    ":exp"      => @date("U") + (Int)$time,
                ));
            }


        }

        return false;

    }

    function driver_get($keyword, $option = array()) {
        // return null if no caching
        // return value if in caching
        try {
            $stm = $this->db($keyword)->prepare("SELECT * FROM `caching` WHERE `keyword`=:keyword LIMIT 1");
            $stm->execute(array(
                ":keyword"  =>  $keyword
            ));
            $row = $stm->fetch(PDO::FETCH_ASSOC);

        } catch(PDOException $e) {

            $stm = $this->db($keyword,true)->prepare("SELECT * FROM `caching` WHERE `keyword`=:keyword LIMIT 1");
            $stm->execute(array(
                ":keyword"  =>  $keyword
            ));
            $row = $stm->fetch(PDO::FETCH_ASSOC);
        }


        if($this->isExpired($row)) {
            $this->deleteRow($row);
            return null;
        }



        if(isset($row['id'])) {
            $data = $this->decode($row['object']);
            return $data;
        }


        return null;
    }

    function isExpired($row) {
        if(isset($row['exp']) && @date("U") >= $row['exp']) {
            return true;
        }

        return false;
    }

    function deleteRow($row) {
        $stm = $this->db($row['keyword'])->prepare("DELETE FROM `caching` WHERE (`id`=:id) OR (`exp` <= :U) ");
        $stm->execute(array(
            ":id"   => $row['id'],
            ":U"    =>  @date("U"),
        ));
    }

    function driver_delete($keyword, $option = array()) {
        $stm = $this->db($keyword)->prepare("DELETE FROM `caching` WHERE (`keyword`=:keyword) OR (`exp` <= :U)");
        $stm->execute(array(
            ":keyword"   => $keyword,
            ":U"    =>  @date("U"),
        ));
    }

    function driver_stats($option = array()) {
        $res = array(
            "info"  =>  "",
            "size"  =>  "",
            "data"  =>  "",
        );
        $total = 0;
        $optimized = 0;

        $dir = opendir($this->path);
        while($file = readdir($dir)) {
            if($file!="." && $file!="..") {
                $file_path = $this->path."/".$file;
                $size = filesize($file_path);
                $total = $total + $size;

                $PDO = new PDO("sqlite:".$file_path);
                $PDO->setAttribute(PDO::ATTR_ERRMODE,
                    PDO::ERRMODE_EXCEPTION);

                $stm = $PDO->prepare("DELETE FROM `caching` WHERE `exp` <= :U");
                $stm->execute(array(
                    ":U"    =>  @date("U"),
                ));

                $PDO->exec("VACUUM;");
                $size = filesize($file_path);
                $optimized = $optimized + $size;

            }
        }
        $res['size'] = round($optimized/1024/1024,1);
        $res['info'] = array(
            "total" => round($total/1024/1024,1),
            "optimized" => round($optimized/1024/1024,1),
        );

        return $res;
    }

    function driver_clean($option = array()) {
        // delete everything before reset indexing
        $dir = opendir($this->path);
        while($file = readdir($dir)) {
            if($file != "." && $file!="..") {
                unlink($this->path."/".$file);
            }
        }
    }

    function driver_isExisting($keyword) {
        $stm = $this->db($keyword)->prepare("SELECT COUNT(`id`) as `total` FROM `caching` WHERE `keyword`=:keyword");
        $stm->execute(array(
            ":keyword"   => $keyword
        ));
        $data = $stm->fetch(PDO::FETCH_ASSOC);
        if($data['total'] >= 1) {
            return true;
        } else {
            return false;
        }
    }


}

/*
 * khoaofgod@yahoo.com
 * Website: http://www.phpfastcache.com
 * Example at our website, any bugs, problems, please visit http://www.codehelper.io
 */

class phpfastcache_files extends  phpFastCache implements phpfastcache_driver  {

    function checkdriver() {
        if(is_writable($this->getPath())) {
            return true;
        } else {

        }
        return false;
    }

    /*
     * Init Cache Path
     */
    function __construct($option = array()) {

        $this->setOption($option);
        $this->getPath();

        if(!$this->checkdriver() && !isset($option['skipError'])) {
            throw new Exception("Can't use this driver for your website!");
        }

    }

    /*
     * Return $FILE FULL PATH
     */
    private function getFilePath($keyword, $skip = false) {
        $path = $this->getPath();
        $code = md5($keyword);
        $folder = substr($code,0,2);
        $path = $path."/".$folder;
        /*
         * Skip Create Sub Folders;
         */
        if($skip == false) {
            if(!file_exists($path)) {
                if(!@mkdir($path,0777)) {
                    throw new Exception("PLEASE CHMOD ".$this->getPath()." - 0777 OR ANY WRITABLE PERMISSION!",92);
                }

            } elseif(!is_writeable($path)) {
                @chmod($path,0777);
            }
        }

        $file_path = $path."/".$code.".txt";
        return $file_path;
    }


    function driver_set($keyword, $value = "", $time = 300, $option = array() ) {
        $file_path = $this->getFilePath($keyword);
      //  echo "<br>DEBUG SET: ".$keyword." - ".$value." - ".$time."<br>";
        $data = $this->encode($value);

        $toWrite = true;
        /*
         * Skip if Existing Caching in Options
         */
        if(isset($option['skipExisting']) && $option['skipExisting'] == true && file_exists($file_path)) {
            $content = $this->readfile($file_path);
            $old = $this->decode($content);
            $toWrite = false;
            if($this->isExpired($old)) {
                $toWrite = true;
            }
        }

        if($toWrite == true) {
                $f = fopen($file_path,"w+");
                fwrite($f,$data);
                fclose($f);
        }
    }




    function driver_get($keyword, $option = array()) {

        $file_path = $this->getFilePath($keyword);
        if(!file_exists($file_path)) {
            return null;
        }

        $content = $this->readfile($file_path);
        $object = $this->decode($content);
        if($this->isExpired($object)) {
            @unlink($file_path);
            $this->auto_clean_expired();
            return null;
        }

        return $object;
    }

    function driver_delete($keyword, $option = array()) {
        $file_path = $this->getFilePath($keyword,true);
        if(@unlink($file_path)) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * Return total cache size + auto removed expired files
     */
    function driver_stats($option = array()) {
        $res = array(
            "info"  =>  "",
            "size"  =>  "",
            "data"  =>  "",
        );

        $path = $this->getPath();
        $dir = @opendir($path);
        if(!$dir) {
            throw new Exception("Can't read PATH:".$path,94);
        }

        $total = 0;
        $removed = 0;
        while($file=readdir($dir)) {
            if($file!="." && $file!=".." && is_dir($path."/".$file)) {
                // read sub dir
                $subdir = @opendir($path."/".$file);
                if(!$subdir) {
                    throw new Exception("Can't read path:".$path."/".$file,93);
                }

                while($f = readdir($subdir)) {
                    if($f!="." && $f!="..") {
                        $file_path = $path."/".$file."/".$f;
                        $size = filesize($file_path);
                        $object = $this->decode($this->readfile($file_path));
                        if($this->isExpired($object)) {
                            unlink($file_path);
                            $removed = $removed + $size;
                        }
                        $total = $total + $size;
                    }
                } // end read subdir
            } // end if
       } // end while

       $res['size']  = $total - $removed;
       $res['info'] = array(
                "Total" => $total,
                "Removed"   => $removed,
                "Current"   => $res['size'],
       );
       return $res;
    }

    function auto_clean_expired() {
        $autoclean = $this->get("keyword_clean_up_driver_files");
        if($autoclean == null) {
            $this->set("keyword_clean_up_driver_files",3600*24);
            $res = $this->stats();
        }
    }

    function driver_clean($option = array()) {

        $path = $this->getPath();
        $dir = @opendir($path);
        if(!$dir) {
            throw new Exception("Can't read PATH:".$path,94);
        }

        while($file=readdir($dir)) {
            if($file!="." && $file!=".." && is_dir($path."/".$file)) {
                // read sub dir
                $subdir = @opendir($path."/".$file);
                if(!$subdir) {
                    throw new Exception("Can't read path:".$path."/".$file,93);
                }

                while($f = readdir($subdir)) {
                    if($f!="." && $f!="..") {
                        $file_path = $path."/".$file."/".$f;
                        unlink($file_path);
                    }
                } // end read subdir
            } // end if
        } // end while


    }


    function driver_isExisting($keyword) {
        $file_path = $this->getFilePath($keyword,true);
        if(!file_exists($file_path)) {
            return false;
        } else {
            // check expired or not
            $value = $this->get($keyword);
            if($value == null) {
                return false;
            } else {
                return true;
            }
        }
    }

    function isExpired($object) {

        if(isset($object['expired_time']) && @date("U") >= $object['expired_time']) {
            return true;
        } else {
            return false;
        }
    }




}



/*
 * khoaofgod@yahoo.com
 * Website: http://www.phpfastcache.com
 * Example at our website, any bugs, problems, please visit http://www.codehelper.io
 */


class phpfastcache_example extends phpFastCache implements phpfastcache_driver  {

    function checkdriver() {
        // return true;
        return false;
    }



    function __construct($option = array()) {
        $this->setOption($option);
        if(!$this->checkdriver() && !isset($option['skipError'])) {
            throw new Exception("Can't use this driver for your website!");
        }

    }

    function driver_set($keyword, $value = "", $time = 300, $option = array() ) {
        if(isset($option['skipExisting']) && $option['skipExisting'] == true) {
            // skip driver
        } else {
            // add driver
        }

    }

    function driver_get($keyword, $option = array()) {
        // return null if no caching
        // return value if in caching

        return null;
    }

    function driver_delete($keyword, $option = array()) {

    }

    function driver_stats($option = array()) {
        $res = array(
            "info"  => "",
            "size"  =>  "",
            "data"  => "",
        );

        return $res;
    }

    function driver_clean($option = array()) {

    }

    function driver_isExisting($keyword) {

    }



}


/*
 * khoaofgod@yahoo.com
 * Website: http://www.phpfastcache.com
 * Example at our website, any bugs, problems, please visit http://www.codehelper.io
 */

class phpfastcache_memcached extends phpFastCache implements phpfastcache_driver {

    var $instant;

    function checkdriver() {
        if (class_exists("Memcached")) {
            return true;
        }
        return false;
    }

    function __construct($option = array()) {
        $this->setOption($option);
        if (!$this->checkdriver() && !isset($option['skipError'])) {
            throw new Exception("Can't use this driver for your website!");
        }

        if ($this->checkdriver() && !is_object($this->instant)) {
            $this->instant = new Memcached();
        }
    }

    function connectServer() {
        $s = $this->option['server'];
        if (count($s) < 1) {
            $s = array(
                array("127.0.0.1", 11211, 100),
            );
        }

        foreach ($s as $server) {
            $name = isset($server[0]) ? $server[0] : "127.0.0.1";
            $port = isset($server[1]) ? $server[1] : 11211;
            $sharing = isset($server[2]) ? $server[2] : 0;
            $checked = $name . "_" . $port;
            if (!isset($this->checked[$checked])) {
                if ($sharing > 0) {
                    $this->instant->addServer($name, $port, $sharing);
                } else {
                    $this->instant->addServer($name, $port);
                }
                $this->checked[$checked] = 1;
            }
        }
    }

    function driver_set($keyword, $value = "", $time = 300, $option = array()) {
        $this->connectServer();
        if (isset($option['isExisting']) && $option['isExisting'] == true) {
            return $this->instant->add($keyword, $value, time() + $time);
        } else {
            return $this->instant->set($keyword, $value, time() + $time);
        }
    }

    function driver_get($keyword, $option = array()) {
        // return null if no caching
        // return value if in caching
        $this->connectServer();
        $x = $this->instant->get($keyword);
        if ($x == false) {
            return null;
        } else {
            return $x;
        }
    }

    function driver_delete($keyword, $option = array()) {
        $this->connectServer();
        $this->instant->delete($keyword);
    }

    function driver_stats($option = array()) {
        $this->connectServer();
        $res = array(
            "info" => "",
            "size" => "",
            "data" => $this->instant->getStats(),
        );

        return $res;
    }

    function driver_clean($option = array()) {
        $this->connectServer();
        $this->instant->flush();
    }

    function driver_isExisting($keyword) {
        $this->connectServer();
        $x = $this->get($keyword);
        if ($x == null) {
            return false;
        } else {
            return true;
        }
    }

}


/*
 * khoaofgod@yahoo.com
 * Website: http://www.phpfastcache.com
 * Example at our website, any bugs, problems, please visit http://www.codehelper.io
 */



// short function
if (!function_exists("__c")) {

    function __c($storage = "", $option = array()) {
        return phpfastcache($storage, $option);
    }

}

// main function
if (!function_exists("phpFastCache")) {

    function phpFastCache($storage = "", $option = array()) {
        if (!isset(phpFastCache_instances::$instances[$storage])) {
            phpFastCache_instances::$instances[$storage] = new phpFastCache($storage, $option);
        }
        return phpFastCache_instances::$instances[$storage];
    }

}

class phpFastCache_instances {

    public static $instances = array();

}

// main class
class phpFastCache {

    public static $storage = "auto";
    public static $config = array(
        "storage" => "auto",
        "fallback" => array(
            "example" => "files",
        ),
        "securityKey" => "auto",
        "htaccess" => true,
        "path" => "",
        "server" => array(
            array("127.0.0.1", 11211, 1),
        //  array("new.host.ip",11211,1),
        ),
        "extensions" => array(),
    );
    var $tmp = array();
    var $checked = array(
        "path" => false,
        "fallback" => false,
        "hook" => false,
    );
    var $is_driver = false;
    var $driver = NULL;
    // default options, this will be merge to Driver's Options
    var $option = array(
        "path" => "", // path for cache folder
        "htaccess" => null, // auto create htaccess
        "securityKey" => null, // Key Folder, Setup Per Domain will good.
        "system" => array(),
        "storage" => "",
        "cachePath" => "",
    );

    /*
     * Basic Method
     */

    function set($keyword, $value = "", $time = 300, $option = array()) {
        $object = array(
            "value" => $value,
            "write_time" => @date("U"),
            "expired_in" => $time,
            "expired_time" => @date("U") + (Int) $time,
        );
        if ($this->is_driver == true) {
            return $this->driver_set($keyword, $object, $time, $option);
        } else {
            return $this->driver->driver_set($keyword, $object, $time, $option);
        }
    }

    function get($keyword, $option = array()) {
        if ($this->is_driver == true) {
            $object = $this->driver_get($keyword, $option);
        } else {
            $object = $this->driver->driver_get($keyword, $option);
        }

        if ($object == null) {
            return null;
        }
        return $object['value'];
    }

    function getInfo($keyword, $option = array()) {
        if ($this->is_driver == true) {
            $object = $this->driver_get($keyword, $option);
        } else {
            $object = $this->driver->driver_get($keyword, $option);
        }

        if ($object == null) {
            return null;
        }
        return $object;
    }

    function delete($keyword, $option = array()) {
        if ($this->is_driver == true) {
            return $this->driver_delete($keyword, $option);
        } else {
            return $this->driver->driver_delete($keyword, $option);
        }
    }

    function stats($option = array()) {
        if ($this->is_driver == true) {
            return $this->driver_stats($option);
        } else {
            return $this->driver->driver_stats($option);
        }
    }

    function clean($option = array()) {
        if ($this->is_driver == true) {
            return $this->driver_clean($option);
        } else {
            return $this->driver->driver_clean($option);
        }
    }

    function isExisting($keyword) {
        if ($this->is_driver == true) {
            if (method_exists($this, "driver_isExisting")) {
                return $this->driver_isExisting($keyword);
            }
        } else {
            if (method_exists($this->driver, "driver_isExisting")) {
                return $this->driver->driver_isExisting($keyword);
            }
        }

        $data = $this->get($keyword);
        if ($data == null) {
            return false;
        } else {
            return true;
        }
    }

    function increment($keyword, $step = 1, $option = array()) {
        $object = $this->get($keyword);
        if ($object == null) {
            return false;
        } else {
            $value = (Int) $object['value'] + (Int) $step;
            $time = $object['expired_time'] - @date("U");
            $this->set($keyword, $value, $time, $option);
            return true;
        }
    }

    function decrement($keyword, $step = 1, $option = array()) {
        $object = $this->get($keyword);
        if ($object == null) {
            return false;
        } else {
            $value = (Int) $object['value'] - (Int) $step;
            $time = $object['expired_time'] - @date("U");
            $this->set($keyword, $value, $time, $option);
            return true;
        }
    }

    /*
     * Extend more time
     */

    function touch($keyword, $time = 300, $option = array()) {
        $object = $this->get($keyword);
        if ($object == null) {
            return false;
        } else {
            $value = $object['value'];
            $time = $object['expired_time'] - @date("U") + $time;
            $this->set($keyword, $value, $time, $option);
            return true;
        }
    }

    /*
     * Other Functions Built-int for phpFastCache since 1.3
     */

    public function setMulti($list = array()) {
        foreach ($list as $array) {
            $this->set($array[0], isset($array[1]) ? $array[1] : 300, isset($array[2]) ? $array[2] : array());
        }
    }

    public function getMulti($list = array()) {
        $res = array();
        foreach ($list as $array) {
            $name = $array[0];
            $res[$name] = $this->get($name, isset($array[1]) ? $array[1] : array());
        }
        return $res;
    }

    public function getInfoMulti($list = array()) {
        $res = array();
        foreach ($list as $array) {
            $name = $array[0];
            $res[$name] = $this->getInfo($name, isset($array[1]) ? $array[1] : array());
        }
        return $res;
    }

    public function deleteMulti($list = array()) {
        foreach ($list as $array) {
            $this->delete($array[0], isset($array[1]) ? $array[1] : array());
        }
    }

    public function isExistingMulti($list = array()) {
        $res = array();
        foreach ($list as $array) {
            $name = $array[0];
            $res[$name] = $this->isExisting($name);
        }
        return $res;
    }

    public function incrementMulti($list = array()) {
        $res = array();
        foreach ($list as $array) {
            $name = $array[0];
            $res[$name] = $this->increment($name, $array[1], isset($array[2]) ? $array[2] : array());
        }
        return $res;
    }

    public function decrementMulti($list = array()) {
        $res = array();
        foreach ($list as $array) {
            $name = $array[0];
            $res[$name] = $this->decrement($name, $array[1], isset($array[2]) ? $array[2] : array());
        }
        return $res;
    }

    public function touchMulti($list = array()) {
        $res = array();
        foreach ($list as $array) {
            $name = $array[0];
            $res[$name] = $this->touch($name, $array[1], isset($array[2]) ? $array[2] : array());
        }
        return $res;
    }

    /*
     * Begin Parent Classes;
     */

    public static function setup($name, $value = "") {
        if (!is_array($name)) {
            if ($name == "storage") {
                self::$storage = $value;
            }

            self::$config[$name] = $value;
        } else {
            foreach ($name as $n => $value) {
                self::setup($n, $value);
            }
        }
    }

    function __construct($storage = "", $option = array()) {
        if (isset(self::$config['fallback'][$storage])) {
            $storage = self::$config['fallback'][$storage];
        }

        if ($storage == "") {
            $storage = self::$storage;
            self::option("storage", $storage);
        } else {
            self::$storage = $storage;
        }

        $this->tmp['storage'] = $storage;

        $this->option = array_merge($this->option, self::$config, $option);

        if ($storage != "auto" && $storage != "" && $this->isExistingDriver($storage)) {
            $driver = "phpfastcache_" . $storage;
        } else {
            $storage = $this->autoDriver();
            self::$storage = $storage;
            $driver = "phpfastcache_" . $storage;
        }

        $this->option("storage", $storage);

        if ($this->option['securityKey'] == "auto" || $this->option['securityKey'] == "") {
            $this->option['securityKey'] = "cache.storage." . $_SERVER['HTTP_HOST'];
        }


        $this->driver = new $driver($this->option);
        $this->driver->is_driver = true;
    }

    /*
     * For Auto Driver
     *
     */

    function autoDriver() {

        $driver = "files";

        if (extension_loaded('apc') && ini_get('apc.enabled') && strpos(PHP_SAPI, "CGI") === false) {
            $driver = "apc";
        } elseif (extension_loaded('pdo_sqlite') && is_writeable($this->getPath())) {
            $driver = "sqlite";
        } elseif (is_writeable($this->getPath())) {
            $driver = "files";
        } else if (class_exists("memcached")) {
            $driver = "memcached";
        } elseif (extension_loaded('wincache') && function_exists("wincache_ucache_set")) {
            $driver = "wincache";
        } elseif (extension_loaded('xcache') && function_exists("xcache_get")) {
            $driver = "xcache";
        } else if (function_exists("memcache_connect")) {
            $driver = "memcache";
        } else {
            global $system;
            foreach ($system['cache_drivers'] as $filepath) {
                include $filepath;
                $file = pathinfo($filepath, PATHINFO_BASENAME);
                $namex = str_replace(".php", "", $file);
                $class = "phpfastcache_" . $namex;
                $option = $this->option;
                $option['skipError'] = true;
                $driver = new $class($option);
                $driver->option = $option;
                if ($driver->checkdriver()) {
                    $driver = $namex;
                }
            }
        }


        return $driver;
    }

    function option($name, $value = null) {
        if ($value == null) {
            if (isset($this->option[$name])) {
                return $this->option[$name];
            } else {
                return null;
            }
        } else {

            if ($name == "path") {
                $this->checked['path'] = false;
                $this->driver->checked['path'] = false;
            }

            self::$config[$name] = $value;
            $this->option[$name] = $value;
            $this->driver->option[$name] = $this->option[$name];

            return $this;
        }
    }

    public function setOption($option = array()) {
        $this->option = array_merge($this->option, self::$config, $option);
        $this->checked['path'] = false;
    }

    function __get($name) {
        $this->driver->option = $this->option;
        return $this->driver->get($name);
    }

    function __set($name, $v) {
        $this->driver->option = $this->option;
        if (isset($v[1]) && is_numeric($v[1])) {
            return $this->driver->set($name, $v[0], $v[1], isset($v[2]) ? $v[2] : array() );
        } else {
            throw new Exception("Example ->$name = array('VALUE', 300);", 98);
        }
    }

    /*
     * Only require_once for the class u use.
     * Not use autoload default of PHP and don't need to load all classes as default
     */

    private function isExistingDriver($class) {
        return true;
    }

    /*
     * return System Information
     */

    public function systemInfo() {
        if (count($this->option("system")) == 0) {


            $this->option['system']['driver'] = "files";

            $this->option['system']['drivers'] = array();

            global $system;

            foreach (array('apc', 'sqlite', 'files', 'memcached', 'wincache', 'xcache', 'memcache') as $namex) {
                $class = "phpfastcache_" . $namex;
                $this->option['skipError'] = true;
                $driver = new $class($this->option);
                $driver->option = $this->option;
                if ($driver->checkdriver()) {
                    $this->option['system']['drivers'][$namex] = true;
                    $this->option['system']['driver'] = $namex;
                } else {
                    $this->option['system']['drivers'][$namex] = false;
                }
            }

            foreach ($system['cache_drivers'] as $filepath) {
                include $filepath;
                $file = pathinfo($filepath, PATHINFO_BASENAME);
                $namex = str_replace(".php", "", $file);
                $class = "phpfastcache_" . $namex;
                $this->option['skipError'] = true;
                $driver = new $class($this->option);
                $driver->option = $this->option;
                if ($driver->checkdriver()) {
                    $this->option['system']['drivers'][$namex] = true;
                    $this->option['system']['driver'] = $namex;
                } else {
                    $this->option['system']['drivers'][$namex] = false;
                }
            }



            /*
             * PDO is highest priority with SQLite
             */
            if ($this->option['system']['drivers']['sqlite'] == true) {
                $this->option['system']['driver'] = "sqlite";
            }
        }

        $example = new phpfastcache_example($this->option);
        $this->option("path", $example->getPath(true));
        return $this->option;
    }

    public function getOS() {
        $os = array(
            "os" => PHP_OS,
            "php" => PHP_SAPI,
            "system" => php_uname(),
            "unique" => md5(php_uname() . PHP_OS . PHP_SAPI)
        );
        return $os;
    }

    /*
     * Object for Files & SQLite
     */

    public function encode($data) {
        return serialize($data);
    }

    public function decode($value) {
        $x = @unserialize($value);
        if ($x == false) {
            return $value;
        } else {
            return $x;
        }
    }

    /*
     * Auto Create .htaccess to protect cache folder
     */

    public function htaccessGen($path = "") {
        if ($this->option("htaccess") == true) {

            if (!file_exists($path . "/.htaccess")) {
                //   echo "write me";
                $html = "order deny, allow \r\n
deny from all \r\n
allow from 127.0.0.1";

                $f = @fopen($path . "/.htaccess", "w+");
                if (!$f) {
                    throw new Exception("Can't create .htaccess", 97);
                }
                fwrite($f, $html);
                fclose($f);
            } else {
                //   echo "got me";
            }
        }
    }

    /*
     * Check phpModules or CGI
     */

    public function isPHPModule() {
        if (PHP_SAPI == "apache2handler") {
            return true;
        } else {
            if (strpos(PHP_SAPI, "handler") !== false) {
                return true;
            }
        }
        return false;
    }

    /*
     * return PATH for Files & PDO only
     */

    public function getPath($create_path = false) {

        if ($this->option['path'] == "" && self::$config['path'] != "") {
            $this->option("path", self::$config['path']);
        }


        if ($this->option['path'] == '') {
            // revision 618
            if ($this->isPHPModule()) {
                $tmp_dir = ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir();
                $this->option("path", $tmp_dir);
            } else {
                $this->option("path", dirname(__FILE__));
            }

            if (self::$config['path'] == "") {
                self::$config['path'] = $this->option("path");
            }
        }


        $full_path = $this->option("path") . "/" . $this->option("securityKey") . "/";

        if ($create_path == false && $this->checked['path'] == false) {

            if (!file_exists($full_path) || !is_writable($full_path)) {
                if (!file_exists($full_path)) {
                    @mkdir($full_path, 0777);
                }
                if (!is_writable($full_path)) {
                    @chmod($full_path, 0777);
                }
                if (!file_exists($full_path) || !is_writable($full_path)) {
                    throw new Exception("Sorry, Please create " . $this->option("path") . "/" . $this->option("securityKey") . "/ and SET Mode 0777 or any Writable Permission!", 100);
                }
            }


            $this->checked['path'] = true;
            $this->htaccessGen($full_path);
        }

        $this->option['cachePath'] = $full_path;
        return $this->option['cachePath'];
    }

    /*
     * Read File
     * Use file_get_contents OR ALT read
     */

    function readfile($file) {
        if (function_exists("file_get_contents")) {
            return file_get_contents($file);
        } else {
            $string = "";

            $file_handle = @fopen($file, "r");
            if (!$file_handle) {
                throw new Exception("Can't Read File", 96);
            }
            while (!feof($file_handle)) {
                $line = fgets($file_handle);
                $string .= $line;
            }
            fclose($file_handle);

            return $string;
        }
    }

}

/* End of file WoniuInput.php */