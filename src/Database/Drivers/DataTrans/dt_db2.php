<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Data Transaction / IBM DB2 Plugin
 * A IBM DB2 plugin to the (DataTrans) class
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
 * dt_db2 Class
 */
//**************************************************************************************
class dt_db2 extends dt_structure
{
    
    /**
	* Opens a connection to the specified data source based on the data source type
	**/
	//*************************************************************************
	// Make a connection to the given source and store the handle
	//*************************************************************************
	public function open()
	{
		if (!empty($GLOBALS['phpopenfw_db2_conn']) && is_resource($GLOBALS['phpopenfw_db2_conn']) && !$this->handle) {
			$this->handle = $GLOBALS['phpopenfw_db2_conn'];
		}
		else if (!$this->handle) {

			$conn_str = "
				DRIVER={IBM DB2 ODBC DRIVER};
				DATABASE={$this->source};
				HOSTNAME={$this->server};
				PORT={$this->port};
				PROTOCOL=TCPIP;
				UID={$this->user};
				PWD={$this->pass};
			";

			// Connection String
			if ($this->conn_str !== false) {
				$db_params = (string)$this->conn_str;
				if ($this->persistent) {
					$this->handle = (!empty($this->options)) ? (
						db2_pconnect($db_params, '', '', $this->options)
					) : (
						db2_pconnect($db_params, '', '')
					);
					
				}
				else {
					$this->handle = (!empty($this->options)) ? (
						db2_connect($db_params, '', '', $this->options)
					) : (
						db2_connect($db_params, '', '')
					);
				}
			}
			else {
				if ($this->persistent) {
					$this->handle = (!empty($this->options)) ? (
						db2_pconnect($this->source, $this->user, $this->pass, $this->options)
					) : (
						db2_pconnect($this->source, $this->user, $this->pass)
					);
				}
				else {
					$this->handle = (!empty($this->options)) ? (
						db2_connect($this->source, $this->user, $this->pass, $this->options)
					) : (
						db2_connect($this->source, $this->user, $this->pass)
					);
				}
			}
	
	        if (db2_conn_errormsg()) {
	            $this->connection_error(db2_conn_errormsg());
	            $this->handle = false;
	            return false;
	        }

			// Keep track of the number of connections we create
			$this->increment_counters();
		}

		// Flag Connection as Open 
        $this->conn_open = true;

		// Start Transaction and Turn off Auto Commit?
		if (!$this->auto_commit && !$this->trans_started) {
			db2_autocommit($this->handle, DB2_AUTOCOMMIT_OFF);
			$this->start_trans();
		}
		
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
				return db2_close($this->handle);
			}
		}
		return true;
	}
	
	/**
	* Executes a query based on the data source type
	* @param mixed IBM DB2: SQL Statement
	**/
	//*************************************************************************
	// Execute a query and store the record set
	//*************************************************************************
	public function query($query)
	{
		$ret_val = false;

		//----------------------------------------------
		// Check for Open Connection
		//----------------------------------------------
		if (!$this->is_open()) { return false; }
		$this->curr_query = $query;

		//----------------------------------------------
		// If Statement exists, free it.
		//----------------------------------------------
		if ($this->stmt && !$this->data_result) {
			db2_free_stmt($this->stmt);
		}

		//----------------------------------------------
		// Execute Query
		//----------------------------------------------
        $this->stmt = db2_exec($this->handle, $query);

		//----------------------------------------------
        // Check for errors
		//----------------------------------------------
    	if (!$this->stmt) {
	        if ($this->check_and_print_stmt_error($this->stmt)) { return false; }

    		$this->gen_error('Query failed.');
    		return false;
    	}

		//----------------------------------------------
		// Create Data Result Object if Necessary
		//----------------------------------------------
    	if ($this->stmt && gettype($this->stmt) != 'boolean') {

			//----------------------------------------------
			// Affected Rows
			//----------------------------------------------
	    	$this->affected_rows = db2_num_rows($this->stmt);
	    	$ret_val = $this->affected_rows;

			//----------------------------------------------
	    	// Create Data Result Object
			//----------------------------------------------
        	$this->data_result = new DataResult($this->stmt, $this->data_src);

			//----------------------------------------------
	        // Last Insert ID
			//----------------------------------------------
	        $this->last_id = db2_last_insert_id($this->handle);
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
		// Check for error
		//----------------------------------------------
		if ($this->check_and_print_stmt_error($this->stmt)) { return false; }

		return $ret_val;
	}

	//*************************************************************************
	/**
	* Check and Print Database Error
	**/
	//*************************************************************************
	// Check and Print Database Error
	//*************************************************************************
	private function check_and_print_stmt_error($stmt=false)
	{
		$skip_errnos = array('02000');
		if ($stmt) {
			if ($error = db2_stmt_errormsg($stmt)) {
				$errno = db2_stmt_error($stmt);
				if (in_array($errno, $skip_errnos)) { return false; }
				$this->print_error($error, $errno);
				return true;
			}
			return false;
		}
		else if ($error = db2_stmt_errormsg()) {
			$errno = db2_stmt_error();
			if (in_array($errno, $skip_errnos)) { return false; }
			$this->print_error($error, $errno);
			return true;
		}

		return false;
	}

	//*************************************************************************
	/**
	* Start a new Database Transaction
	**/
	//*************************************************************************
	protected function _start_trans()
	{
		return true;
	}

    //*************************************************************************
	/**
	* Internal Auto Commit Function
	**/
	//*************************************************************************
    protected function _auto_commit($curr, $new)
    {
    	if (!$curr && $new) { $this->commit(false); }
		if (!$new) { $status = db2_autocommit($this->handle, DB2_AUTOCOMMIT_OFF); }
    	else { $status = db2_autocommit($this->handle, DB2_AUTOCOMMIT_ON); }
    	if ($status) {
	    	if (!$new && !$this->trans_started) { $this->start_trans(); }
	    	else { $this->trans_started = false; }
	    	return true;
		}

		return false;
    }

	//*************************************************************************
	/**
	* Internal Commit Function
	**/
	//*************************************************************************
	protected function _commit() { return db2_commit($this->handle); }

	//*************************************************************************
	/**
	* Internal Rollback Function
	**/
	//*************************************************************************
	protected function _rollback() { return db2_rollback($this->handle); }

    //*************************************************************************
	/**
	* Prepare Function
	* @param string SQL Statement
	**/
	//*************************************************************************
    public function prepare($stmt=false)
    {
    	if ($this->stmt && !$this->data_result) {
    		db2_free_stmt($this->stmt);
    	}
    	$this->stmt = db2_prepare($this->handle, $stmt);
    	$this->curr_query = $stmt;

        // Error Reporting
        if ($this->check_and_print_stmt_error()) { return false; }

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

		//----------------------------------------------
		// Check Statement Resource
		//----------------------------------------------
    	if (!$this->stmt) {
    		$this->gen_error('Invalid statement resource.');
    		return false;
    	}

		//----------------------------------------------
		// Check Bind Parameters
		//----------------------------------------------
    	if (!is_array($bind_params)) {
    		$this->gen_error('Binding parameters must be passed as an array.');
    		return false;
    	}
    	$this->bind_params = $bind_params;

		//----------------------------------------------
		// Execute Query
		//----------------------------------------------
		$exec_status = @db2_execute($this->stmt, $bind_params);

		//----------------------------------------------
        // Check for Errors
		//----------------------------------------------
    	if (!$exec_status) {
	        if ($this->check_and_print_stmt_error($this->stmt)) { return false; }

    		$this->gen_error('Prepared query execution failed.');
    		return false;
    	}

		//----------------------------------------------
		// Create Data Result Object if Necessary
		//----------------------------------------------
    	if ($this->stmt && gettype($this->stmt) != 'boolean') {

			//----------------------------------------------
			// Affected Rows
			//----------------------------------------------
	    	$this->affected_rows = db2_num_rows($this->stmt);
	    	$ret_val = $this->affected_rows;

			//----------------------------------------------
	    	// Create Data Result Object
			//----------------------------------------------
        	$this->data_result = new DataResult($this->stmt, $this->data_src);

			//----------------------------------------------
	        // Last Insert ID
			//----------------------------------------------
	        $this->last_id = db2_last_insert_id($this->handle);
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
		if ($this->check_and_print_stmt_error($this->stmt)) { return false; }

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
			return db2_free_stmt($this->stmt);
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

