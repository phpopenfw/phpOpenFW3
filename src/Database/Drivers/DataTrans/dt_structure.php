<?php
//*****************************************************************************
//*****************************************************************************
/**
 * DT Structure Class
 * Abstract Data Transaction Structure Class
 *
 * @package         phpopenfw/phpopenfw3
 * @author          Christian J. Clark
 * @copyright       Copyright (c) Christian J. Clark
 * @website         https://phpopenfw.org
 * @license         https://mit-license.org
 * @access          private
 */
//*****************************************************************************
//*****************************************************************************

namespace phpOpenFW\Database\Drivers\DataTrans;

//*****************************************************************************
/**
 * dt_structure Class
 */
//*****************************************************************************
abstract class dt_structure
{

    //*************************************************************************
    // Class variables
    //*************************************************************************
    /**
     * @var object Data source object
     **/
    protected $ds_obj;

    /**
     * @var string Data source type (mysql, mysqli, pgsql, oracle, mssql, db2, sqlsrv, sqlite)
     **/
    protected $data_type;

    /**
     * @var string Server address to use when connecting to the data source
     **/
    protected $server;

    /**
     * @var string Server port to use when connecting to the data source
     **/
    protected $port;

    /**
     * @var string Database or directory to use when connecting to the data source
     **/
    protected $source;

    /**
     * @var string Database instance to use when connecting to the data source
     **/
    protected $instance;

    /**
     * @var string User to use when connecting to the data source
     **/
    protected $user;

    /**
     * @var string Password to use when connecting to the data source
     **/
    protected $pass;

    /**
     * @var string Database specific connection string
     **/
    protected $conn_str;

    /**
     * @var string Database specific options
     **/
    protected $options;

    /**
     * @var resource Connection handle of the current data source connection
     **/
    protected $handle;

    /**
     * @var resource Resource ID of the data source record set
     **/
    protected $rsrc_id;

    /**
     * @var array The record set
     **/
    protected $result;

    /**
     * @var integer Number of rows in the record set returned
     **/
    protected $num_rows;

    /**
     * @var integer Number of rows affected by query
     **/
    protected $affected_rows;

    /**
     * @var integer Number of fields in the record set returned
     **/
    protected $num_fields;

    /**
     * @var integer ID of the last inserted row for the current connection
     **/
    protected $last_id;

    /**
     * @var bool Connection Open (true or false)
     **/
    protected $conn_open;

    /**
     * @var bool Connection is Persistent (true or false)
     **/
    protected $persistent;

    /**
     * @var bool Reuse one Connection (true or false)
     **/
    protected $reuse_connection;

    /**
     * @var string Display Data Type
     **/
    protected $disp_dt;

    /**
     * @var string Auto commit flag
     **/
    protected $auto_commit;

    /**
     * @var mixed A prepared SQL Statement handle or FALSE when invalid or not set.
     **/
    protected $stmt;

    /**
     * @var bool Has a transaction been started?
     **/
    protected $trans_started;

    /**
     * @var string Current SQL Query
     **/
    protected $curr_query;

    /**
     * @var array Current SQL Bind Parameters
     **/
    protected $bind_params;

    /**
     * @var array Options that affect the current instantiated object
     **/
    protected $inst_opts;

    /**
     * @var array Is this the first use of this data source?
     **/
    protected $first_use;

    /**
     * @var object The Data Result Object for the last run query with this object.
     **/
    protected $data_result;

    /**
     * @var bool A flag to determine if the results from the query have been saved/cached.
     **/
    protected $results_set;

    /**
     * @var mixed String containing the character set i.e. utf8 or false if not using one
     **/
    protected $charset;

    /**
     * @var Array Global data source parameters (does not live in session)
     **/
    protected $global_ds_params;

