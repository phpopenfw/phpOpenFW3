<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Data Transaction / Oracle Plugin
 * A Oracle plugin to the (DataTrans) class
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
 * dt_oracle Class
 */
//**************************************************************************************
class dt_oracle extends dt_structure
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

			// Port
			if (!$this->port) { $this->port = 1521; }
	
			// Connection String
			if ($this->conn_str !== false) {
				$db_params = $this->conn_str;
			}
			else {
				$db_params = "(DESCRIPTION =(ADDRESS = (PROTOCOL = TCP)(HOST = {$this->server})(PORT = {$this->port}))\n";
				$db_params .= "(CONNECT_DATA = (SERVER = DEDICATED)(SERVICE_NAME = )(INSTANCE_NAME = )))";
			}
	
			// Connect
			if ($this->persistent) { $this->handle = oci_pconnect($this->user, $this->pass, $db_params); }
	        else { $this->handle = oci_connect($this->user, $this->pass, $db_params); }

	        // Check for and print error
	        if ($oracle_error=oci_error()) {
	        	$this->print_error($oracle_error);
	        	$this->handle = false;
				return false;
	        }

			// Keep track of the number of connections we create
			$this->increment_counters();
		}

		// Flag Connection as Open       
        $this->conn_open = true;

		// Start Transaction?
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
				return oci_close($this->handle);
			}
		}
		return true;
	}

	/**
	* Executes a query based on the data source type
	* @param mixed Oracle: SQL Statement
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
		// Parse Query
		//----------------------------------------------
		$this->rsrc_id = oci_parse($this->handle, $query);

		//----------------------------------------------
		// Execute Query
		//----------------------------------------------
		if (!$this->auto_commit) {
			@oci_execute($this->rsrc_id, OCI_DEFAULT);
		}
		else {
			@oci_execute($this->rsrc_id);
		}

		//----------------------------------------------
        // Check for error
		//----------------------------------------------
        $is_error = $this->check_and_print_error($this->rsrc_id);
		if ($is_error) { return false; }

		// Fetch Associative Record Set
        if ($this->rsrc_id) {
        	if (gettype($this->rsrc_id) != 'boolean') {
        		$this->data_result = new DataResult($this->rsrc_id, $this->data_src);
	        	$this->num_rows = $this->data_result->num_rows();
	        	$this->num_fields = $this->data_result->num_fields();
	            $this->affected_rows = oci_num_rows($this->rsrc_id);
        		$ret_val = $this->data_result;
			}
			else {
				$ret_val = $this->rsrc_id;
			}
        }

		//----------------------------------------------
        // Last Insert ID
		//----------------------------------------------
        $this->last_id = NULL;

		//----------------------------------------------
        // Check for error
		//----------------------------------------------
        $is_error = $this->check_and_print_error($this->rsrc_id);
		if ($is_error) { return false; }

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
		else if ($error = oci_error($rsrc)) {
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
			return parent::print_error($error['message'], $error['code']);
		}
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
		if (!$new && !$this->trans_started) { $this->start_trans(); }
    	else { $this->trans_started = false; }
		return true;
    }

	//*************************************************************************
	/**
	* Internal Commit Function
	**/
	//*************************************************************************
	protected function _commit() { return oci_commit($this->handle); }

	//*************************************************************************
	/**
	* Internal Rollback Function
	**/
	//*************************************************************************
	protected function _rollback() { return oci_rollback($this->handle); }

    //*************************************************************************
	/**
	* Prepare Function
	* @param string SQL Statement
	**/
	//*************************************************************************
    public function prepare($stmt=false)
    {
    	//if ($this->stmt) { oci_free_statement($this->stmt); }
    	$this->stmt = oci_parse($this->handle, $stmt);
    	$this->curr_query = $stmt;

        // Check for error
        $is_error = $this->check_and_print_error($this->handle);
		if ($is_error) { return false; }

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

    	if (!$this->stmt) { return false; }
    	if (!is_array($bind_params)) {
    		$this->gen_error('Binding parameters must be passed as an array.');
    		return false;
    	}
    	$this->bind_params = $bind_params;

		// Bind Parameters
		extract($bind_params);
		foreach ($bind_params as $key => $val) {
			oci_bind_by_name($this->stmt, $key, $$key);
		}

        // Check for error
        $is_error = $this->check_and_print_error($this->stmt);
		if ($is_error) { return false; }

		// Execute Query
		if (!$this->auto_commit) { $exec_status = @oci_execute($this->stmt, OCI_DEFAULT); }
		else { $exec_status = @oci_execute($this->stmt); }
    	if (!$exec_status) {

	        // Check for error
	        $is_error = $this->check_and_print_error($this->stmt);
			if ($is_error) { return false; }

    		$this->gen_error('Query execution failed.');
    		return false;
    	}

		// Fetch Associative Record Set
        if ($this->stmt) {
        	if (gettype($this->stmt) != 'boolean') {
        		$this->data_result = new DataResult($this->stmt, $this->data_src);
	        	$this->num_rows = $this->data_result->num_rows();
	        	$this->num_fields = $this->data_result->num_fields();
	            $this->affected_rows = oci_num_rows($this->stmt);

        		if ($this->num_fields) {
	        		$ret_val = $this->data_result;
	        	}
	        	else {
		        	$ret_val = $this->affected_rows;
	        	}
			}
			else {
				$ret_val = $this->stmt;
			}
        }

        // Last Insert ID
        $this->last_id = NULL;

        // Check for error
        $is_error = $this->check_and_print_error($this->stmt);
		if ($is_error) { return false; }

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
			return oci_free_statement($this->stmt);
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
				$key = ':' . $key;
				$param = "'{$param}'";
				$pos = strpos($query, $key);
				if ($pos === false) { continue; }
				$query = substr_replace($query, $param, $pos, strlen($key));
			}
		}
		return $query;
	}
}

