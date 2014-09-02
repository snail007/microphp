<?php
/**
 * PDO Database Adapter Class
 *
 * Note: _DB is an extender class that the app controller
 * creates dynamically based on whether the active record
 * class is being used or not.
 *
 * @package		CodeIgniter
 * @subpackage	Drivers
 * @category	Database
 * @author		Dready
 * @link		http://dready.jexiste.fr/dotclear/
 */
class CI_DB_sqlite3_driver extends CI_DB {
// Added by Xi
    var $dbdriver = 'pdo';
    var $_escape_char = ''; // The character used to escape with - not needed for SQLite
    var $conn_id;
    var $_random_keyword = ' Random()'; // database specific random keyword
// clause and character used for LIKE escape sequences - not used in MySQL
    var $_like_escape_str = '';
    var $_like_escape_chr = '';
    /**
     * Whether to use the MySQL "delete hack" which allows the number
     * of affected rows to be shown. Uses a preg_replace when enabled,
     * adding a bit more processing to all queries.
     */
    var $delete_hack = TRUE;
    /**
     * The syntax to count rows is slightly different across different
     * database engines, so this string appears in each driver and is
     * used for the count_all() and count_all_results() functions.
     */
    var $_count_string = 'SELECT COUNT(*) AS ';
// whether SET NAMES must be used to set the character set
    var $use_set_names;
    /**
     * Non-persistent database connection
     *
     * @access	private called by the base class
     * @return	resource
     */
    function db_connect() {
        $conn_id = false;
        try {
            $conn_id = new PDO($this->database, $this->username, $this->password);
            log_message('debug', "PDO driver connecting " . $this->database);
        } catch (PDOException $e) {
            log_message('debug', 'merde');
            log_message('error', $e->getMessage());
            if ($this->db_debug) {
                $this->display_error($e->getMessage(), '', TRUE);
            }
        }
        log_message('debug', print_r($conn_id, true));
        if ($conn_id) {
            log_message('debug', 'PDO driver connection ok');
        }
        // Added by Xi
        $this->conn_id = $conn_id;
        return $conn_id;
    }
    /**
     * Show column query
     *
     * Generates a platform-specific query string so that the column names can be fetched
     *
     * @access	public
     * @param	string	the table name
     * @return	string
     */
    function _list_columns($table = '') {
        return "PRAGMA table_info('" . $this->_protect_identifiers($table, TRUE, NULL, FALSE) . "') ";
    }
    
    /**
     * Persistent database connection
     *
     * @access	private, called by the base class
     * @return	resource
     */
    function db_pconnect() {
        // For SQLite architecture can not enable persistent connection
        return $this->db_connect();
    }
    
    /**
     * Select the database
     *
     * @access	private called by the base class
     * @return	resource
     */
    function db_select() {
        return TRUE;
    }
    
    /**
     * Execute the query
     *
     * @access	private, called by the base class
     * @param	string	an SQL query
     * @return	resource
     */
    function _execute($sql) {
        $sql = $this->_prep_query($sql);
        log_message('debug', 'SQL : ' . $sql);
        return @$this->conn_id->query($sql);
    }
    
    /**
     * Prep the query
     *
     * If needed, each database adapter can prep the query string
     *
     * @access	private called by execute()
     * @param	string	an SQL query
     * @return	string
     */
    function &_prep_query($sql) {
        return $sql;
    }
// Modify by Xi
    /**
     * "Smart" Escape String
     *
     * Escapes data based on type
     * Sets boolean and null types
     *
     * @access	public
     * @param	string
     * @return	integer
     */
    function escape($str) {
        switch (gettype($str)) {
            case 'string' : $str = "'" . $this->escape_str($str) . "'";
                break;
            case 'boolean' : $str = ($str === FALSE) ? 0 : 1;
                break;
            default : $str = ($str === NULL) ? 'NULL' : $str;
                break;
        }
        return $str;
    }
    
