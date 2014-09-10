<?php
class RedisSessionHandle {
    public function start($config = array()) {
        ini_set('session.save_handler', 'redis');
        ini_set('session.save_path', $config['redis']);
    }
}
