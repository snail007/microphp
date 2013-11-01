<?php

/*
 * This MongoDB session handler is intended to store any data you see fit.
 * One interesting optimization to note is the setting of the active flag
 * to 0 when a session has expired. The intended purpose of this garbage
 * collection is to allow you to create a batch process for removal of
 * all expired sessions. This should most likely be implemented as a cronjob
 * script.
 *
 * @author		Corey Ballou
 * @copyright	Corey Ballou (2010)
 * @property MongoCollection __mongo_collection
 */

class MemcacheSessionHandle implements WoniuSessionHandle {

    /**
     * Default constructor.
     *
     * @access  public
     * @param   array   $config
     */
    public function start($config = array()) {
        $config = array_merge($config['common'], $config['memcache']);
        $session_save_path = "tcp://{$config['host']}:{$config['port']}";
        ini_set('session.save_handler', 'memcache');
        ini_set('session.save_path', $session_save_path);
        // start it up
        if ($config['autostart'] && !isset($_SESSION)) {
            session_start();
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