    /**
     * Escape String         
     *         
     * @access      public         
     * @param       string         
     * @return      string         
     */
    function escape_str($str) {
        if (function_exists('sqlite_escape_string')) {
            return sqlite_escape_string($str);
        } else {
            return SQLite3::escapeString($str);
        }
    }
// Added by Xi
    /**     * Escape the SQL Identifiers * 
     * This function escapes column and table names * 
     * @accessprivate 
     * @paramstring 
     * @returnstring */
    function _escape_identifiers($item) {
        if ($this->_escape_char == '') {
            return $item;
        }
        foreach ($this->_reserved_identifiers as $id) {
            if (strpos($item, '.' . $id) !== FALSE) {
                $str = $this->_escape_char . str_replace('.', $this->_escape_char . '.', $item);
                // remove duplicates if the user already included the escape
                return preg_replace('/[' . $this->_escape_char . ']+/', $this->_escape_char, $str);
            }
        }
        if (strpos($item, '.') !== FALSE) {
            $str = $this->_escape_char . str_replace('.', $this->_escape_char . '.' . $this->_escape_char, $item) . $this->_escape_char;
        } else {
            $str = $this->_escape_char . $item . $this->_escape_char;
        }
        // remove duplicates if the user already included the escape
        return preg_replace('/[' . $this->_escape_char . ']+/', $this->_escape_char, $str);
    }
// Add by Xi
    /**
     * Begin Transaction
     *
     * @access	public
     * @return	bool		
     */
    function trans_begin($test_mode = FALSE) {
        if (!$this->trans_enabled) {
            return TRUE;
        }
        // When transactions are nested we only begin/commit/rollback the outermost ones
        if ($this->_trans_depth > 0) {
            return TRUE;
        }
        // Reset the transaction failure flag.
        // If the $test_mode flag is set to TRUE transactions will be rolled back
        // even if the queries produce a successful result.
        $this->_trans_failure = ($test_mode === TRUE) ? TRUE : FALSE;
        $this->simple_query('BEGIN TRANSACTION');
        return TRUE;
    }
    
// Add by Xi
    /**
     * Commit Transaction
     *
     * @access	public
     * @return	bool		
     */
    function trans_commit() {
        if (!$this->trans_enabled) {
            return TRUE;
        }
        // When transactions are nested we only begin/commit/rollback the outermost ones
        if ($this->_trans_depth > 0) {
            return TRUE;
        }
        $this->simple_query('COMMIT');
        return TRUE;
    }
    
// Add by Xi
    /**
     * Rollback Transaction
     *
     * @access	public
     * @return	bool		
     */
    function trans_rollback() {
        if (!$this->trans_enabled) {
            return TRUE;
        }
        // When transactions are nested we only begin/commit/rollback the outermost ones
        if ($this->_trans_depth > 0) {
            return TRUE;
        }
        $this->simple_query('ROLLBACK');
        return TRUE;
    }
    
    /**
     * Close DB Connection
     *
     * @access	public
     * @param	resource
     * @return	void
     */
    function destroy($conn_id) {
        $conn_id = null;
    }
    
    /**
     * Insert ID
     *
     * @access	public
     * @return	integer
     */
    function insert_id() {
        return @$this->conn_id->lastInsertId();
    }
    
    /**
     * "Count All" query
     *
     * Generates a platform-specific query string that counts all records in
     * the specified database
     *
     * @access	public
     * @param	string
     * @return	string
     */
    function count_all($table = '') {
        if ($table == '')
            return '0';
        $query = $this->query("SELECT COUNT(*) AS numrows FROM `" . $table . "`");
        if ($query->num_rows() == 0)
            return '0';
        $row = $query->row();
        return $row->numrows;
    }
    
    /**
     * The error message string
     *
     * @access	private
     * @return	string
     */
    function _error_message() {
        $infos = $this->conn_id->errorInfo();
        return $infos[2];
    }
    
    /**
     * The error message number
     *
     * @access	private
     * @return	integer
     */
    function _error_number() {
        $infos = $this->conn_id->errorInfo();
        return $infos[1];
    }
    
    /**
     * Version number query string
     *
     * @access	public
     * @return	string
     */
    function version() {
        return $this->conn_id->getAttribute(constant("PDO::ATTR_SERVER_VERSION"));
    }
    
    /**
     * Escape Table Name
     *
     * This function adds backticks if the table name has a period
     * in it. Some DBs will get cranky unless periods are escaped
     *
     * @access	public
     * @param	string	the table name
     * @return	string
     */
    function escape_table($table) {
        if (stristr($table, '.')) {
            $table = preg_replace("/\./", "`.`", $table);
        }
        return $table;
    }
    
    /**
     * Field data query
     *
     * Generates a platform-specific query so that the column data can be retrieved
     *
     * @access	public
     * @param	string	the table name
     * @return	object
     */
    function _field_data($table) {
        $sql = "SELECT * FROM " . $this->escape_table($table) . " LIMIT 1";
        $query = $this->query($sql);
        return $query->field_data();
    }
    