    /**
     * Constructor function
     *
     * Initializes data transaction, setting all necessary variables from specified data source
     *
     * @param string The name of data source to use as specified in config.inc.php
     **/
    //*************************************************************************
    // Constructor function
    //*************************************************************************
    public function __construct($data_src)
    {
        //---------------------------------------------------------------------
        // Data Source Object Passed
        //---------------------------------------------------------------------
        $this->ds_obj = \phpOpenFW\Core\DataSources::GetOneOrDefault($data_src);

        //---------------------------------------------------------------------
        // Stored Data Source Specific Parameters
        // First Use
        //---------------------------------------------------------------------
        $global_id = (!empty($this->ds_obj->index)) ? ($this->ds_obj->index) : ($this->ds_obj->internal_id);
        if (!isset($GLOBALS['PHPOPENFW_DATASOURCE_PARAMS'][$global_id])) {
            $GLOBALS['PHPOPENFW_DATASOURCE_PARAMS'][$global_id] = [];
            $this->first_use = true;
        }
        else {
            $this->first_use = false;
        }
        $this->global_ds_params =& $GLOBALS['PHPOPENFW_DATASOURCE_PARAMS'][$global_id];

        //---------------------------------------------------------------------
        // Set Data Source Variables
        //---------------------------------------------------------------------
        $this->data_type = $this->ds_obj->type;
        $this->server = $this->ds_obj->server;
        $this->port = $this->ds_obj->port;
        $this->instance = $this->ds_obj->instance;
        $this->source = $this->ds_obj->source;
        $this->user = $this->ds_obj->user;
        $this->pass = $this->ds_obj->pass;
        $this->conn_str = $this->ds_obj->conn_str;
        $this->options = $this->ds_obj->options;
        $this->charset = $this->ds_obj->charset;
        $this->handle = $this->ds_obj->handle;
        $this->result = array();
        $this->results_set = false;
        $this->num_rows = false;
        $this->num_fields = false;
        $this->affected_rows = false;
        $this->last_id = NULL;
        $this->conn_open = false;
        $this->disp_dt = '[' . strtoupper('dt_' . $this->data_type) . ']';
        $this->stmt = false;
        $this->curr_query = false;
        $this->bind_params = false;
        $this->inst_opts = array();
        $this->data_result = false;

        //---------------------------------------------------------------------
        // Connection Persistent?
        //---------------------------------------------------------------------
        if (isset($this->ds_obj->persistent)) {
            $p = $this->ds_obj->persistent;
            $this->persistent = (empty($p) || strtolower($p) == 'no') ? (false) : (true);
        }
        else { $this->persistent = false; }

        //---------------------------------------------------------------------
        // Reuse One Connetion?
        //---------------------------------------------------------------------
        if (in_array($this->data_type, array('db2', 'oracle', 'sqlsrv'))) {
            $this->reuse_connection = true;
        }
        else if (isset($this->ds_obj->reuse_connection)) {
            $p = $this->ds_obj->reuse_connection;
            $this->reuse_connection = (empty($p) || strtolower($p) == 'no') ? (false) : (true);
        }
        else if (in_array($this->data_type, array('pgsql', 'mssql', 'mysql', 'mysqli', 'sqlite'))) {
            $this->reuse_connection = true;
        }
        else {
            $this->reuse_connection = false;
        }

        //---------------------------------------------------------------------
        // Database Handle
        //---------------------------------------------------------------------
        if ($this->reuse_connection) {
            if (empty($this->global_ds_params['handle'])) {
                $this->global_ds_params['handle'] = false;
            }
            $this->handle =& $this->global_ds_params['handle'];
        }

        //---------------------------------------------------------------------
        // Auto Commit
        //---------------------------------------------------------------------
        if ($this->reuse_connection) {
            if ($this->first_use) {
                $this->global_ds_params['auto_commit'] = true;
            }
            $this->auto_commit =& $this->global_ds_params['auto_commit'];
        }
        else {
            $this->auto_commit = true;
        }

        //---------------------------------------------------------------------
        // Transaction Started?
        //---------------------------------------------------------------------
        if ($this->reuse_connection) {
            if ($this->first_use) {
                $this->global_ds_params['trans_started'] = false;
            }
            $this->trans_started =& $this->global_ds_params['trans_started'];
        }
        else {
            $this->trans_started = false;
        }

        //---------------------------------------------------------------------
        // Character Set
        //---------------------------------------------------------------------
        if ($this->charset) {
            $this->set_opt('charset', $this->charset);
        }

        //---------------------------------------------------------------------
        // Open Connection
        //---------------------------------------------------------------------
        $this->open();
    }

