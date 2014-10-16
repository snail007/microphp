<?php
/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.2.0 or newer
 *
 * @package                MicroPHP
 * @author                狂奔的蜗牛
 * @email                672308444@163.com
 * @copyright          Copyright (c) 2013 - 2013, 狂奔的蜗牛, Inc.
 * @link                http://git.oschina.net/snail/microphp
 * @since                Version 1.0
 * @createdtime       {createdtime}
 */
class WoniuDB {
    private static $conns = array();
    public static function &getInstance($config, $force_new_conn = false) {
        $default['dbdriver'] = "mysql";
        $default['hostname'] = '127.0.0.1';
        $default['port'] = '3306';
        $default['username'] = 'root';
        $default['password'] = '';
        $default['database'] = 'test';
        $default['dbprefix'] = '';
        $default['pconnect'] = TRUE;
        $default['db_debug'] = TRUE;
        $default['char_set'] = 'utf8';
        $default['dbcollat'] = 'utf8_general_ci';
        $default['swap_pre'] = '';
        $default['autoinit'] = TRUE;
        $default['stricton'] = FALSE;
        $config=  array_merge($default,$config);
        $class = 'CI_DB_' . $config['dbdriver'] . '_driver';
        if(!class_exists($class, false)){
            return null;
        }
        $config0=$config;
        asort($config0);
        $hash = md5(sha1(var_export($config0, TRUE)));
        if ($force_new_conn || !isset(self::$conns[$hash])) {
            self::$conns[$hash] = new $class($config);
        }
        if ($config['dbdriver'] == 'pdo' && strpos($config['hostname'], 'mysql') !== FALSE) {
            //pdo下面dns设置mysql字符会失效，这里hack一下
            self::$conns[$hash]->simple_query('set names ' . $config['char_set']);
        }
        return self::$conns[$hash];
    }
}
/**
 * CI_DB_mysql_driver -> CI_DB -> CI_DB_active_record -> CI_DB_driver
 * CI_DB_mysql_result -> Woniu_DB_result -> CI_DB_result
 */
class CI_DB extends CI_DB_active_record {
    
}
/**
 * Database Driver Class
 *
 * This is the platform-independent base DB implementation class.
 * This class will not be called directly. Rather, the adapter
 * class for the specific database will extend and instantiate it.
 *
 * @package                CodeIgniter
 * @subpackage        Drivers
 * @category        Database
 * @author                ExpressionEngine Dev Team
 * @link                http://codeigniter.com/user_guide/database/
 */
