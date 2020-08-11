<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Data Transaction / Microsoft SQL Server Plugin for Microsoft's SQLSRV Driver
 * A Microsoft SQL Server (SQLSRV) plugin to the (DataTrans) class
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 * @access		private
 */
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\Database\Drivers\DataTrans;
use phpOpenFW\Database\DataResult;

//**************************************************************************************
/**
 * dt_sqlsrv Class
 */
//**************************************************************************************
class dt_sqlsrv extends dt_structure
{

    /**
	* Opens a connection to the specified data source based on the data source type
	**/
	//*************************************************************************
	// Make a connection to the given source and store the handle
	//*************************************************************************
	public function open()
	{
		if (!$this->handle) {

            //---------------------------------------------------------------
			// Setup Connection Parameters
            //---------------------------------------------------------------
			$host = $this->server;
			if (!empty($this->port)) { $host .= ',' . $this->port; }
	
            //---------------------------------------------------------------
			// Connection Parameters
            //---------------------------------------------------------------
			$connectionInfo = array();
			if (!empty($this->user)) { $connectionInfo['UID'] = $this->user; }
			if (!empty($this->pass)) { $connectionInfo['PWD'] = $this->pass; }
			if (!empty($this->source)) { $connectionInfo['Database'] = $this->source; }
	
            //---------------------------------------------------------------
			// Attempt Connection
            //---------------------------------------------------------------
			if ($this->persistent) { $this->handle = sqlsrv_connect($host, $connectionInfo); }
	        else { $this->handle = sqlsrv_connect($host, $connectionInfo); }
		}

        if (!$this->handle) {
            $this->connection_error('SQLSRV: There was an error connecting to the database server.');
            $this->check_and_print_error();
            $this->handle = false;
            return false;
        }

        //---------------------------------------------------------------
		// Keep track of the number of connections we create
        //---------------------------------------------------------------
		$this->increment_counters();

        //---------------------------------------------------------------
		// Flag Connection as Open       
        //---------------------------------------------------------------
        $this->conn_open = true;

        //---------------------------------------------------------------
		// Start Transaction?
        //---------------------------------------------------------------
		if (!$this->auto_commit && !$this->trans_started) { $this->start_trans(); }

        return true;
	}
	
	/**
	* Closes a connection to the specified data source based on the data source type
	**/
	//*************************************************************************
	// Close the connection to the data source
	//*************************************************************************
    public function close()
	{
		$this->conn_open = false;
		if (!$this->reuse_connection) {
			if ($this->handle && !$this->data_result) {
				return sqlsrv_close($this->handle);
			}
		}
		return true;
	}

	/**
	* Executes a query based on the data source type
	* @param mixed SQLSRV: SQL Statement
	**/
	//*************************************************************************
	// Execute a query and store the record set
	//*************************************************************************
	public function query($query, $params=false)
	{
		$ret_val = false;

		//----------------------------------------------
		// Check for Open Connection
		//----------------------------------------------
		if (!$this->is_open()) { return false; }
		$this->curr_query = $query;
		if (is_array($params) && $params) { $this->bind_params = $params; }

		//----------------------------------------------
		// Execute Query
		//----------------------------------------------
		if ($params) {
			$this->stmt = sqlsrv_query($this->handle, $query, $params, array('Scrollable' => SQLSRV_CURSOR_STATIC));
		}
		else {
			$this->stmt = sqlsrv_query($this->handle, $query);
		}

		//----------------------------------------------
        // Check for Errors
		//----------------------------------------------
		if (!$this->stmt) {
			if ($this->check_and_print_error()) { return false; }
			$this->print_error('Query execution failed.');
			return false;
		}

		//----------------------------------------------
		// Create Data Result Object if Necessary
		//----------------------------------------------
    	if ($this->stmt && gettype($this->stmt) != 'boolean') {

			//----------------------------------------------
			// Affected Rows
			//----------------------------------------------
	    	$this->affected_rows = sqlsrv_rows_affected($this->stmt);
	    	$ret_val = $this->affected_rows;

			//----------------------------------------------
	    	// Create Data Result Object
			//----------------------------------------------
			$has_rows = sqlsrv_has_rows($this->stmt);
        	$this->data_result = new DataResult($this->stmt, $this->data_src);

			//----------------------------------------------
	        // Last Insert ID
			//----------------------------------------------
	        $this->last_id = null;
        }

		//----------------------------------------------
		// Return Data Result Object if it exists
		//----------------------------------------------
		if ($this->data_result) {
        	$this->num_rows = $this->data_result->num_rows();
        	$this->num_fields = $this->data_result->num_fields();
			$ret_val = $this->data_result;
		}

		//----------------------------------------------
        // Check for Errors
		//----------------------------------------------
        if ($this->check_and_print_error()) { return false; }

        return $ret_val;
	}

	//*************************************************************************
	/**
	* Check and Print Database Error
	**/
	//*************************************************************************
	// Check and Print Database Error
	//*************************************************************************
	private function check_and_print_error()
	{
		if ($error = sqlsrv_errors(SQLSRV_ERR_ERRORS)) {
			$this->print_error($error);
			return true;
		}

		return false;
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
			if (!is_array($error)) {
				return parent::print_error($error);
			}
			else {
				foreach ($error as $tmp) {
					if (!is_array($tmp)) {
						parent::print_error($tmp);
					}
					else {
						parent::print_error($tmp['message'], $tmp['code'], $tmp['SQLSTATE']);
					}
				}
			}
		}