    /**
     * Insert statement
     *
     * Generates a platform-specific insert string from the supplied data
     *
     * @access	public
     * @param	string	the table name
     * @param	array	the insert keys
     * @param	array	the insert values
     * @return	string
     */
    function _insert($table, $keys, $values) {
        return "INSERT INTO " . $this->escape_table($table) . " (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ")";
    }
    
    /**
     * Update statement
     *
     * Generates a platform-specific update string from the supplied data
     *
     * @access	public
     * @param	string	the table name
     * @param	array	the update data
     * @param	array	the where clause
     * @return	string
     */
    function _update($table, $values, $where) {
        foreach ($values as $key => $val) {
            $valstr[] = $key . " = " . $val;
        }
        return "UPDATE " . $this->escape_table($table) . " SET " . implode(', ', $valstr) . " WHERE " . implode(" ", $where);
    }
    
    /**
     * Delete statement
     *
     * Generates a platform-specific delete string from the supplied data
     *
     * @access	public
     * @param	string	the table name
     * @param	array	the where clause
     * @return	string
     */
    function _delete($table, $where) {
        return "DELETE FROM " . $this->escape_table($table) . " WHERE " . implode(" ", $where);
    }
    
    /**
     * Show table query
     *
     * Generates a platform-specific query string so that the table names can be fetched
     *
     * @access	public
     * @return	string
     */
    function _show_tables() {
        return "SELECT name from sqlite_master WHERE type='table'";
    }
    
    /**
     * Show columnn query
     *
     * Generates a platform-specific query string so that the column names can be fetched
     *
     * @access	public
     * @param	string	the table name
     * @return	string
     */
    function _show_columns($table = '') {
        // Not supported
        return FALSE;
    }
    
    /**
     * Limit string
     *
     * Generates a platform-specific LIMIT clause
     *
     * @access	public
     * @param	string	the sql query string
     * @param	integer	the number of rows to limit the query to
     * @param	integer	the offset value
     * @return	string
     */
    function _limit($sql, $limit, $offset) {
        if ($offset == 0) {
            $offset = '';
        } else {
            $offset .= ", ";
        }
        return $sql . "LIMIT " . $offset . $limit;
    }
    /**
     * From Tables ... contributed/requested by CodeIgniter user: quindo
     *
     * This function implicitly groups FROM tables so there is no confusion
     * about operator precedence in harmony with SQL standards
     *
     * @access  public
     * @param   type
     * @return  type
     */
    function _from_tables($tables) {
        if (!is_array($tables)) {
            $tables = array($tables);
        }
        return implode(', ', $tables);
    }
    /**
     * Set client character set
     * contributed/requested by CodeIgniter user:  jtiai
     *
     * @access    public
     * @param    string
     * @param    string
     * @return    resource
     */
    function db_set_charset($charset, $collation) {
        // TODO - add support if needed
        return TRUE;
    }
    
    /**
     * Close DB Connection
     *
     * @access    public
     * @param    resource
     * @return    void
     */
    function _close($conn_id) {
        // Do nothing since PDO don't have close
    }
    /**
     * List table query    
     *    
     * Generates a platform-specific query string so that the table names can be fetched    
     *    
     * @access      private    
     * @param       boolean    
     * @return      string    
     */
    function _list_tables($prefix_limit = FALSE) {
        $sql = "SELECT name from sqlite_master WHERE type='table'";
        if ($prefix_limit !== FALSE AND $this->dbprefix != '') {
            $sql .= " AND 'name' LIKE '" . $this->dbprefix . "%'";
        }
        return $sql;
    }
}
/**
 * PDO Result Class
 *
 * This class extends the parent result class: CI_DB_result
 *
 * @category	Database
 * @author		Dready
 * @link			http://dready.jexiste.fr/dotclear/
 */
class CI_DB_sqlite3_result extends CI_DB_result {
    var $pdo_results = '';
    var $pdo_index = 0;
    /**
     * Number of rows in the result set
     *
     * @access	public
     * @return	integer
     */
    function num_rows() {
        if (!$this->pdo_results) {
            $this->pdo_results = $this->result_id->fetchAll(PDO::FETCH_ASSOC);
        }
        return sizeof($this->pdo_results);
    }
    
    /**
     * Number of fields in the result set
     *
     * @access	public
     * @return	integer
     */
    function num_fields() {
        if (is_array($this->pdo_results)) {
            return sizeof($this->pdo_results[$this->pdo_index]);
        } else {
            return $this->result_id->columnCount();
        }
    }
    
    /**
     * Result - associative array
     *
     * Returns the result set as an array
     *
     * @access	private
     * @return	array
     */
    function _fetch_assoc() {
        if (is_array($this->pdo_results)) {
            $i = $this->pdo_index;
            $this->pdo_index++;
            if (isset($this->pdo_results[$i]))
                return $this->pdo_results[$i];
            return null;
        }
        return $this->result_id->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Result - object
     *
     * Returns the result set as an object
     *
     * @access	private
     * @return	object
     */
    function _fetch_object() {
        if (is_array($this->pdo_results)) {
            $i = $this->pdo_index;
            $this->pdo_index++;
            if (isset($this->pdo_results[$i])) {
                $back = new stdClass();
                foreach ($this->pdo_results[$i] as $key => $val) {
                    $back->$key = $val;
                }
                return $back;
            }
            return null;
        }
        return $this->result_id->fetch(PDO::FETCH_OBJ);
    }
}
/* End of file sqlite3.php */