    //*************************************************************************
    /**
     * Destructor Function
     **/
    //*************************************************************************
    public function __destruct()
    {
        if ($this->is_open() && !$this->persistent) { $this->close(); }
    }

    //*************************************************************************
    /**
     * Display Error Function
     **/
    //*************************************************************************
    private function display_error($function, $msg)
    {
        $class = __CLASS__;
        trigger_error("Error: [{$class}]::{$function}(): {$msg}");
    }

    //*************************************************************************
    /**
    * Methods required to be implemented
    * open - opens the connection
    * close - closes the connection and clean-up
    **/
    //*************************************************************************
    abstract public function open();
    abstract public function close();

    //*************************************************************************
    /**
     * Return the number rows in the current record set
     * @return integer The number rows in the current record set
     **/
    //*************************************************************************
    public function num_rows() { return $this->num_rows; }

    //*************************************************************************
    /**
     * Return the number fields in the current record set
     * @return integer The number fields in the current record set
     **/
    //*************************************************************************
    public function num_fields() { return $this->num_fields; }

    //*************************************************************************
    /**
     * Return the number of affected rows for the current query
     * @return integer The number of affected rows for the current query
     **/
    //*************************************************************************
    public function affected_rows() { return $this->affected_rows; }

    /**
     * Return ID of the last inserted row for the current session
     * @return integer ID of the last inserted row for the current session
     **/
    //*************************************************************************
    // Return the ID of the last inserted row
    //*************************************************************************
    public function last_insert_id() { return $this->last_id; }

    //*************************************************************************
    /**
     * Clear Result Function
     **/
    //*************************************************************************
    public function clear_result()
    {
        $this->result = array();
        $this->results_set = false;
        $this->num_rows = false;
        $this->num_fields = false;
        $this->affected_rows = false;
        $this->data_result = false;
    }

    //*************************************************************************
    /**
     * Clear Last Insert ID Function
     **/
    //*************************************************************************
    public function clear_last_insert_id() { $this->last_id = NULL; }

    //*************************************************************************
    /**
     * Get User Function
     **/
    //*************************************************************************
    public function get_user() { return $this->user; }

    //*************************************************************************
    /**
     * Get Password Function
     **/
    //*************************************************************************
    public function get_pass() { return $this->pass; }

    //*************************************************************************
    /**
     * Get Connection Handle
     **/
    //*************************************************************************
    public function get_conn_handle() { return $this->handle; }

    //*************************************************************************
    /**
     * Get Query Function
     **/
    //*************************************************************************
    public function get_query()
    {
        $query = $this->curr_query;
        $bind_params = $this->bind_params;
        if ($bind_params) {
            $new_query = array($query, $bind_params);
            if (method_exists($this, 'get_combined_query')) {
                $new_query[] = $this->get_combined_query($query, $bind_params);
            }
            return $new_query;
        }
        return $query;
    }

    //*************************************************************************
    /**
     * Get Option Function
     * @param string The key of the value to retrieve
     **/
    //*************************************************************************
    public function get_opt($opt)
    {
        $opt = strtoupper((string)$opt);
        if ($opt == '') { return false; }
        if (isset($this->inst_opts[$opt])) { return $this->inst_opts[$opt]; }
        else { return false; }
    }

    //*************************************************************************
    /**
     * Set Option Function
     * @param string The key of the value to set
     * @param string The value to set
     **/
    //*************************************************************************
    public function set_opt($opt, $val=false)
    {
        $opt = strtoupper((string)$opt);
        if ($opt == '') { return false; }
        $this->inst_opts[$opt] = $val;
        return true;
    }

    //*************************************************************************
    /**
     * Check if connection is open Function
     **/
    //*************************************************************************
    public function is_open() { return $this->conn_open; }

    //*************************************************************************
    /**
     * Connection Error Function
     **/
    //*************************************************************************
    protected function connection_error($error=false, $errno=false)
    {
        if ($error) {
            $msg = "{$this->disp_dt} Connection Error: {$error}";
            if ($errno) { $msg .= ", Error Code: {$errno}"; }
            trigger_error($msg);
        }
    }

