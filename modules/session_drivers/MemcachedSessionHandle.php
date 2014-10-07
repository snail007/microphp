<?php
class MemcachedSessionHandle {
    public function start($config = array()) {
        ini_set('session.save_handler', 'memcached');
        ini_set('session.save_path', $config['memcached']);
    }
}
