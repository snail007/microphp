<?php
class MongodbSessionHandle implements WoniuSessionHandle {
    // default config with support for multiple servers
    // (helpful for sharding and replication setups)
    protected $_config;
    private $__mongo_collection = NULL;
    private $__current_session = NULL;
    private $__mongo_conn = NULL;
    public function connect() {
        $connection_string = sprintf('mongodb://%s:%s', $this->_config['host'], $this->_config['port']);
        if ($this->_config['user'] != null && $this->_config['password'] != null) {
            $connection_string = sprintf('mongodb://%s:%s@%s:%s/%s', $this->_config['user'], $this->_config['password'], $this->_config['host'], $this->_config['port'], $this->_config['database']);
        }
        // add immediate connection
        $opts = array('connect' => true);
        // support persistent connections
        if ($this->_config['persistent'] && !empty($this->_config['persistentId'])) {
            $opts['persist'] = $this->_config['persistentId'];
        }
        // support replica sets
        if ($this->_config['replicaSet']) {
            $opts['replicaSet'] = $this->_config['replicaSet'];
        }
        $class = 'MongoClient';
        if (!class_exists($class)) {
            $class = 'Mongo';
        }
        $this->__mongo_conn=$object_conn = new $class($connection_string, $opts);
        $object_mongo = $object_conn->{$this->_config['database']};
        $this->__mongo_collection = $object_mongo->{$this->_config['collection']};
    }
    /**
     * Default constructor.
     *
     * @access  public
     * @param   array   $config
     */
    public function start($config = array()) {
        // initialize the database
        $config = array_merge($config['common'], $config['mongodb']);
        $this->_config = $config;
        // set object as the save handler
        session_set_save_handler(array(&$this, 'open'), array(&$this, 'close'), array(&$this, 'read'), array(&$this, 'write'), array(&$this, 'destroy'), array(&$this, 'gc'));
    }
    /**
     * 
     * check for collection object
     * @access public
     * @param string $session_path
     * @param string $session_name
     * @return boolean
     */
    public function open($session_path, $session_name) {
        if (!is_object($this->__mongo_collection)) {
            $this->connect();
        }
        $result = true;
        if ($this->__mongo_collection == NULL) {
            $result = false;
        }
        //echo 'open called'."\n";
        return $result;
    }
    /**
     * 
     * doing noting
     * @access public
     * @return boolean
     */
    public function close() {
        $this->__mongo_conn->close();
        return true;
    }
    /**
     * 
     * Reading session data based on id
     * @access public
     * @param string $session_id
     * @return mixed 
     */
    public function read($session_id) {
        //print "Read <br />";
        $result = NULL;
        $ret = '';
        $expiry = time();
        $query['_id'] = $session_id;
        $query['expiry'] = array('$gte' => $expiry);
//        print_r($query);
        $result = $this->__mongo_collection->findone($query);
        if ($result) {
            $this->__current_session = $result;
            $result['expiry'] = time() + $this->_config['lifetime'];
            $this->__mongo_collection->update(array("_id" => $session_id), $result);
            $ret = $result['data'];
        }
        //echo 'read called'."\n";
        return $ret;
    }
    /**
     * 
     * Writing session data
     * @access public
     * @param string $session_id
     * @param mixed $data
     * @return boolean
     */
    public function write($session_id, $data) {
        $result = true;
        $expiry = time() + $this->_config['lifetime'];
        $session_data = array();
        if (empty($this->__current_session)) {
            $session_id = $session_id;
            $session_data['_id'] = $session_id;
            $session_data['data'] = $data;
            $session_data['expiry'] = $expiry;
        } else {
            $session_data = (array) $this->__current_session;
            $session_data['data'] = $data;
            $session_data['expiry'] = $expiry;
        }
        $query['_id'] = $session_id;
        $record = $this->__mongo_collection->findOne($query);
        if ($record == null) {
            $this->__mongo_collection->insert($session_data);
            //var_dump('insert');
        } else {
            $record['data'] = $data;
            $record['expiry'] = $expiry;
            $this->__mongo_collection->save($record);
            //var_dump('save');
        }
        //echo 'write called'."\n";
        return true;
    }
    /**
     * 
     * remove session data
     * @access public
     * @param string $session_id
     * @return boolean
     */
    public function destroy($session_id) {
        unset($_SESSION);
        $query['_id'] = $session_id;
        $this->__mongo_collection->remove($query);
        //echo 'destory called'."\n";
        return true;
    }
    /**
     * 
     * Garbage collection
     * @access public
     * @return boolean
     */
    public function gc($max = 0) {
        $query = array();
        $query['expiry'] = array(':lt' => time());
        $this->__mongo_collection->remove($query, array('justOne' => false));
        return true;
    }
}