    //*************************************************************************
    /**
     * Generic Error Function
     **/
    //*************************************************************************
    protected function gen_error($error=false)
    {
        if ($error) {
            trigger_error("{$this->disp_dt} Error: {$error}");
        }
    }

    //*************************************************************************
    /**
     * Print Database Error
     **/
    //*************************************************************************
    // Print Database Error
    //*************************************************************************
    protected function print_error($error=false, $errno=false, $sqlstate=false)
    {
        if (!$error) { return false; }
        else {
            $msg_break = "\n";
            $error_msg = $error;
            if ($errno) {
                $error_msg = "[Code] =>  {$errno} [Message] => {$error}";
            }
            if ($sqlstate) {
                $error_msg .= " [SQLSTATE] => {$sqlstate}";
            }
            $this->gen_error($error_msg);
            return true;
        }
    }

    //*************************************************************************
    /**
     * Cache Results from Current Query Result
     **/
    //*************************************************************************
    protected function cache_results()
    {
        if (gettype($this->data_result) == 'object' && get_class($this->data_result) == 'phpOpenFW\Database\DataResult') {
            if (!$this->results_set) {
                $this->result = $this->data_result->fetch_all_rows();
                if (!$this->result && !is_array($this->result)) { $this->result = array(); }
                $this->results_set = true;
                return 1;
            }
            return 2;
        }

        $this->results_set = true;
        return 0;
    }

    /**
     * Return the current record set in the form of an associative array
     * @return array current record set in the form of an associate array
     **/
    //*************************************************************************
    // Extract the record set to local variables
    //*************************************************************************
    public function assoc_result()
    {
        $this->cache_results();
        return $this->result;
    }

    /**
     * Return an abbreviated form of the current record set in the form of an associative array
     * @param string field to be used as the "key" in the associative array
     * @param string field to be used as the "value" in the associative array
     * @return array abbreviated form of the current record set in the form of an associative array
     **/
    //*************************************************************************
    // Extract an abbreviated record set to a "key" => "value" array
    //*************************************************************************
    public function key_val_result($key, $value)
    {
        //----------------------------------------------------------
           // Initialize Result Array
        //----------------------------------------------------------
        $assoc_result = array();

        //----------------------------------------------------------
        // Cache Record Results
        //----------------------------------------------------------
        $this->cache_results();

        if ($this->result) {
            $row_num = 0;
            foreach ($this->result as $row) {
                if ($key == '') {
                    if (!array_key_exists($value, $row)) {
                        trigger_error("{$this->disp_dt} key_val_result() :: Index \"{$value}\" does not exist!!");
                        return false;
                    }
                    $assoc_result[$row_num] = $row[$value];
                }
                else if (array_key_exists($key, $row) && array_key_exists($value, $row)) {
                    $assoc_result[$row[$key]] = $row[$value];
                }
                else {
                    trigger_error("{$this->disp_dt} key_val_result() :: Index \"{$key}\" or Index \"{$value}\" does not exist!!");
                    return false;
                }
                $row_num++;
            }
        }

        //----------------------------------------------------------
        // Return Result Array
        //----------------------------------------------------------
        return $assoc_result;
    }

    //*************************************************************************
    /**
     * Return the current record set in the form of an associative array with $key as the key for each record
     * @param string The index of the field to be used as the "key" for each record
     * @return array Return the current record set in the form of an associative array with $key as the key for each record
     **/
    //*************************************************************************
    public function key_assoc_result($key)
    {
        //----------------------------------------------------------
        // Initialize Result Array
        //----------------------------------------------------------
        $assoc_result = array();

        //----------------------------------------------------------
        // Cache Record Results
        //----------------------------------------------------------
        $this->cache_results();

        if ($this->result) {
            foreach ($this->result as $row) {
                if (array_key_exists($key, $row)) {
                    $assoc_result[$row[$key]] = $row;
                }
                else{
                    trigger_error("{$this->disp_dt} key_assoc_result() :: Index \"{$key}\" does not exist!!");
                    return false;
                }
            }
        }

        //----------------------------------------------------------
        // Return Result Array
        //----------------------------------------------------------
        return $assoc_result;
    }