class CI_DB_driver {
    var $username;
    var $password;
    var $hostname;
    var $database;
    var $dbdriver = 'mysql';
    var $dbprefix = '';
    var $char_set = 'utf8';
    var $dbcollat = 'utf8_general_ci';
    var $autoinit = TRUE; // Whether to automatically initialize the DB
    var $swap_pre = '';
    var $port = '';
    var $pconnect = FALSE;
    var $conn_id = FALSE;
    var $result_id = FALSE;
    var $db_debug = FALSE;
    var $benchmark = 0;
    var $query_count = 0;
    var $bind_marker = '?';
    var $save_queries = TRUE;
    var $queries = array();
    var $query_times = array();
    var $data_cache = array();
    var $trans_enabled = TRUE;
    var $trans_strict = TRUE;
    var $_trans_depth = 0;
    var $_trans_status = TRUE; // Used with transactions to determine if a rollback should occur
// Private variables
    var $_protect_identifiers = TRUE;
    var $_reserved_identifiers = array('*'); // Identifiers that should NOT be escaped
// These are use with Oracle
    var $stmt_id;
    var $curs_id;
    var $limit_used;
    /**
     * Constructor.  Accepts one parameter containing the database
     * connection settings.
     *
     * @param array
     */
    function __construct($params) {
        if (is_array($params)) {
            foreach ($params as $key => $val) {
                $this->$key = $val;
            }
        }
        log_message('debug', 'Database Driver Class Initialized');
    }
    /**
     * Initialize Database Settings
     *
     * @access        private Called by the constructor
     * @param        mixed
     * @return        void
     */
    function initialize() {
// If an existing connection resource is available
// there is no need to connect and select the database
        if (is_resource($this->conn_id) OR is_object($this->conn_id)) {
            return TRUE;
        }
// ----------------------------------------------------------------
// Connect to the database and set the connection ID
        $this->conn_id = ($this->pconnect == FALSE) ? $this->db_connect() : $this->db_pconnect();
// No connection resource?  Throw an error
        if (!$this->conn_id) {
            log_message('error', 'Unable to connect to the database');
            if ($this->db_debug || systemInfo('error_manage')) {
                $this->display_error('db_unable_to_connect');
            }
            return FALSE;
        }
// ----------------------------------------------------------------
// Select the DB... assuming a database name is specified in the config file
        if ($this->database != '') {
            if (!$this->db_select()) {
                log_message('error', 'Unable to select database: ' . $this->database);
                if ($this->db_debug || systemInfo('error_manage')) {
                    $this->display_error('db_unable_to_select', $this->database);
                }
                return FALSE;
            } else {
// We've selected the DB. Now we set the character set
                if (!$this->db_set_charset($this->char_set, $this->dbcollat)) {
                    return FALSE;
                }
                return TRUE;
            }
        }
        return TRUE;
    }
    /**
     * Set client character set
     *
     * @access        public
     * @param        string
     * @param        string
     * @return        resource
     */
    function db_set_charset($charset, $collation) {
        if (!$this->_db_set_charset($this->char_set, $this->dbcollat)) {
            log_message('error', 'Unable to set database connection charset: ' . $this->char_set);
            if ($this->db_debug || systemInfo('error_manage')) {
                $this->display_error('db_unable_to_set_charset', $this->char_set);
            }
            return FALSE;
        }
        return TRUE;
    }
    /**
     * The name of the platform in use (mysql, mssql, etc...)
     *
     * @access        public
     * @return        string
     */
    function platform() {
        return $this->dbdriver;
    }
    /**
     * Database Version Number.  Returns a string containing the
     * version of the database being used
     *
     * @access        public
     * @return        string
     */
    function version() {
        if (FALSE === ($sql = $this->_version())) {
            if ($this->db_debug || systemInfo('error_manage')) {
                return $this->display_error('db_unsupported_function');
            }
            return FALSE;
        }
// Some DBs have functions that return the version, and don't run special
// SQL queries per se. In these instances, just return the result.
        $driver_version_exceptions = array('oci8', 'sqlite', 'cubrid');
        if (in_array($this->dbdriver, $driver_version_exceptions)) {
            return $sql;
        } else {
            $query = $this->query($sql);
            return $query->row('ver');
        }
    }
    /**
     * Execute the query
     *
     * Accepts an SQL string as input and returns a result object upon
     * successful execution of a "read" type query.  Returns boolean TRUE
     * upon successful execution of a "write" type query. Returns boolean
     * FALSE upon failure, and if the $db_debug variable is set to TRUE
     * will raise an error.
     *
     * @access        public
     * @param        string        An SQL query string
     * @param        array        An array of binding data
     * @return        mixed
     */
    function query($sql, $binds = FALSE, $return_object = TRUE) {
        if ($sql == '') {
            if ($this->db_debug || systemInfo('error_manage')) {
                log_message('error', 'Invalid query: ' . $sql);
                return $this->display_error('db_invalid_query');
            }
            return FALSE;
        }
// Verify table prefix and replace if necessary
        if (($this->dbprefix != '' AND $this->swap_pre != '') AND ( $this->dbprefix != $this->swap_pre)) {
            $sql = preg_replace("/(\W)" . $this->swap_pre . "(\S+?)/", "\\1" . $this->dbprefix . "\\2", $sql);
        }
// Compile binds if needed
        if ($binds !== FALSE) {
            $sql = $this->compile_binds($sql, $binds);
        }
// Is query caching enabled?  If the query is a "read type"
// we will load the caching class and return the previously
// Save the  query for debugging
        if ($this->save_queries == TRUE) {
            $this->queries[] = $sql;
        }
// Start the Query Timer
        $time_start = list($sm, $ss) = explode(' ', microtime());
// Run the Query
        if (FALSE === ($this->result_id = $this->simple_query($sql))) {
            if ($this->save_queries == TRUE) {
                $this->query_times[] = 0;
            }
// This will trigger a rollback if transactions are being used
            $this->_trans_status = FALSE;
            if ($this->db_debug || systemInfo('error_manage')) {
// grab the error number and message now, as we might run some
// additional queries before displaying the error
                $error_no = $this->_error_number();
                $error_msg = $this->_error_message();
// We call this function in order to roll-back queries
// if transactions are enabled.  If we don't call this here
// the error message will trigger an exit, causing the
// transactions to remain in limbo.
                $this->trans_complete();
// Log and display errors
                log_message('error', 'Query error: ' . $error_msg);
                return $this->display_error(
                                array(
                                    'Error Number: ' . $error_no,
                                    $error_msg,
                                    $sql
                                )
                );
            }
            return FALSE;
        }
// Stop and aggregate the query time results
        $time_end = list($em, $es) = explode(' ', microtime());
        $this->benchmark += ($em + $es) - ($sm + $ss);
        if ($this->save_queries == TRUE) {
            $this->query_times[] = ($em + $es) - ($sm + $ss);
        }
// Increment the query counter
        $this->query_count++;
// Was the query a "write" type?
// If so we'll simply return true
        if ($this->is_write_type($sql) === TRUE) {
// If caching is enabled we'll auto-cleanup any
// existing files related to this particular URI
            return TRUE;
        }
// Return TRUE if we don't need to create a result object
// Currently only the Oracle driver uses this when stored
// procedures are used
        if ($return_object !== TRUE) {
            return TRUE;
        }
// Load and instantiate the result driver
        $driver = $this->load_rdriver();
        $RES = new $driver();
        $RES->conn_id = $this->conn_id;
        $RES->result_id = $this->result_id;
        if ($this->dbdriver == 'oci8') {
            $RES->stmt_id = $this->stmt_id;
            $RES->curs_id = NULL;
            $RES->limit_used = $this->limit_used;
            $this->stmt_id = FALSE;
        }
// oci8 vars must be set before calling this
        $RES->num_rows = $RES->num_rows();
// Is query caching enabled?  If so, we'll serialize the
// result object and save it to a cache file.
        return $RES;
    }
    /**
     * Load the result drivers
     *
     * @access        public
     * @return        string        the name of the result class
     */
    function load_rdriver() {
        $driver = 'CI_DB_' . $this->dbdriver . '_result';
        if (!class_exists($driver, FALSE)) {
            include_once(BASEPATH . 'database/DB_result.php');
            include_once(BASEPATH . 'database/drivers/' . $this->dbdriver . '/' . $this->dbdriver . '_result.php');
        }
        return $driver;
    }
    /**
     * Simple Query
     * This is a simplified version of the query() function.  Internally
     * we only use it when running transaction commands since they do
     * not require all the features of the main query() function.
     *
     * @access        public
     * @param        string        the sql query
     * @return        mixed
     */
    function simple_query($sql) {
        if (!$this->conn_id) {
            $this->initialize();
        }
        return $this->_execute($sql);
    }
    /**
     * Disable Transactions
     * This permits transactions to be disabled at run-time.
     *
     * @access        public
     * @return        void
     */
    function trans_off() {
        $this->trans_enabled = FALSE;
    }
    /**
     * Enable/disable Transaction Strict Mode
     * When strict mode is enabled, if you are running multiple groups of
     * transactions, if one group fails all groups will be rolled back.
     * If strict mode is disabled, each group is treated autonomously, meaning
     * a failure of one group will not affect any others
     *
     * @access        public
     * @return        void
     */
    function trans_strict($mode = TRUE) {
        $this->trans_strict = is_bool($mode) ? $mode : TRUE;
    }
    /**
     * Start Transaction
     *
     * @access        public
     * @return        void
     */
    function trans_start($test_mode = FALSE) {
        if (!$this->trans_enabled) {
            return FALSE;
        }
// When transactions are nested we only begin/commit/rollback the outermost ones
        if ($this->_trans_depth > 0) {
            $this->_trans_depth += 1;
            return;
        }
        $this->trans_begin($test_mode);
    }
    /**
     * Complete Transaction
     *
     * @access        public
     * @return        bool
     */
    function trans_complete() {
        if (!$this->trans_enabled) {
            return FALSE;
        }
// When transactions are nested we only begin/commit/rollback the outermost ones
        if ($this->_trans_depth > 1) {
            $this->_trans_depth -= 1;
            return TRUE;
        }
// The query() function will set this flag to FALSE in the event that a query failed
        if ($this->_trans_status === FALSE) {
            $this->trans_rollback();
// If we are NOT running in strict mode, we will reset
// the _trans_status flag so that subsequent groups of transactions
// will be permitted.
            if ($this->trans_strict === FALSE) {
                $this->_trans_status = TRUE;
            }
            log_message('debug', 'DB Transaction Failure');
            return FALSE;
        }
        $this->trans_commit();
        return TRUE;
    }
    /**
     * Lets you retrieve the transaction flag to determine if it has failed
     *
     * @access        public
     * @return        bool
     */
    function trans_status() {
        return $this->_trans_status;
    }
    /**
     * Compile Bindings
     *
     * @access        public
     * @param        string        the sql statement
     * @param        array        an array of bind data
     * @return        string
     */
    function compile_binds($sql, $binds) {
        if (strpos($sql, $this->bind_marker) === FALSE) {
            return $sql;
        }
        if (!is_array($binds)) {
            $binds = array($binds);
        }
// Get the sql segments around the bind markers
        $segments = explode($this->bind_marker, $sql);
// The count of bind should be 1 less then the count of segments
// If there are more bind arguments trim it down
        if (count($binds) >= count($segments)) {
            $binds = array_slice($binds, 0, count($segments) - 1);
        }
// Construct the binded query
        $result = $segments[0];
        $i = 0;
        foreach ($binds as $bind) {
            $result .= $this->escape($bind);
            $result .= $segments[++$i];
        }
        return $result;
    }
    /**
     * Determines if a query is a "write" type.
     *
     * @access        public
     * @param        string        An SQL query string
     * @return        boolean
     */
    function is_write_type($sql) {
        if (!preg_match('/^\s*"?(SET|INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|TRUNCATE|LOAD DATA|COPY|ALTER|GRANT|REVOKE|LOCK|UNLOCK)\s+/i', $sql)) {
            return FALSE;
        }
        return TRUE;
    }
    /**
     * Calculate the aggregate query elapsed time
     *
     * @access        public
     * @param        integer        The number of decimal places
     * @return        integer
     */
    function elapsed_time($decimals = 6) {
        return number_format($this->benchmark, $decimals);
    }
    /**
     * Returns the total number of queries
     *
     * @access        public
     * @return        integer
     */
    function total_queries() {
        return $this->query_count;
    }
    /**
     * Returns the last query that was executed
     *
     * @access        public
     * @return        void
     */
    function last_query() {
        return end($this->queries);
    }
    /**
     * "Smart" Escape String
     *
     * Escapes data based on type
     * Sets boolean and null types
     *
     * @access        public
     * @param        string
     * @return        mixed
     */
    function escape($str) {
        if (is_string($str)) {
            $str = "'" . $this->escape_str($str) . "'";
        } elseif (is_bool($str)) {
            $str = ($str === FALSE) ? 0 : 1;
        } elseif (is_null($str)) {
            $str = 'NULL';
        }
        return $str;
    }
    /**
     * Escape LIKE String
     *
     * Calls the individual driver for platform
     * specific escaping for LIKE conditions
     *
     * @access        public
     * @param        string
     * @return        mixed
     */
    function escape_like_str($str) {
        return $this->escape_str($str, TRUE);
    }
    /**
     * Primary
     *
     * Retrieves the primary key.  It assumes that the row in the first
     * position is the primary key
     *
     * @access        public
     * @param        string        the table name
     * @return        string
     */
    function primary($table = '') {
        $fields = $this->list_fields($table);
        if (!is_array($fields)) {
            return FALSE;
        }
        return current($fields);
    }
    /**
     * Returns an array of table names
     *
     * @access        public
     * @return        array
     */
    function list_tables($constrain_by_prefix = FALSE) {
// Is there a cached result?
        if (isset($this->data_cache['table_names'])) {
            return $this->data_cache['table_names'];
        }
        if (FALSE === ($sql = $this->_list_tables($constrain_by_prefix))) {
            if ($this->db_debug || systemInfo('error_manage')) {
                return $this->display_error('db_unsupported_function');
            }
            return FALSE;
        }
        $retval = array();
        $query = $this->query($sql);
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                if (isset($row['TABLE_NAME'])) {
                    $retval[] = $row['TABLE_NAME'];
                } else {
                    $retval[] = array_shift($row);
                }
            }
        }
        $this->data_cache['table_names'] = $retval;
        return $this->data_cache['table_names'];
    }
    /**
     * Determine if a particular table exists
     * @access        public
     * @return        boolean
     */
    function table_exists($table_name) {
        return (!in_array($this->_protect_identifiers($table_name, TRUE, FALSE, FALSE), $this->list_tables())) ? FALSE : TRUE;
    }
    /**
     * Fetch MySQL Field Names
     *
     * @access        public
     * @param        string        the table name
     * @return        array
     */
    function list_fields($table = '') {
// Is there a cached result?
        if (isset($this->data_cache['field_names'][$table])) {
            return $this->data_cache['field_names'][$table];
        }
        if ($table == '') {
            if ($this->db_debug || systemInfo('error_manage')) {
                return $this->display_error('db_field_param_missing');
            }
            return FALSE;
        }
        if (FALSE === ($sql = $this->_list_columns($table))) {
            if ($this->db_debug || systemInfo('error_manage')) {
                return $this->display_error('db_unsupported_function');
            }
            return FALSE;
        }
        $query = $this->query($sql);
        $retval = array();
        foreach ($query->result_array() as $row) {
            if (isset($row['COLUMN_NAME'])) {
                $retval[] = $row['COLUMN_NAME'];
            } else if ($this->dbdriver == 'sqlite3') {
                $retval[] = $row['name'];
            } else {
                $retval[] = current($row);
            }
        }
        $this->data_cache['field_names'][$table] = $retval;
        return $this->data_cache['field_names'][$table];
    }
    /**
     * Determine if a particular field exists
     * @access        public
     * @param        string
     * @param        string
     * @return        boolean
     */
    function field_exists($field_name, $table_name) {
        return (!in_array($field_name, $this->list_fields($table_name))) ? FALSE : TRUE;
    }
    /**
     * Returns an object with field data
     *
     * @access        public
     * @param        string        the table name
     * @return        object
     */
    function field_data($table = '') {
        if ($table == '') {
            if ($this->db_debug || systemInfo('error_manage')) {
                return $this->display_error('db_field_param_missing');
            }
            return FALSE;
        }
        $query = $this->query($this->_field_data($this->_protect_identifiers($table, TRUE, NULL, FALSE)));
        return $query->field_data();
    }
    /**
     * Generate an insert string
     *
     * @access        public
     * @param        string        the table upon which the query will be performed
     * @param        array        an associative array data of key/values
     * @return        string
     */
    function insert_string($table, $data) {
        $fields = array();
        $values = array();
        foreach ($data as $key => $val) {
            $fields[] = $this->_escape_identifiers($key);
            $values[] = $this->escape($val);
        }
        return $this->_insert($this->_protect_identifiers($table, TRUE, NULL, FALSE), $fields, $values);
    }
    /**
     * Generate an update string
     *
     * @access        public
     * @param        string        the table upon which the query will be performed
     * @param        array        an associative array data of key/values
     * @param        mixed        the "where" statement
     * @return        string
     */
    function update_string($table, $data, $where) {
        if ($where == '') {
            return false;
        }
        $fields = array();
        foreach ($data as $key => $val) {
            $fields[$this->_protect_identifiers($key)] = $this->escape($val);
        }
        if (!is_array($where)) {
            $dest = array($where);
        } else {
            $dest = array();
            foreach ($where as $key => $val) {
                $prefix = (count($dest) == 0) ? '' : ' AND ';
                if ($val !== '') {
                    if (!$this->_has_operator($key)) {
                        $key .= ' =';
                    }
                    $val = ' ' . $this->escape($val);
                }
                $dest[] = $prefix . $key . $val;
            }
        }
        return $this->_update($this->_protect_identifiers($table, TRUE, NULL, FALSE), $fields, $dest);
    }
    /**
     * Tests whether the string has an SQL operator
     *
     * @access        private
     * @param        string
     * @return        bool
     */
    function _has_operator($str) {
        $str = trim($str);
        if (!preg_match("/(\s|<|>|!|=|is null|is not null)/i", $str)) {
            return FALSE;
        }
        return TRUE;
    }
    /**
     * Enables a native PHP function to be run, using a platform agnostic wrapper.
     *
     * @access        public
     * @param        string        the function name
     * @param        mixed        any parameters needed by the function
     * @return        mixed
     */
    function call_function($function) {
        $driver = ($this->dbdriver == 'postgre') ? 'pg_' : $this->dbdriver . '_';
        if (FALSE === strpos($driver, $function)) {
            $function = $driver . $function;
        }
        if (!function_exists($function)) {
            if ($this->db_debug || systemInfo('error_manage')) {
                return $this->display_error('db_unsupported_function');
            }
            return FALSE;
        } else {
            $args = (func_num_args() > 1) ? array_splice(func_get_args(), 1) : null;
            if (is_null($args)) {
                return call_user_func($function);
            } else {
                return call_user_func_array($function, $args);
            }
        }
    }
    
    /**
     * Close DB Connection
     *
     * @access        public
     * @return        void
     */
    function close() {
        if (is_resource($this->conn_id) OR is_object($this->conn_id)) {
            $this->_close($this->conn_id);
        }
        $this->conn_id = FALSE;
    }
    /**
     * Display an error message
     *
     * @access        public
     * @param        string        the error message
     * @param        string        any "swap" values
     * @param        boolean        whether to localize the message
     * @return        string        sends the application/error_db.php template
     */
    function display_error($error = '', $swap = '', $native = FALSE) {
        woniu_db_error_handler($error, $swap, $native);
    }
    /**
     * Protect Identifiers
     *
     * This function adds backticks if appropriate based on db type
     *
     * @access        private
     * @param        mixed        the item to escape
     * @return        mixed        the item with backticks
     */
    function protect_identifiers($item, $prefix_single = FALSE) {
        return $this->_protect_identifiers($item, $prefix_single);
    }
    /**
     * Protect Identifiers
     *
     * This function is used extensively by the Active Record class, and by
     * a couple functions in this class.
     * It takes a column or table name (optionally with an alias) and inserts
     * the table prefix onto it.  Some logic is necessary in order to deal with
     * column names that include the path.  Consider a query like this:
     *
     * SELECT * FROM hostname.database.table.column AS c FROM hostname.database.table
     *
     * Or a query with aliasing:
     *
     * SELECT m.member_id, m.member_name FROM members AS m
     *
     * Since the column name can include up to four segments (host, DB, table, column)
     * or also have an alias prefix, we need to do a bit of work to figure this out and
     * insert the table prefix (if it exists) in the proper position, and escape only
     * the correct identifiers.
     *
     * @access        private
     * @param        string
     * @param        bool
     * @param        mixed
     * @param        bool
     * @return        string
     */
    function _protect_identifiers($item, $prefix_single = FALSE, $protect_identifiers = NULL, $field_exists = TRUE) {
        if (!is_bool($protect_identifiers)) {
            $protect_identifiers = $this->_protect_identifiers;
        }
        if (is_array($item)) {
            $escaped_array = array();
            foreach ($item as $k => $v) {
                $escaped_array[$this->_protect_identifiers($k)] = $this->_protect_identifiers($v);
            }
            return $escaped_array;
        }
// Convert tabs or multiple spaces into single spaces
        $item = preg_replace('/[\t ]+/', ' ', $item);
// If the item has an alias declaration we remove it and set it aside.
// Basically we remove everything to the right of the first space
        if (strpos($item, ' ') !== FALSE) {
            $alias = strstr($item, ' ');
            $item = substr($item, 0, - strlen($alias));
        } else {
            $alias = '';
        }
// This is basically a bug fix for queries that use MAX, MIN, etc.
// If a parenthesis is found we know that we do not need to
// escape the data or add a prefix.  There's probably a more graceful
// way to deal with this, but I'm not thinking of it -- Rick
        if (strpos($item, '(') !== FALSE) {
            return $item . $alias;
        }
// Break the string apart if it contains periods, then insert the table prefix
// in the correct location, assuming the period doesn't indicate that we're dealing
// with an alias. While we're at it, we will escape the components
        if (strpos($item, '.') !== FALSE) {
            $parts = explode('.', $item);
// Does the first segment of the exploded item match
// one of the aliases previously identified?  If so,
// we have nothing more to do other than escape the item
            if (in_array($parts[0], $this->ar_aliased_tables)) {
                if ($protect_identifiers === TRUE) {
                    foreach ($parts as $key => $val) {
                        if (!in_array($val, $this->_reserved_identifiers)) {
                            $parts[$key] = $this->_escape_identifiers($val);
                        }
                    }
                    $item = implode('.', $parts);
                }
                return $item . $alias;
            }
// Is there a table prefix defined in the config file?  If not, no need to do anything
            if ($this->dbprefix != '') {
// We now add the table prefix based on some logic.
// Do we have 4 segments (hostname.database.table.column)?
// If so, we add the table prefix to the column name in the 3rd segment.
                if (isset($parts[3])) {
                    $i = 2;
                }
// Do we have 3 segments (database.table.column)?
// If so, we add the table prefix to the column name in 2nd position
                elseif (isset($parts[2])) {
                    $i = 1;
                }
// Do we have 2 segments (table.column)?
// If so, we add the table prefix to the column name in 1st segment
                else {
                    $i = 0;
                }
// This flag is set when the supplied $item does not contain a field name.
// This can happen when this function is being called from a JOIN.
                if ($field_exists == FALSE) {
                    $i++;
                }
// Verify table prefix and replace if necessary
                if ($this->swap_pre != '' && strncmp($parts[$i], $this->swap_pre, strlen($this->swap_pre)) === 0) {
                    $parts[$i] = preg_replace("/^" . $this->swap_pre . "(\S+?)/", $this->dbprefix . "\\1", $parts[$i]);
                }
// We only add the table prefix if it does not already exist
                if (substr($parts[$i], 0, strlen($this->dbprefix)) != $this->dbprefix) {
                    $parts[$i] = $this->dbprefix . $parts[$i];
                }
// Put the parts back together
                $item = implode('.', $parts);
            }
            if ($protect_identifiers === TRUE) {
                $item = $this->_escape_identifiers($item);
            }
            return $item . $alias;
        }
// Is there a table prefix?  If not, no need to insert it
        if ($this->dbprefix != '') {
// Verify table prefix and replace if necessary
            if ($this->swap_pre != '' && strncmp($item, $this->swap_pre, strlen($this->swap_pre)) === 0) {
                $item = preg_replace("/^" . $this->swap_pre . "(\S+?)/", $this->dbprefix . "\\1", $item);
            }
// Do we prefix an item with no segments?
            if ($prefix_single == TRUE AND substr($item, 0, strlen($this->dbprefix)) != $this->dbprefix) {
                $item = $this->dbprefix . $item;
            }
        }
        if ($protect_identifiers === TRUE AND ! in_array($item, $this->_reserved_identifiers)) {
            $item = $this->_escape_identifiers($item);
        }
        return $item . $alias;
    }
    /**
     * Dummy method that allows Active Record class to be disabled
     *
     * This function is used extensively by every db driver.
     *
     * @return        void
     */
    protected function _reset_select() {
        
    }
}

