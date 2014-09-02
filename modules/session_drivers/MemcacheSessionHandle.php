<?php
class MemcacheSessionHandle implements WoniuSessionHandle {
    /**
     * Default constructor.
     *
     * @access  public
     * @param   array   $config
     */
    public function start($config = array()) {
        $session_save_path = $config['memcache'];
        ini_set('session.save_handler', 'memcache');
        ini_set('session.save_path', $session_save_path);
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
