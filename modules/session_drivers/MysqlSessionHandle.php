<?php
class MysqlSessionHandle implements WoniuSessionHandle {
    private $_config;
    /**
     * a database MySQLi connection resource
     * @var resource
     */
    protected $dbConnection;
    /**
     * the name of the DB table which handles the sessions
     * @var string
     */
    protected $dbTable;
    public function connect() {
        $config = $this->_config;
        $dbHost = $config['host'];
        $dbPort = $config['port'];
        $dbUser = $config['user'];
        $dbPassword = $config['password'];
        $dbDatabase = $config['database'];
        $dbTable = $config['table'];
        //create db connection
        $this->dbConnection = new mysqli($dbHost, $dbUser, $dbPassword, $dbDatabase, $dbPort);
        $this->dbTable = $dbTable;
        //check connection
        if (mysqli_connect_error()) {
            throw new Exception('Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
        }//if
    }
    /**
     * Set db data if no connection is being injected
     * @param 	string	$dbHost	
     * @param	string	$dbUser
     * @param	string	$dbPassword
     * @param	string	$dbDatabase
     */
    public function start($config = array()) {
        $this->_config = $config = array_merge($config['common'], $config['mysql']);
        session_set_save_handler(array($this, 'open'), array($this, 'close'), array($this, 'read'), array($this, 'write'), array($this, 'destroy'), array($this, 'gc'));
    }
    /**
     * Open the session
     * @return bool
     */
    public function open($save_path, $session_name) {
        if (!is_object($this->dbConnection)) {
            $this->connect();
        }
        return TRUE;
    }
    /**
     * Close the session
     * @return bool
     */
    public function close() {
        return $this->dbConnection->close();
    }
    /**
     * Read the session
     * @param int session id
     * @return string string of the sessoin
     */
    public function read($id) {
        $sql = sprintf("SELECT data FROM %s WHERE id = '%s'", $this->dbTable, $this->dbConnection->escape_string($id));
        if ($result = $this->dbConnection->query($sql)) {
            if ($result->num_rows && $result->num_rows > 0) {
                $record = $result->fetch_assoc();
                 $sql = sprintf("update  %s set `timestamp` =%s where id='%s' ", $this->dbTable, time() + intval($this->_config['lifetime']), $this->dbConnection->escape_string($id));
                 $this->dbConnection->query($sql);
                return $record['data'];
            } else {
                return false;
            }
        } else {
            return false;
        }
        return true;
    }
    /**
     * Write the session
     * @param int session id
     * @param string data of the session
     */
    public function write($id, $data) {
        $sql = sprintf("REPLACE INTO %s VALUES('%s', '%s', %s)", $this->dbTable, $this->dbConnection->escape_string($id), $this->dbConnection->escape_string($data), time() + intval($this->_config['lifetime']));
        return $this->dbConnection->query($sql);
    }
    /**
     * Destoroy the session
     * @param int session id
     * @return bool
     */
    public function destroy($id) {
        unset($_SESSION);
        $sql = sprintf("DELETE FROM %s WHERE `id` = '%s'", $this->dbTable, $this->dbConnection->escape_string($id));
        return $this->dbConnection->query($sql);
    }
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
    public function gc($max = 0) {
        $sql = sprintf("DELETE FROM %s WHERE `timestamp` < %s ", $this->dbTable, time());
        return $this->dbConnection->query($sql);
    }
}