/**
 * Database Result Class
 *
 * This is the platform-independent result class.
 * This class will not be called directly. Rather, the adapter
 * class for the specific database will extend and instantiate it.
 *
 * @category        Database
 * @author                ExpressionEngine Dev Team
 * @link                http://codeigniter.com/user_guide/database/
 */
class CI_DB_result {
    var $conn_id = NULL;
    var $result_id = NULL;
    var $result_array = array();
    var $result_object = array();
    var $custom_result_object = array();
    var $current_row = 0;
    var $num_rows = 0;
    var $row_data = NULL;
    /**
     * Query result.  Acts as a wrapper function for the following functions.
     *
     * @access        public
     * @param        string        can be "object" or "array"
     * @return        mixed        either a result object or array
     */
    public function result($type = 'object') {
        if ($type == 'array')
            return $this->result_array();
        else if ($type == 'object')
            return $this->result_object();
        else
            return $this->custom_result_object($type);
    }
    /**
     * Custom query result.
     *
     * @param class_name A string that represents the type of object you want back
     * @return array of objects
     */
    public function custom_result_object($class_name) {
        if (array_key_exists($class_name, $this->custom_result_object)) {
            return $this->custom_result_object[$class_name];
        }
        if ($this->result_id === FALSE OR $this->num_rows() == 0) {
            return array();
        }
// add the data to the object
        $this->_data_seek(0);
        $result_object = array();
        while ($row = $this->_fetch_object()) {
            $object = new $class_name();
            foreach ($row as $key => $value) {
                if (method_exists($object, 'set_' . $key)) {
                    $object->{'set_' . $key}($value);
                } else {
                    $object->$key = $value;
                }
            }
            $result_object[] = $object;
        }
// return the array
        return $this->custom_result_object[$class_name] = $result_object;
    }
    /**
     * Query result.  "object" version.
     *
     * @access        public
     * @return        object
     */
    public function result_object() {
        if (count($this->result_object) > 0) {
            return $this->result_object;
        }
// In the event that query caching is on the result_id variable
// will return FALSE since there isn't a valid SQL resource so
// we'll simply return an empty array.
        if ($this->result_id === FALSE OR $this->num_rows() == 0) {
            return array();
        }
        $this->_data_seek(0);
        while ($row = $this->_fetch_object()) {
            $this->result_object[] = $row;
        }
        return $this->result_object;
    }
    /**
     * Query result.  "array" version.
     *
     * @access        public
     * @return        array
     */
    public function result_array() {
        if (count($this->result_array) > 0) {
            return $this->result_array;
        }
// In the event that query caching is on the result_id variable
// will return FALSE since there isn't a valid SQL resource so
// we'll simply return an empty array.
        if ($this->result_id === FALSE OR $this->num_rows() == 0) {
            return array();
        }
        $this->_data_seek(0);
        while ($row = $this->_fetch_assoc()) {
            $this->result_array[] = $row;
        }
        return $this->result_array;
    }
    /**
     * Query result.  Acts as a wrapper function for the following functions.
     *
     * @access        public
     * @param        string
     * @param        string        can be "object" or "array"
     * @return        mixed        either a result object or array
     */
    public function row($n = 0, $type = 'object') {
        if (!is_numeric($n)) {
// We cache the row data for subsequent uses
            if (!is_array($this->row_data)) {
                $this->row_data = $this->row_array(0);
            }
// array_key_exists() instead of isset() to allow for MySQL NULL values
            if (array_key_exists($n, $this->row_data)) {
                return $this->row_data[$n];
            }
// reset the $n variable if the result was not achieved
            $n = 0;
        }
        if ($type == 'object')
            return $this->row_object($n);
        else if ($type == 'array')
            return $this->row_array($n);
        else
            return $this->custom_row_object($n, $type);
    }
    /**
     * Assigns an item into a particular column slot
     *
     * @access        public
     * @return        object
     */
    public function set_row($key, $value = NULL) {
// We cache the row data for subsequent uses
        if (!is_array($this->row_data)) {
            $this->row_data = $this->row_array(0);
        }
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->row_data[$k] = $v;
            }
            return;
        }
        if ($key != '' AND ! is_null($value)) {
            $this->row_data[$key] = $value;
        }
    }
    /**
     * Returns a single result row - custom object version
     *
     * @access        public
     * @return        object
     */
    public function custom_row_object($n, $type) {
        $result = $this->custom_result_object($type);
        if (count($result) == 0) {
            return $result;
        }
        if ($n != $this->current_row AND isset($result[$n])) {
            $this->current_row = $n;
        }
        return $result[$this->current_row];
    }
    /**
     * Returns a single result row - object version
     *
     * @access        public
     * @return        object
     */
    public function row_object($n = 0) {
        $result = $this->result_object();
        if (count($result) == 0) {
            return $result;
        }
        if ($n != $this->current_row AND isset($result[$n])) {
            $this->current_row = $n;
        }
        return $result[$this->current_row];
    }
    /**
     * Returns a single result row - array version
     *
     * @access        public
     * @return        array
     */
    public function row_array($n = 0) {
        $result = $this->result_array();
        if (count($result) == 0) {
            return $result;
        }
        if ($n != $this->current_row AND isset($result[$n])) {
            $this->current_row = $n;
        }
        return $result[$this->current_row];
    }
    /**
     * Returns the "first" row
     *
     * @access        public
     * @return        object
     */
    public function first_row($type = 'object') {
        $result = $this->result($type);
        if (count($result) == 0) {
            return $result;
        }
        return $result[0];
    }
    /**
     * Returns the "last" row
     *
     * @access        public
     * @return        object
     */
    public function last_row($type = 'object') {
        $result = $this->result($type);
        if (count($result) == 0) {
            return $result;
        }
        return $result[count($result) - 1];
    }
    /**
     * Returns the "next" row
     *
     * @access        public
     * @return        object
     */
    public function next_row($type = 'object') {
        $result = $this->result($type);
        if (count($result) == 0) {
            return $result;
        }
        if (isset($result[$this->current_row + 1])) {
            ++$this->current_row;
        }
        return $result[$this->current_row];
    }
    /**
     * Returns the "previous" row
     *
     * @access        public
     * @return        object
     */
    public function previous_row($type = 'object') {
        $result = $this->result($type);
        if (count($result) == 0) {
            return $result;
        }
        if (isset($result[$this->current_row - 1])) {
            --$this->current_row;
        }
        return $result[$this->current_row];
    }
    /**
     * The following functions are normally overloaded by the identically named
     * methods in the platform-specific driver -- except when query caching
     * is used.  When caching is enabled we do not load the other driver.
     * These functions are primarily here to prevent undefined function errors
     * when a cached result object is in use.  They are not otherwise fully
     * operational due to the unavailability of the database resource IDs with
     * cached results.
     */
    public function num_rows() {
        return $this->num_rows;
    }
    public function num_fields() {
        return 0;
    }
    public function list_fields() {
        return array();
    }
    public function field_data() {
        return array();
    }
    public function free_result() {
        return TRUE;
    }
    protected function _data_seek() {
        return TRUE;
    }
    protected function _fetch_assoc() {
        return array();
    }
    protected function _fetch_object() {
        return array();
    }
} 
/**
 * Active Record Class
 *
 * This is the platform-independent base Active Record implementation class.
 *
 * @package                CodeIgniter
 * @subpackage        Drivers
 * @category        Database
 * @author                ExpressionEngine Dev Team
 * @link                http://codeigniter.com/user_guide/database/
 */
