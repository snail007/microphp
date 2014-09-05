<?php
class MemcacheSessionHandle {
    public function start($config = array()) {
        ini_set('session.save_handler', 'memcache');
        ini_set('session.save_path', $config['memcache']);
    }
}
