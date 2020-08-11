<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Data Transaction / MySQL Improved (mysqli) Plugin
 * A MySQLi plugin to the (DataTrans) class
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
 * dt_mysqli Class
 */
//**************************************************************************************
class dt_mysqli extends dt_structure
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
			if (!$this->port) { $this->port = 3306; }
			if ($this->persistent) { $this->server = 'p:' . $this->server; }
	        $this->handle = @new \mysqli($this->server, $this->user, $this->pass, $this->source, $this->port);

	        if ($this->handle->connect_errno) {
	        	$this->connection_error($this->handle->connect_error, $this->handle->connect_errno);
	        	$this->handle = false;
				return false;
	        }

			//------------------------------------------------------
			// Keep track of the number of connections we create
			//------------------------------------------------------
			$this->increment_counters();
		}

		//----------------------------------------------
		// Flag Connection as Open
		//----------------------------------------------
        $this->conn_open = true;

		//----------------------------------------------
		// Start Transaction and Turn off Auto Commit?
		//----------------------------------------------
        if (!$this->auto_commit && !$this->trans_started) {
        	$this->handle->autocommit($this->auto_commit);
        	$this->start_trans();
        }

		//----------------------------------------------
		// Set the character Set If needed
		//----------------------------------------------
		if ($this->charset) {
			//$this->handle->set_charset($this->charset);
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
			if ($this->conn_open && !$this->data_result) {
				return $this->handle->close();
			}
		}
		return true;
	}

	/**
	* Executes a query based on the data source type
	* @param mixed MySQL: SQL Statement
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
		// Execute Query
		//----------------------------------------------
        $this->rsrc_id = $this->handle->query($query);

		//----------------------------------------------
		// Affected Rows
		//----------------------------------------------
    	$this->affected_rows = $this->handle->affected_rows;
    	$ret_val = $this->affected_rows;

		//----------------------------------------------
		// Create Data Result Object if Necessary
		//----------------------------------------------
    	if ($this->rsrc_id && gettype($this->rsrc_id) != 'boolean') {
        	$this->data_result = new DataResult($this->rsrc_id, $this->data_src);
        }

		//----------------------------------------------
        // Last Insert ID
		//----------------------------------------------
        $this->last_id = $this->handle->insert_id;

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
		if ($this->check_and_print_error($this->handle)) { return false; }

		return $ret_val;
	}

	//*************************************************************************
	/**
	* Check and Print Database Error
	**/
	//*************************************************************************
	// Check and Print Database Error
	//*************************************************************************
	private function check_and_print_error($rsrc=false)
	{
		if (!$rsrc) { return false; }
		else if ($rsrc->errno) {
			$this->print_error($rsrc->error, $rsrc->errno);
			return true;
		}

		return false;
	}

	//*************************************************************************
	/**
	* Check and Print Database Statement Error
	**/
	//*************************************************************************
	// Check and Print Database Statement Error
	//*************************************************************************
	private function check_and_print_stmt_error($stmt=false)
	{
		if (!$stmt) { return false; }
		else if ($stmt->errno) {
			$this->print_error($stmt->error, $stmt->errno);
			return true;
		}

		return false;
	}

	//*************************************************************************
	/**
	* Start a new Database Transaction
	**/
	//*************************************************************************
	protected function _start_trans() { return $this->handle->begin_transaction(); }

    //*************************************************************************
	/**
	* Internal Auto Commit Function
	**/
	//*************************************************************************
    protected function _auto_commit($curr, $new)
    {
    	$ac_status = $this->handle->autocommit($this->auto_commit);
    	if ($ac_status) {
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
	protected function _commit() { return $this->handle->commit(); }

	//*************************************************************************
	/**
	* Internal Rollback Function
	**/
	//*************************************************************************
	protected function _rollback() { return $this->handle->rollback(); }

    //*************************************************************************
	/**
	* Prepare Function
	* @param string SQL Statement
	**/
	//*************************************************************************
    public function prepare($stmt=false)
    {
    	$this->stmt = $this->handle->prepare($stmt);
    	$this->curr_query = $stmt;

		//----------------------------------------------
        // Check for error
		//----------------------------------------------
        if ($this->check_and_print_error($this->handle)) { return false; }

		return true;
    }

    //*************************************************************************
	/**
	* Execute Function
	* @param string SQL Statement
	* @param bool Lazy mode: If true, references of values will be created for you
	**/
	//*************************************************************************
    public function execute($bind_params=false)
    {
    	$ret_val = false;

    	if (!$this->stmt) { return false; }
    	if ($bind_params && !is_array($bind_params)) {
    		$this->gen_error('Binding parameters must be passed as an array.');
    		return false;
    	}
    	$this->bind_params = $bind_params;

		//----------------------------------------------
		// Function Arguments
		//----------------------------------------------
		$arg_list = func_get_args();
		$lazy = (isset($arg_list[1]) && $arg_list[1]) ? (true) : (false);

		//----------------------------------------------
		// Character Set
		//----------------------------------------------
		$charset = $this->get_opt('charset');
		if (!empty($charset)) {
			$this->handle->set_charset($charset);
		}

		//----------------------------------------------
		// Bind Parameters
		//----------------------------------------------
		if ($bind_params) {

            //-----------------------------------------------------------
			// Are you feeling a bit lazy?
			// Let's just make those values into references for you...
			//-----------------------------------------------------------
			if ($lazy || $this->get_opt('make_bind_params_refs')) {
				$tmp_bind_params = $bind_params;
				for ($i = 1; $i < count($bind_params); $i++) {
					$bind_params[$i] =& $tmp_bind_params[$i];
				}
			}

            //-----------------------------------------------------------
			// Call Bind Parameters Method
            //-----------------------------------------------------------
	    	$bind_status = call_user_func_array(array($this->stmt, 'bind_param'), $bind_params);
	    	if (!$bind_status) {
	    		$this->gen_error('Binding of parameter data failed.');
	    		return false;
	    	}
		}

		//----------------------------------------------
		// Execute Query
		//----------------------------------------------
		$exec_status = $this->stmt->execute();
    	if (!$exec_status) {
    		$this->check_and_print_error($this->handle);
    		$this->gen_error('Query execution failed.');
    		return false;
    	}
    	else {
			//----------------------------------------------
			// Last Insert ID
			//----------------------------------------------
	        $this->last_id = $this->handle->insert_id;
    	}

		//----------------------------------------------
		// Affected Rows
		//----------------------------------------------
    	$this->affected_rows = $this->handle->affected_rows;
    	$ret_val = $this->affected_rows;

		//----------------------------------------------
		// Create Data Result Object if Necessary
		//----------------------------------------------
		if ($meta_data = $this->stmt->result_metadata()) {

			//---------------------------------------------------
			// MySQL Native Driver (mysqlnd)
			//---------------------------------------------------
			if (method_exists($this->stmt, 'get_result')) {
				$this->rsrc_id = $this->stmt->get_result();
				if (gettype($this->rsrc_id) != 'boolean') {
					$opts = array('stmt' => $this->stmt, 'prepared_query' => 1);
		        	$this->data_result = new DataResult($this->rsrc_id, $this->data_src, $opts);
		        }
	        }
			//---------------------------------------------------
			// Older MySQL Driver
			//---------------------------------------------------
	        else {
		        $this->stmt->store_result();
	        	$opts = array('stmt' => $this->stmt, 'prepared_query' => 1);
	        	$this->data_result = new DataResult($this->stmt, $this->data_src, $opts);
	        }
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
		if ($this->check_and_print_error($this->handle)) { return false; }

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
			return $this->stmt->close();
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
		$num_params = count($bind_params) - 1;
		if ($num_params > 0) {
			for ($i = 1; $i <= $num_params; $i++) {
				$type_index = $i - 1;
				$type = (isset($bind_params[0][$type_index])) ? ($bind_params[0][$type_index]) : (false);
				$param = (isset($bind_params[$i])) ? ($bind_params[$i]) : (false);
				if (!$type || !$param) { continue; }
				if ($type == 's' || $type == 'b') { $param = "'{$param}'"; }
				$pos = strpos($query, '?');
				if ($pos === false) { continue; }
				$query = substr_replace($query, $param, $pos, 1);
			}
		}
		return $query;
	}

}