    //*************************************************************************
    /**
     * Shutdown function
     **/
    //*************************************************************************
    public function shutdown() { return true; }

    //*************************************************************************
    /**
     * Has a database transaction been started?
     **/
    //*************************************************************************
    public function is_trans_started() { return $this->trans_started; }

    //*************************************************************************
    /**
     * Start a new Database Transaction
     **/
    //*************************************************************************
    public function start_trans()
    {
        if (method_exists($this, '_start_trans')) {
            $this->trans_started = $this->_start_trans();
            return $this->trans_started;
        }
        else { return $this->not_available(__FUNCTION__); }
    }

    //*************************************************************************
    /**
     * Auto Commit Enable / Disable Function
     * param bool True (default) = auto commit, False = no auto commit
     **/
    //*************************************************************************
    public function auto_commit($auto_commit=true)
    {
        if (method_exists($this, '_auto_commit')) {
            $curr = $this->auto_commit;
            $ac_status = $this->_auto_commit($curr, (bool)$auto_commit);
            if ($ac_status) {
                $this->auto_commit = (bool)$auto_commit;
                return true;
            }
            else {
                $ac_disp = ((bool)$auto_commit) ? ('on') : ('off');
                $this->gen_error("Could not change auto commit to {$ac_disp}.");
                return false;
            }
        }
        else { return $this->not_available(__FUNCTION__); }
    }

    //*************************************************************************
    /**
     * Commit current Outstanding Statements / Transaction(s)
     * param bool True (default) = Start new transaction after commit, False = Do not start new transaction after commit
     **/
    //*************************************************************************
    public function commit($start_new=true)
    {
        if (method_exists($this, '_commit')) {
            if ($this->trans_started) {
                $status = $this->_commit();
                if (!$status) {
                    $this->gen_error("Failed to commit transaction.");
                    return false;
                }
                if ($start_new) { $this->start_trans(); }
                else { $this->trans_started = false; }
            }
            else {
                $this->gen_error("No transaction has been started.");
                return false;
            }
        }
        else { return $this->not_available(__FUNCTION__); }
    }

    //*************************************************************************
    /**
     * Rollback current Outstanding Statements / Transaction(s)
     * param bool True (default) = Start new transaction after rollback, False = Do not start new transaction after rollback
     **/
    //*************************************************************************
    public function rollback($start_new=true)
    {
        if (method_exists($this, '_rollback')) {
            if ($this->trans_started) {
                $status = $this->_rollback();
                if (!$status) {
                    $this->gen_error("Failed to rollback transaction.");
                    return false;
                }
                if ($start_new) { $this->start_trans(); }
                else { $this->trans_started = false; }
            }
            else {
                $this->gen_error("No transaction has been started.");
                return false;
            }
        }
        else { return $this->not_available(__FUNCTION__); }
    }

    //*************************************************************************
    /**
     * Prepare Function
     **/
    //*************************************************************************
    public function prepare($stmt=false) { return $this->not_available(__FUNCTION__); }

    //*************************************************************************
    /**
     * Execute Function
     **/
    //*************************************************************************
    public function execute($bind_param=false) { return $this->not_available(__FUNCTION__); }

    //*************************************************************************
    /**
     * Function to display a "Not Available" Message
     **/
    //*************************************************************************
    protected function not_available($function)
    {
        trigger_error("The function {$function}() is not available with the {$this->disp_dt} class.", E_USER_WARNING);
        return false;
    }

    //*************************************************************************
    /**
     * Increment Database Connections Counters
     **/
    //*************************************************************************
    protected function increment_counters()
    {
        //---------------------------------------------------------------------
        // Total connections for a data source type
        //---------------------------------------------------------------------
        $type = strtoupper($this->data_type);
        $tmp_index = "PHPOPENFW_{$type}_CONNS";
        if (!isset($GLOBALS[$tmp_index])) {
            $GLOBALS[$tmp_index] = 0;
        }
        $GLOBALS[$tmp_index]++;

        //---------------------------------------------------------------------
        // Data source specific connection count
        //---------------------------------------------------------------------
        if (!isset($this->global_ds_params['connections'])) {
            $this->global_ds_params['connections'] = 0;
        }
        $this->global_ds_params['connections']++;
    }

}