		return true;
	}

	//*************************************************************************
	/**
	* Start a new Database Transaction
	**/
	//*************************************************************************
	protected function _start_trans()
	{
		return (sqlsrv_begin_transaction($this->handle)) ? (true) : (false);
	}

    //*************************************************************************
	/**
	* Internal Auto Commit Function
	**/
	//*************************************************************************
    protected function _auto_commit($curr, $new)
    {
		if (!$curr && $new) { $this->commit(false); }
		if (!$new && !$this->trans_started) { $this->start_trans(); }
    	else { $this->trans_started = false; }
		return true;
    }

	//*************************************************************************
	/**
	* Internal Commit Function
	**/
	//*************************************************************************
	protected function _commit() { return sqlsrv_commit($this->handle); }

	//*************************************************************************
	/**
	* Internal Rollback Function
	**/
	//*************************************************************************
	protected function _rollback() { return sqlsrv_rollback($this->handle); }

    //*************************************************************************
	/**
	* Prepare Function
	* @param string SQL Statement
	**/
	//*************************************************************************
    public function prepare($stmt=false)
    {
    	$this->curr_query = $stmt;
    	return true;
    }

    //*************************************************************************
	/**
	* Hidden Prepare Function to be used be execute
	* @param string SQL Statement
	* @param array An Array of Bind Parameters
	* @param bool Lazy mode: If true, references of values will be created for you
	**/
	//*************************************************************************
    private function _prepare($stmt=false)
    {
    	if (!$this->handle) { return false; }

		//----------------------------------------------
		// Pull Arguments
		//----------------------------------------------
    	$arg_list = func_get_args();
    	$bind_params = (isset($arg_list[1]) && $arg_list[1]) ? ($arg_list[1]) : (false);
		if (!is_array($bind_params) || !$bind_params) { return false; }
    	$lazy = (isset($arg_list[2]) && $arg_list[2]) ? (true) : (false);

		//----------------------------------------------
		// Save Query / Bind Parameters
		//----------------------------------------------
    	$this->curr_query = $stmt;
    	$this->bind_params = $bind_params;

		//----------------------------------------------
		// Are you feeling a bit lazy? Let's just make those values into references for you…
		//----------------------------------------------
		if ($bind_params && ($lazy || $this->get_opt('make_bind_params_refs'))) {
			$tmp_bind_params = $bind_params;
			for ($i = 0; $i < count($bind_params); $i++) {
				$bind_params[$i] =& $tmp_bind_params[$i];
			}
		}

    	if ($this->stmt && !$this->data_result) {
    		sqlsrv_free_stmt($this->stmt);
    	}
    	$this->stmt = sqlsrv_prepare($this->handle, $stmt, $bind_params, array('Scrollable' => SQLSRV_CURSOR_STATIC));

		//----------------------------------------------
        // Check for Errors
		//----------------------------------------------
        if ($this->check_and_print_error()) { return false; }

		return true;
    }

    //*************************************************************************
	/**
	* Execute Function
	* @param string SQL Statement
	**/
	//*************************************************************************
    public function execute($bind_params=false)
    {
    	$ret_val = false;

    	$arg_list = func_get_args();
    	$lazy = (isset($arg_list[1]) && $arg_list[1]) ? (true) : (false);

		//----------------------------------------------
		// Prepare SQL Statement
		//----------------------------------------------
		$prepare_status = $this->_prepare($this->curr_query, $bind_params, $lazy);
		if (!$prepare_status) {
			if ($this->check_and_print_error()) { return false; }
			$this->print_error('Query prepare failed.');
			return false;
		}
    	if (!$this->stmt) { return false; }

		//----------------------------------------------
		// Execute Query
		//----------------------------------------------
		$exec_status = @sqlsrv_execute($this->stmt);
		if (!$exec_status) {
			if ($this->check_and_print_error()) { return false; }
			$this->print_error('Query execution failed.');
			return false;
		}

		//----------------------------------------------
		// Create Data Result Object if Necessary
		//----------------------------------------------
    	if ($this->stmt && gettype($this->stmt) != 'boolean') {

			//----------------------------------------------
			// Affected Rows
			//----------------------------------------------
	    	$this->affected_rows = sqlsrv_rows_affected($this->stmt);
	    	$ret_val = $this->affected_rows;

			//----------------------------------------------
	    	// Create Data Result Object
			//----------------------------------------------
			$has_rows = sqlsrv_has_rows($this->stmt);
        	$this->data_result = new DataResult($this->stmt, $this->data_src);

			//----------------------------------------------
	        // Last Insert ID
			//----------------------------------------------
	        $this->last_id = null;
        }

		//----------------------------------------------
		// Return Data Result Object if it exists
		//----------------------------------------------
		if ($this->data_result) {
        	$this->num_rows = $this->data_result->num_rows();
        	$this->num_fields = $this->data_result->num_fields();
			$ret_val = $this->data_result;
		}

		//----------------------------------------------
        // Check for Errors
		//----------------------------------------------
        if ($this->check_and_print_error()) { return false; }

		return $ret_val;
    }

	//*************************************************************************
	/**
	* Shutdown function
	**/
	//*************************************************************************
	public function shutdown()
	{
		if ($this->stmt && !$this->data_result) {
			return sqlsrv_free_stmt($this->stmt);
		}
	}

	//*************************************************************************
	/**
	* Get Combined Query function
	**/
	//*************************************************************************
	public function get_combined_query($query, $bind_params)
	{
		if (!is_array($bind_params)) { return false; }
		$num_params = count($bind_params);
		if ($num_params > 0) {
			foreach ($bind_params as $key => $param) {
				$key = '?';
				$param = "'{$param}'";
				$pos = strpos($query, $key);
				if ($pos === false) { continue; }
				$query = substr_replace($query, $param, $pos, strlen($key));
			}
		}
		return $query;
	}
}

