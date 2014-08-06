<?php
class RedisSessionHandle implements WoniuSessionHandle {
    /**
     * Default constructor.
     *
     * @access  public
     * @param   array   $config
     */
    public function start($config = array()) {
        $session_save_path = $config['redis'];
        ini_set('session.save_handler', 'redis');
        ini_set('session.save_path', $session_save_path);
        
        // set some important session vars
        ini_set('session.auto_start', 0);
        ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', 100);
        ini_set('session.gc_maxlifetime', $config['common']['lifetime']);
        ini_set('session.referer_check', '');
        ini_set('session.entropy_file', '/dev/urandom');
        ini_set('session.entropy_length', 16);
        ini_set('session.use_cookies', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.use_trans_sid', 0);
        ini_set('session.hash_function', 1);
        ini_set('session.hash_bits_per_character', 5);
        // disable client/proxy caching
        session_cache_limiter('nocache');
        // set the cookie parameters
        session_set_cookie_params(
                $config['common']['lifetime'], $config['common']['cookie_path'], $config['common']['cookie_domain'], ($_SERVER['SERVER_PORT'] == 443), TRUE
        );
        // name the session
        session_name($config['common']['session_name']);
        register_shutdown_function('session_write_close');
        
        
        // start it up
        if ($config['common']['autostart'] && !isset($_SESSION)) {
            if (!isset($_SESSION)) {
                session_start();
            }
        }
    }
    public function open($session_path, $session_name) {
        
    }
    public function close() {
        
    }
    public function read($session_id) {
        
    }
    public function write($session_id, $data) {
        
    }
    public function destroy($session_id) {
        
    }
    public function gc($max = 0) {
        
    }
}
