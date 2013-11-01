<?php

/**
 *
 * @author pm
 */
interface WoniuSessionHandle {
    
    public function start($config=array());

    /**
     * Open the session
     * @return bool
     */
    public function open($save_path, $session_name);

    /**
     * Close the session
     * @return bool
     */
    public function close();

    /**
     * Read the session
     * @param int session id
     * @return string string of the sessoin
     */
    public function read($id);

    /**
     * Write the session
     * @param int session id
     * @param string data of the session
     */
    public function write($id, $data);

    /**
     * Destoroy the session
     * @param int session id
     * @return bool
     */
    public function destroy($id);

    /**
     * Garbage Collector
     * @param int life time (sec.)
     * @return bool
     * @see session.gc_divisor      100
     * @see session.gc_maxlifetime 1440
     * @see session.gc_probability    1
     * @usage execution rate 1/100
     *        (session.gc_probability/session.gc_divisor)
     */
    public function gc($max=0);
}