class CI_DB_active_record extends CI_DB_driver {
    var $ar_select = array();
    var $ar_distinct = FALSE;
    var $ar_from = array();
    var $ar_join = array();
    var $ar_where = array();
    var $ar_like = array();
    var $ar_groupby = array();
    var $ar_having = array();
    var $ar_keys = array();
    var $ar_limit = FALSE;
    var $ar_offset = FALSE;
    var $ar_order = FALSE;
    var $ar_orderby = array();
    var $ar_set = array();
    var $ar_wherein = array();
    var $ar_aliased_tables = array();
    var $ar_store_array = array();
    var $ar_no_escape = array();
    /**
     * Select
     *
     * Generates the SELECT portion of the query
     *
     * @param        string
     * @return        object
     */
    public function select($select = '*', $escape = NULL) {
        if (is_string($select)) {
            $select = explode(',', $select);
        }
        foreach ($select as $val) {
            $val = trim($val);
            if ($val != '') {
                $this->ar_select[] = $val;
                $this->ar_no_escape[] = $escape;
            }
        }
        return $this;
    }
    /**
     * Select Max
     *
     * Generates a SELECT MAX(field) portion of a query
     *
     * @param        string        the field
     * @param        string        an alias
     * @return        object
     */
    public function select_max($select = '', $alias = '') {
        return $this->_max_min_avg_sum($select, $alias, 'MAX');
    }
    /**
     * Select Min
     *
     * Generates a SELECT MIN(field) portion of a query
     *
     * @param        string        the field
     * @param        string        an alias
     * @return        object
     */
    public function select_min($select = '', $alias = '') {
        return $this->_max_min_avg_sum($select, $alias, 'MIN');
    }
    /**
     * Select Average
     *
     * Generates a SELECT AVG(field) portion of a query
     *
     * @param        string        the field
     * @param        string        an alias
     * @return        object
     */
    public function select_avg($select = '', $alias = '') {
        return $this->_max_min_avg_sum($select, $alias, 'AVG');
    }
    /**
     * Select Sum
     *
     * Generates a SELECT SUM(field) portion of a query
     *
     * @param        string        the field
     * @param        string        an alias
     * @return        object
     */
    public function select_sum($select = '', $alias = '') {
        return $this->_max_min_avg_sum($select, $alias, 'SUM');
    }
    /**
     * Processing Function for the four functions above:
     *
     *         select_max()
     *         select_min()
     *         select_avg()
     *  select_sum()
     *
     * @param        string        the field
     * @param        string        an alias
     * @return        object
     */
    protected function _max_min_avg_sum($select = '', $alias = '', $type = 'MAX') {
        if (!is_string($select) OR $select == '') {
            $this->display_error('db_invalid_query');
        }
        $type = strtoupper($type);
        if (!in_array($type, array('MAX', 'MIN', 'AVG', 'SUM'))) {
            show_error('Invalid function type: ' . $type);
        }
        if ($alias == '') {
            $alias = $this->_create_alias_from_table(trim($select));
        }
        $sql = $type . '(' . $this->_protect_identifiers(trim($select)) . ') AS ' . $alias;
        $this->ar_select[] = $sql;
        return $this;
    }
    /**
     * Determines the alias name based on the table
     *
     * @param        string
     * @return        string
     */
    protected function _create_alias_from_table($item) {
        if (strpos($item, '.') !== FALSE) {
            return end(explode('.', $item));
        }
        return $item;
    }
    /**
     * DISTINCT
     *
     * Sets a flag which tells the query string compiler to add DISTINCT
     *
     * @param        bool
     * @return        object
     */
    public function distinct($val = TRUE) {
        $this->ar_distinct = (is_bool($val)) ? $val : TRUE;
        return $this;
    }
    /**
     * From
     *
     * Generates the FROM portion of the query
     *
     * @param        mixed        can be a string or array
     * @return        object
     */
    public function from($from) {
        foreach ((array) $from as $val) {
            if (strpos($val, ',') !== FALSE) {
                foreach (explode(',', $val) as $v) {
                    $v = trim($v);
                    $this->_track_aliases($v);
                    $this->ar_from[] = $this->_protect_identifiers($v, TRUE, NULL, FALSE);
                }
            } else {
                $val = trim($val);
// Extract any aliases that might exist.  We use this information
// in the _protect_identifiers to know whether to add a table prefix
                $this->_track_aliases($val);
                $this->ar_from[] = $this->_protect_identifiers($val, TRUE, NULL, FALSE);
            }
        }
        return $this;
    }
    /**
     * Join
     *
     * Generates the JOIN portion of the query
     *
     * @param        string
     * @param        string        the join condition
     * @param        string        the type of join
     * @return        object
     */
    public function join($table, $cond, $type = '') {
        if ($type != '') {
            $type = strtoupper(trim($type));
            if (!in_array($type, array('LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER'))) {
                $type = '';
            } else {
                $type .= ' ';
            }
        }
// Extract any aliases that might exist.  We use this information
// in the _protect_identifiers to know whether to add a table prefix
        $this->_track_aliases($table);
// Strip apart the condition and protect the identifiers
        if (preg_match('/([\w\.]+)([\W\s]+)(.+)/', $cond, $match)) {
            $match[1] = $this->_protect_identifiers($match[1]);
            $match[3] = $this->_protect_identifiers($match[3]);
            $cond = $match[1] . $match[2] . $match[3];
        }
// Assemble the JOIN statement
        $join = $type . 'JOIN ' . $this->_protect_identifiers($table, TRUE, NULL, FALSE) . ' ON ' . $cond;
        $this->ar_join[] = $join;
        return $this;
    }
    /**
     * Where
     *
     * Generates the WHERE portion of the query. Separates
     * multiple calls with AND
     *
     * @param        mixed
     * @param        mixed
     * @return        object
     */
    public function where($key, $value = NULL, $escape = TRUE) {
        return $this->_where($key, $value, 'AND ', $escape);
    }
    /**
     * OR Where
     *
     * Generates the WHERE portion of the query. Separates
     * multiple calls with OR
     *
     * @param        mixed
     * @param        mixed
     * @return        object
     */
    public function or_where($key, $value = NULL, $escape = TRUE) {
        return $this->_where($key, $value, 'OR ', $escape);
    }
    /**
     * Where
     *
     * Called by where() or or_where()
     *
     * @param        mixed
     * @param        mixed
     * @param        string
     * @return        object
     */
    protected function _where($key, $value = NULL, $type = 'AND ', $escape = NULL) {
        if (!is_array($key)) {
            $key = array($key => $value);
        }
// If the escape value was not set will will base it on the global setting
        if (!is_bool($escape)) {
            $escape = $this->_protect_identifiers;
        }
        foreach ($key as $k => $v) {
            $prefix = (count($this->ar_where) == 0) ? '' : $type;
            if (is_null($v) && !$this->_has_operator($k)) {
// value appears not to have been set, assign the test to IS NULL
                $k .= ' IS NULL';
            }
            if (!is_null($v)) {
                if ($escape === TRUE) {
                    $k = $this->_protect_identifiers($k, FALSE, $escape);
                    $v = ' ' . $this->escape($v);
                }
                if (!$this->_has_operator($k)) {
                    $k .= ' = ';
                }
            } else {
                $k = $this->_protect_identifiers($k, FALSE, $escape);
            }
            $this->ar_where[] = $prefix . $k . $v;
        }
        return $this;
    }
    /**
     * Where_in
     *
     * Generates a WHERE field IN ('item', 'item') SQL query joined with
     * AND if appropriate
     *
     * @param        string        The field to search
     * @param        array        The values searched on
     * @return        object
     */
    public function where_in($key = NULL, $values = NULL) {
        return $this->_where_in($key, $values);
    }
    /**
     * Where_in_or
     *
     * Generates a WHERE field IN ('item', 'item') SQL query joined with
     * OR if appropriate
     *
     * @param        string        The field to search
     * @param        array        The values searched on
     * @return        object
     */
    public function or_where_in($key = NULL, $values = NULL) {
        return $this->_where_in($key, $values, FALSE, 'OR ');
    }
    /**
     * Where_not_in
     *
     * Generates a WHERE field NOT IN ('item', 'item') SQL query joined
     * with AND if appropriate
     *
     * @param        string        The field to search
     * @param        array        The values searched on
     * @return        object
     */
    public function where_not_in($key = NULL, $values = NULL) {
        return $this->_where_in($key, $values, TRUE);
    }
    /**
     * Where_not_in_or
     *
     * Generates a WHERE field NOT IN ('item', 'item') SQL query joined
     * with OR if appropriate
     *
     * @param        string        The field to search
     * @param        array        The values searched on
     * @return        object
     */
    public function or_where_not_in($key = NULL, $values = NULL) {
        return $this->_where_in($key, $values, TRUE, 'OR ');
    }
    /**
     * Where_in
     *
     * Called by where_in, where_in_or, where_not_in, where_not_in_or
     *
     * @param        string        The field to search
     * @param        array        The values searched on
     * @param        boolean        If the statement would be IN or NOT IN
     * @param        string
     * @return        object
     */
    protected function _where_in($key = NULL, $values = NULL, $not = FALSE, $type = 'AND ') {
        if ($key === NULL OR $values === NULL) {
            return;
        }
        if (!is_array($values)) {
            $values = array($values);
        } elseif (empty($values)) {
            $values = array('');
        }
        $not = ($not) ? ' NOT' : '';
        foreach ($values as $value) {
            $this->ar_wherein[] = $this->escape($value);
        }
        $prefix = (count($this->ar_where) == 0) ? '' : $type;
        $where_in = $prefix . $this->_protect_identifiers($key) . $not . " IN (" . implode(", ", $this->ar_wherein) . ") ";
        $this->ar_where[] = $where_in;
// reset the array for multiple calls
        $this->ar_wherein = array();
        return $this;
    }
    /**
     * Like
     *
     * Generates a %LIKE% portion of the query. Separates
     * multiple calls with AND
     *
     * @param        mixed
     * @param        mixed
     * @return        object
     */
    public function like($field, $match = '', $side = 'both') {
        return $this->_like($field, $match, 'AND ', $side);
    }
    /**
     * Not Like
     *
     * Generates a NOT LIKE portion of the query. Separates
     * multiple calls with AND
     *
     * @param        mixed
     * @param        mixed
     * @return        object
     */
    public function not_like($field, $match = '', $side = 'both') {
        return $this->_like($field, $match, 'AND ', $side, 'NOT');
    }
    /**
     * OR Like
     *
     * Generates a %LIKE% portion of the query. Separates
     * multiple calls with OR
     *
     * @param        mixed
     * @param        mixed
     * @return        object
     */
    public function or_like($field, $match = '', $side = 'both') {
        return $this->_like($field, $match, 'OR ', $side);
    }
    /**
     * OR Not Like
     *
     * Generates a NOT LIKE portion of the query. Separates
     * multiple calls with OR
     *
     * @param        mixed
     * @param        mixed
     * @return        object
     */
    public function or_not_like($field, $match = '', $side = 'both') {
        return $this->_like($field, $match, 'OR ', $side, 'NOT');
    }
    /**
     * Like
     *
     * Called by like() or orlike()
     *
     * @param        mixed
     * @param        mixed
     * @param        string
     * @return        object
     */
    protected function _like($field, $match = '', $type = 'AND ', $side = 'both', $not = '') {
        if (!is_array($field)) {
            $field = array($field => $match);
        }
        foreach ($field as $k => $v) {
            $k = $this->_protect_identifiers($k);
            $prefix = (count($this->ar_like) == 0) ? '' : $type;
            $v = $this->escape_like_str($v);
            if ($side == 'none') {
                $like_statement = $prefix . " $k $not LIKE '{$v}'";
            } elseif ($side == 'before') {
                $like_statement = $prefix . " $k $not LIKE '%{$v}'";
            } elseif ($side == 'after') {
                $like_statement = $prefix . " $k $not LIKE '{$v}%'";
            } else {
                $like_statement = $prefix . " $k $not LIKE '%{$v}%'";
            }
// some platforms require an escape sequence definition for LIKE wildcards
            if ($this->_like_escape_str != '') {
                $like_statement = $like_statement . sprintf($this->_like_escape_str, $this->_like_escape_chr);
            }
            $this->ar_like[] = $like_statement;
        }
        return $this;
    }
    /**
     * GROUP BY
     *
     * @param        string
     * @return        object
     */
    public function group_by($by) {
        if (is_string($by)) {
            $by = explode(',', $by);
        }
        foreach ($by as $val) {
            $val = trim($val);
            if ($val != '') {
                $this->ar_groupby[] = $this->_protect_identifiers($val);
            }
        }
        return $this;
    }
    /**
     * Sets the HAVING value
     *
     * Separates multiple calls with AND
     *
     * @param        string
     * @param        string
     * @return        object
     */
    public function having($key, $value = '', $escape = TRUE) {
        return $this->_having($key, $value, 'AND ', $escape);
    }
    /**
     * Sets the OR HAVING value
     *
     * Separates multiple calls with OR
     *
     * @param        string
     * @param        string
     * @return        object
     */
    public function or_having($key, $value = '', $escape = TRUE) {
        return $this->_having($key, $value, 'OR ', $escape);
    }
    /**
     * Sets the HAVING values
     *
     * Called by having() or or_having()
     *
     * @param        string
     * @param        string
     * @return        object
     */
    protected function _having($key, $value = '', $type = 'AND ', $escape = TRUE) {
        if (!is_array($key)) {
            $key = array($key => $value);
        }
        foreach ($key as $k => $v) {
            $prefix = (count($this->ar_having) == 0) ? '' : $type;
            if ($escape === TRUE) {
                $k = $this->_protect_identifiers($k);
            }
            if (!$this->_has_operator($k)) {
                $k .= ' = ';
            }
            if ($v != '') {
                $v = ' ' . $this->escape($v);
            }
            $this->ar_having[] = $prefix . $k . $v;
        }
        return $this;
    }
    /**
     * Sets the ORDER BY value
     *
     * @param        string
     * @param        string        direction: asc or desc
     * @return        object
     */
    public function order_by($orderby, $direction = '') {
        if (strtolower($direction) == 'random') {
            $orderby = ''; // Random results want or don't need a field name
            $direction = $this->_random_keyword;
        } elseif (trim($direction) != '') {
            $direction = (in_array(strtoupper(trim($direction)), array('ASC', 'DESC'), TRUE)) ? ' ' . $direction : ' ASC';
        }
        if (strpos($orderby, ',') !== FALSE) {
            $temp = array();
            foreach (explode(',', $orderby) as $part) {
                $part = trim($part);
                if (!in_array($part, $this->ar_aliased_tables)) {
                    $part = $this->_protect_identifiers(trim($part));
                }
                $temp[] = $part;
            }
            $orderby = implode(', ', $temp);
        } else if ($direction != $this->_random_keyword) {
            $orderby = $this->_protect_identifiers($orderby);
        }
        $orderby_statement = $orderby . $direction;
        $this->ar_orderby[] = $orderby_statement;
        return $this;
    }
    /**
     * Sets the LIMIT value
     *
     * @param        integer        the limit value
     * @param        integer        the offset value
     * @return        object
     */
    public function limit($value, $offset = '') {
        $this->ar_limit = (int) $value;
        if ($offset != '') {
            $this->ar_offset = (int) $offset;
        }
        return $this;
    }
    /**
     * Sets the OFFSET value
     *
     * @param        integer        the offset value
     * @return        object
     */
    public function offset($offset) {
        $this->ar_offset = $offset;
        return $this;
    }
    /**
     * The "set" function.  Allows key/value pairs to be set for inserting or updating
     *
     * @param        mixed
     * @param        string
     * @param        boolean
     * @return        object
     */
    public function set($key, $value = '', $escape = TRUE) {
        $key = $this->_object_to_array($key);
        if (!is_array($key)) {
            $key = array($key => $value);
        }
        foreach ($key as $k => $v) {
            if ($escape === FALSE) {
                $this->ar_set[$this->_protect_identifiers($k)] = $v;
            } else {
                $this->ar_set[$this->_protect_identifiers($k, FALSE, TRUE)] = $this->escape($v);
            }
        }
        return $this;
    }
    /**
     * Get
     *
     * Compiles the select statement based on the other functions called
     * and runs the query
     *
     * @param        string        the table
     * @param        string        the limit clause
     * @param        string        the offset clause
     * @return        object
     */
    public function get($table = '', $limit = null, $offset = null) {
        if ($table != '') {
            $this->_track_aliases($table);
            $this->from($table);
        }
        if (!is_null($limit)) {
            $this->limit($limit, $offset);
        }
        $sql = $this->_compile_select();
        $result = $this->query($sql);
        $this->_reset_select();
        return $result;
    }
    /**
     * "Count All Results" query
     *
     * Generates a platform-specific query string that counts all records
     * returned by an Active Record query.
     *
     * @param        string
     * @return        string
     */
    public function count_all_results($table = '') {
        if ($table != '') {
            $this->_track_aliases($table);
            $this->from($table);
        }
        $sql = $this->_compile_select($this->_count_string . $this->_protect_identifiers('numrows'));
        $query = $this->query($sql);
        $this->_reset_select();
        if ($query->num_rows() == 0) {
            return 0;
        }
        $row = $query->row();
        return (int) $row->numrows;
    }
    /**
     * Get_Where
     *
     * Allows the where clause, limit and offset to be added directly
     *
     * @param        string        the where clause
     * @param        string        the limit clause
     * @param        string        the offset clause
     * @return        object
     */
    public function get_where($table = '', $where = null, $limit = null, $offset = null) {
        if ($table != '') {
            $this->from($table);
        }
        if (!is_null($where)) {
            $this->where($where);
        }
        if (!is_null($limit)) {
            $this->limit($limit, $offset);
        }
        $sql = $this->_compile_select();
        $result = $this->query($sql);
        $this->_reset_select();
        return $result;
    }
    /**
     * Insert_Batch
     *
     * Compiles batch insert strings and runs the queries
     *
     * @param        string        the table to retrieve the results from
     * @param        array        an associative array of insert values
     * @return        object
     */
    public function insert_batch($table = '', $set = NULL) {
        if (!is_null($set)) {
            $this->set_insert_batch($set);
        }
        if (count($this->ar_set) == 0) {
            if ($this->db_debug || systemInfo('error_manage')) {
//No valid data array.  Folds in cases where keys and values did not match up
                return $this->display_error('db_must_use_set');
            }
            return FALSE;
        }
        if ($table == '') {
            if (!isset($this->ar_from[0])) {
                if ($this->db_debug || systemInfo('error_manage')) {
                    return $this->display_error('db_must_set_table');
                }
                return FALSE;
            }
            $table = $this->ar_from[0];
        }
// Batch this baby
        for ($i = 0, $total = count($this->ar_set); $i < $total; $i = $i + 100) {
            $sql = $this->_insert_batch($this->_protect_identifiers($table, TRUE, NULL, FALSE), $this->ar_keys, array_slice($this->ar_set, $i, 100));
//echo $sql;
            $this->query($sql);
        }
        $this->_reset_write();
        return TRUE;
    }
    /**
     * The "set_insert_batch" function.  Allows key/value pairs to be set for batch inserts
     *
     * @param        mixed
     * @param        string
     * @param        boolean
     * @return        object
     */
    public function set_insert_batch($key, $value = '', $escape = TRUE) {
        $key = $this->_object_to_array_batch($key);
        if (!is_array($key)) {
            $key = array($key => $value);
        }
        $keys = array_keys(current($key));
        sort($keys);
        foreach ($key as $row) {
            if (count(array_diff($keys, array_keys($row))) > 0 OR count(array_diff(array_keys($row), $keys)) > 0) {
// batch function above returns an error on an empty array
                $this->ar_set[] = array();
                return;
            }
            ksort($row); // puts $row in the same order as our keys
            if ($escape === FALSE) {
                $this->ar_set[] = '(' . implode(',', $row) . ')';
            } else {
                $clean = array();
                foreach ($row as $value) {
                    $clean[] = $this->escape($value);
                }
                $this->ar_set[] = '(' . implode(',', $clean) . ')';
            }
        }
        foreach ($keys as $k) {
            $this->ar_keys[] = $this->_protect_identifiers($k);
        }
        return $this;
    }
    /**
     * Insert
     *
     * Compiles an insert string and runs the query
     *
     * @param        string        the table to insert data into
     * @param        array        an associative array of insert values
     * @return        object
     */
    function insert($table = '', $set = NULL) {
        if (!is_null($set)) {
            $this->set($set);
        }
        if (count($this->ar_set) == 0) {
            if ($this->db_debug || systemInfo('error_manage')) {
                return $this->display_error('db_must_use_set');
            }
            return FALSE;
        }
        if ($table == '') {
            if (!isset($this->ar_from[0])) {
                if ($this->db_debug || systemInfo('error_manage')) {
                    return $this->display_error('db_must_set_table');
                }
                return FALSE;
            }
            $table = $this->ar_from[0];
        }
        $sql = $this->_insert($this->_protect_identifiers($table, TRUE, NULL, FALSE), array_keys($this->ar_set), array_values($this->ar_set));
        $this->_reset_write();
        return $this->query($sql);
    }
    /**
     * Replace
     *
     * Compiles an replace into string and runs the query
     *
     * @param        string        the table to replace data into
     * @param        array        an associative array of insert values
     * @return        object
     */
    public function replace($table = '', $set = NULL) {
        if (!is_null($set)) {
            $this->set($set);
        }
        if (count($this->ar_set) == 0) {
            if ($this->db_debug || systemInfo('error_manage')) {
                return $this->display_error('db_must_use_set');
            }
            return FALSE;
        }
        if ($table == '') {
            if (!isset($this->ar_from[0])) {
                if ($this->db_debug || systemInfo('error_manage')) {
                    return $this->display_error('db_must_set_table');
                }
                return FALSE;
            }
            $table = $this->ar_from[0];
        }
        $sql = $this->_replace($this->_protect_identifiers($table, TRUE, NULL, FALSE), array_keys($this->ar_set), array_values($this->ar_set));
        $this->_reset_write();
        return $this->query($sql);
    }
    /**
     * Update
     *
     * Compiles an update string and runs the query
     *
     * @param        string        the table to retrieve the results from
     * @param        array        an associative array of update values
     * @param        mixed        the where clause
     * @return        object
     */
    public function update($table = '', $set = NULL, $where = NULL, $limit = NULL) {
// Combine any cached components with the current statements
        if (!is_null($set)) {
            $this->set($set);
        }
        if (count($this->ar_set) == 0) {
            if ($this->db_debug || systemInfo('error_manage')) {
                return $this->display_error('db_must_use_set');
            }
            return FALSE;
        }
        if ($table == '') {
            if (!isset($this->ar_from[0])) {
                if ($this->db_debug || systemInfo('error_manage')) {
                    return $this->display_error('db_must_set_table');
                }
                return FALSE;
            }
            $table = $this->ar_from[0];
        }
        if ($where != NULL) {
            $this->where($where);
        }
        if ($limit != NULL) {
            $this->limit($limit);
        }
        $sql = $this->_update($this->_protect_identifiers($table, TRUE, NULL, FALSE), $this->ar_set, $this->ar_where, $this->ar_orderby, $this->ar_limit);
        $this->_reset_write();
        return $this->query($sql);
    }
    /**
     * Update_Batch
     *
     * Compiles an update string and runs the query
     *
     * @param        string        the table to retrieve the results from
     * @param        array        an associative array of update values
     * @param        string        the where key
     * @return        object
     */
    public function update_batch($table = '', $set = NULL, $index = NULL) {
        if (is_null($index)) {
            if ($this->db_debug || systemInfo('error_manage')) {
                return $this->display_error('db_must_use_index');
            }
            return FALSE;
        }
        if (!is_null($set)) {
            $this->set_update_batch($set, $index);
        }
        if (count($this->ar_set) == 0) {
            if ($this->db_debug || systemInfo('error_manage')) {
                return $this->display_error('db_must_use_set');
            }
            return FALSE;
        }
        if ($table == '') {
            if (!isset($this->ar_from[0])) {
                if ($this->db_debug || systemInfo('error_manage')) {
                    return $this->display_error('db_must_set_table');
                }
                return FALSE;
            }
            $table = $this->ar_from[0];
        }
// Batch this baby
        for ($i = 0, $total = count($this->ar_set); $i < $total; $i = $i + 100) {
            $sql = $this->_update_batch($this->_protect_identifiers($table, TRUE, NULL, FALSE), array_slice($this->ar_set, $i, 100), $this->_protect_identifiers($index), $this->ar_where);
            $this->query($sql);
        }
        $this->_reset_write();
    }
    /**
     * The "set_update_batch" function.  Allows key/value pairs to be set for batch updating
     *
     * @param        array
     * @param        string
     * @param        boolean
     * @return        object
     */
    public function set_update_batch($key, $index = '', $escape = TRUE) {
        $key = $this->_object_to_array_batch($key);
        if (!is_array($key)) {
// @todo error
        }
        foreach ($key as $k => $v) {
            $index_set = FALSE;
            $clean = array();
            foreach ($v as $k2 => $v2) {
                if ($k2 == $index) {
                    $index_set = TRUE;
                } else {
                    $not[] = $k2 . '-' . $v2;
                }
                if ($escape === FALSE) {
                    $clean[$this->_protect_identifiers($k2)] = $v2;
                } else {
                    $clean[$this->_protect_identifiers($k2)] = $this->escape($v2);
                }
            }
            if ($index_set == FALSE) {
                return $this->display_error('db_batch_missing_index');
            }
            $this->ar_set[] = $clean;
        }
        return $this;
    }
    /**
     * Empty Table
     *
     * Compiles a delete string and runs "DELETE FROM table"
     *
     * @param        string        the table to empty
     * @return        object
     */
    public function empty_table($table = '') {
        if ($table == '') {
            if (!isset($this->ar_from[0])) {
                if ($this->db_debug || systemInfo('error_manage')) {
                    return $this->display_error('db_must_set_table');
                }
                return FALSE;
            }
            $table = $this->ar_from[0];
        } else {
            $table = $this->_protect_identifiers($table, TRUE, NULL, FALSE);
        }
        $sql = $this->_delete($table);
        $this->_reset_write();
        return $this->query($sql);
    }
    /**
     * Truncate
     *
     * Compiles a truncate string and runs the query
     * If the database does not support the truncate() command
     * This function maps to "DELETE FROM table"
     *
     * @param        string        the table to truncate
     * @return        object
     */
    public function truncate($table = '') {
        if ($table == '') {
            if (!isset($this->ar_from[0])) {
                if ($this->db_debug || systemInfo('error_manage')) {
                    return $this->display_error('db_must_set_table');
                }
                return FALSE;
            }
            $table = $this->ar_from[0];
        } else {
            $table = $this->_protect_identifiers($table, TRUE, NULL, FALSE);
        }
        $sql = $this->_truncate($table);
        $this->_reset_write();
        return $this->query($sql);
    }
    /**
     * Delete
     *
     * Compiles a delete string and runs the query
     *
     * @param        mixed        the table(s) to delete from. String or array
     * @param        mixed        the where clause
     * @param        mixed        the limit clause
     * @param        boolean
     * @return        object
     */
    public function delete($table = '', $where = '', $limit = NULL, $reset_data = TRUE) {
        if ($table == '') {
            if (!isset($this->ar_from[0])) {
                if ($this->db_debug || systemInfo('error_manage')) {
                    return $this->display_error('db_must_set_table');
                }
                return FALSE;
            }
            $table = $this->ar_from[0];
        } elseif (is_array($table)) {
            foreach ($table as $single_table) {
                $this->delete($single_table, $where, $limit, FALSE);
            }
            $this->_reset_write();
            return;
        } else {
            $table = $this->_protect_identifiers($table, TRUE, NULL, FALSE);
        }
        if ($where != '') {
            $this->where($where);
        }
        if ($limit != NULL) {
            $this->limit($limit);
        }
        if (count($this->ar_where) == 0 && count($this->ar_wherein) == 0 && count($this->ar_like) == 0) {
            if ($this->db_debug || systemInfo('error_manage')) {
                return $this->display_error('db_del_must_use_where');
            }
            return FALSE;
        }
        $sql = $this->_delete($table, $this->ar_where, $this->ar_like, $this->ar_limit);
        if ($reset_data) {
            $this->_reset_write();
        }
        return $this->query($sql);
    }
    /**
     * DB Prefix
     *
     * Prepends a database prefix if one exists in configuration
     *
     * @param        string        the table
     * @return        string
     */
    public function dbprefix($table = '') {
        if ($table == '') {
            $this->display_error('db_table_name_required');
        }
        return $this->dbprefix . $table;
    }
    /**
     * Set DB Prefix
     *
     * Set's the DB Prefix to something new without needing to reconnect
     *
     * @param        string        the prefix
     * @return        string
     */
    public function set_dbprefix($prefix = '') {
        return $this->dbprefix = $prefix;
    }
    /**
     * Track Aliases
     *
     * Used to track SQL statements written with aliased tables.
     *
     * @param        string        The table to inspect
     * @return        string
     */
    protected function _track_aliases($table) {
        if (is_array($table)) {
            foreach ($table as $t) {
                $this->_track_aliases($t);
            }
            return;
        }
// Does the string contain a comma?  If so, we need to separate
// the string into discreet statements
        if (strpos($table, ',') !== FALSE) {
            return $this->_track_aliases(explode(',', $table));
        }
// if a table alias is used we can recognize it by a space
        if (strpos($table, " ") !== FALSE) {
// if the alias is written with the AS keyword, remove it
            $table = preg_replace('/\s+AS\s+/i', ' ', $table);
// Grab the alias
            $table = trim(strrchr($table, " "));
// Store the alias, if it doesn't already exist
            if (!in_array($table, $this->ar_aliased_tables)) {
                $this->ar_aliased_tables[] = $table;
            }
        }
    }
    /**
     * Compile the SELECT statement
     *
     * Generates a query string based on which functions were used.
     * Should not be called directly.  The get() function calls it.
     *
     * @return        string
     */
    protected function _compile_select($select_override = FALSE) {
// ----------------------------------------------------------------
// Write the "select" portion of the query
        if ($select_override !== FALSE) {
            $sql = $select_override;
        } else {
            $sql = (!$this->ar_distinct) ? 'SELECT ' : 'SELECT DISTINCT ';
            if (count($this->ar_select) == 0) {
                $sql .= '*';
            } else {
// Cycle through the "select" portion of the query and prep each column name.
// The reason we protect identifiers here rather then in the select() function
// is because until the user calls the from() function we don't know if there are aliases
                foreach ($this->ar_select as $key => $val) {
                    $no_escape = isset($this->ar_no_escape[$key]) ? $this->ar_no_escape[$key] : NULL;
                    $this->ar_select[$key] = $this->_protect_identifiers($val, FALSE, $no_escape);
                }
                $sql .= implode(', ', $this->ar_select);
            }
        }
// ----------------------------------------------------------------
// Write the "FROM" portion of the query
        if (count($this->ar_from) > 0) {
            $sql .= "\nFROM ";
            $sql .= $this->_from_tables($this->ar_from);
        }
// ----------------------------------------------------------------
// Write the "JOIN" portion of the query
        if (count($this->ar_join) > 0) {
            $sql .= "\n";
            $sql .= implode("\n", $this->ar_join);
        }
// ----------------------------------------------------------------
// Write the "WHERE" portion of the query
        if (count($this->ar_where) > 0 OR count($this->ar_like) > 0) {
            $sql .= "\nWHERE ";
        }
        $sql .= implode("\n", $this->ar_where);
// ----------------------------------------------------------------
// Write the "LIKE" portion of the query
        if (count($this->ar_like) > 0) {
            if (count($this->ar_where) > 0) {
                $sql .= "\nAND ";
            }
            $sql .= implode("\n", $this->ar_like);
        }
// ----------------------------------------------------------------
// Write the "GROUP BY" portion of the query
        if (count($this->ar_groupby) > 0) {
            $sql .= "\nGROUP BY ";
            $sql .= implode(', ', $this->ar_groupby);
        }
// ----------------------------------------------------------------
// Write the "HAVING" portion of the query
        if (count($this->ar_having) > 0) {
            $sql .= "\nHAVING ";
            $sql .= implode("\n", $this->ar_having);
        }
// ----------------------------------------------------------------
// Write the "ORDER BY" portion of the query
        if (count($this->ar_orderby) > 0) {
            $sql .= "\nORDER BY ";
            $sql .= implode(', ', $this->ar_orderby);
            if ($this->ar_order !== FALSE) {
                $sql .= ($this->ar_order == 'desc') ? ' DESC' : ' ASC';
            }
        }
// ----------------------------------------------------------------
// Write the "LIMIT" portion of the query
        if (is_numeric($this->ar_limit)) {
            $sql .= "\n";
            $sql = $this->_limit($sql, $this->ar_limit, $this->ar_offset);
        }
        return $sql;
    }
    /**
     * Object to Array
     *
     * Takes an object as input and converts the class variables to array key/vals
     *
     * @param        object
     * @return        array
     */
    public function _object_to_array($object) {
        if (!is_object($object)) {
            return $object;
        }
        $array = array();
        foreach (get_object_vars($object) as $key => $val) {
// There are some built in keys we need to ignore for this conversion
            if (!is_object($val) && !is_array($val) && $key != '_parent_name') {
                $array[$key] = $val;
            }
        }
        return $array;
    }
    /**
     * Object to Array
     *
     * Takes an object as input and converts the class variables to array key/vals
     *
     * @param        object
     * @return        array
     */
    public function _object_to_array_batch($object) {
        if (!is_object($object)) {
            return $object;
        }
        $array = array();
        $out = get_object_vars($object);
        $fields = array_keys($out);
        foreach ($fields as $val) {
// There are some built in keys we need to ignore for this conversion
            if ($val != '_parent_name') {
                $i = 0;
                foreach ($out[$val] as $data) {
                    $array[$i][$val] = $data;
                    $i++;
                }
            }
        }
        return $array;
    }

