<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Data Transaction / Microsoft SQL Server Plugin
 * A Microsoft SQL Server plugin to the (DataTrans) class
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
 * dt_mssql Class
 */
//**************************************************************************************
class dt_mssql extends dt_structure
{

	//*************************************************************************
    /**
	* Opens a connection to the specified data source based on the data source type
	**/
	//*************************************************************************
	public function open()
	{
		if (!$this->handle) {

			// Setup Connection Parameters
			$host = $this->server;
			if (!empty($this->port)) { $host .= ':' . $this->port; }
			if (!empty($this->instance)) { $host .= '\\' . $this->instance; }
	
			// Attempt Connection
	        if ($this->persistent) { $this->handle = mssql_pconnect($host, $this->user, $this->pass); }
	        else { $this->handle = mssql_connect($host, $this->user, $this->pass); }
		}

        if (!$this->handle) {
            $this->connection_error('MSSQL: There was an error connecting to the database server.');
            $this->handle = false;
            return false;
        }
        else {
			// Select Database
        	$select_db_ok = @mssql_select_db($this->source, $this->handle);

			if (!$select_db_ok) {
				$msg = "Unable to select database '$this->source'. Either the database does not exist";
				$msg .= ' or the database for this data source is configured incorrectly.';
				$this->gen_error($msg);
				$this->close();
				$this->handle = false;
				return false;
			}

			// Keep track of the number of connections we create
			$this->increment_counters();
		}

		// Flag Connection as Open       
        $this->conn_open = true;

        return true;
	}
	
	//*************************************************************************
	/**
	* Closes a connection to the specified data source based on the data source type
	**/
	//*************************************************************************
    public function close()
	{
		$this->conn_open = false;
		if (!$this->reuse_connection) {
			if ($this->handle && !$this->data_result) {
				return mssql_close($this->handle);
			}
		}
		return true;
	}

	//*************************************************************************
	/**
	* Executes a query based on the data source type
	* @param mixed MsSQL: SQL Statement
	**/
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
        $this->rsrc_id = mssql_query($query);

		//----------------------------------------------
		// Affected Rows
		//----------------------------------------------
    	$this->affected_rows = $this->_affected_rows();
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
        $this->last_id = null;

        //----------------------------------------------
        // Error Reporting
        //----------------------------------------------
        if (!$this->rsrc_id) {
        	$msg = "There was an error performing the query.";
        	$this->gen_error($msg);
        	return false;
        }

		//----------------------------------------------
		// Return Data Result Object if it exists
		//----------------------------------------------
		if ($this->data_result) {
        	$this->num_rows = $this->data_result->num_rows();
        	$this->num_fields = $this->data_result->num_fields();
			$ret_val = $this->data_result;
		}

        return $ret_val;
	}

	//*************************************************************************
	/**
	* Get the error code for a MSSQL Query
	* @return integer Error Code
	**/
	//*************************************************************************
	private function _get_error_code()
	{
		$error = mssql_query("select @@ERROR as error", $this->handle);
		return mssql_result($error, 0, "error"); 
	}

	//*************************************************************************
	/**
	* Get the number of affected rows for a MSSQL Query
	* @return integer Number of rows
	**/
	//*************************************************************************
	private function _affected_rows()
	{
		$rsRows = mssql_query("select @@rowcount as rows", $this->handle);
		return mssql_result($rsRows, 0, "rows"); 
	}

}