    /**
     * Resets the active record values.  Called by the get() function
     *
     * @param        array        An array of fields to reset
     * @return        void
     */
    protected function _reset_run($ar_reset_items) {
        foreach ($ar_reset_items as $item => $default_value) {
            if (!in_array($item, $this->ar_store_array)) {
                $this->$item = $default_value;
            }
        }
    }
    /**
     * Resets the active record values.  Called by the get() function
     *
     * @return        void
     */
    protected function _reset_select() {
        $ar_reset_items = array(
            'ar_select' => array(),
            'ar_from' => array(),
            'ar_join' => array(),
            'ar_where' => array(),
            'ar_like' => array(),
            'ar_groupby' => array(),
            'ar_having' => array(),
            'ar_orderby' => array(),
            'ar_wherein' => array(),
            'ar_aliased_tables' => array(),
            'ar_no_escape' => array(),
            'ar_distinct' => FALSE,
            'ar_limit' => FALSE,
            'ar_offset' => FALSE,
            'ar_order' => FALSE,
        );
        $this->_reset_run($ar_reset_items);
    }
    /**
     * Resets the active record "write" values.
     *
     * Called by the insert() update() insert_batch() update_batch() and delete() functions
     *
     * @return        void
     */
    protected function _reset_write() {
        $ar_reset_items = array(
            'ar_set' => array(),
            'ar_from' => array(),
            'ar_where' => array(),
            'ar_like' => array(),
            'ar_orderby' => array(),
            'ar_keys' => array(),
            'ar_limit' => FALSE,
            'ar_order' => FALSE
        );
        $this->_reset_run($ar_reset_items);
    }
}
function log_message($level, $msg) {/* just suppress logging */
}