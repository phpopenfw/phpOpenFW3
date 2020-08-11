<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Data Transaction Class
 * A data abstraction class used to handle all database transactions. 
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 */
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\Database;

//**************************************************************************************
/**
 * Data Transaction Class
 */
//**************************************************************************************
class DataTrans
{

	//************************************************************************	
	// Class variables
	//************************************************************************
	/**
	* @var string data source type (mysql, mysqli, pgsql, oracle, mssql, db2, sqlsrv, sqlite)
	**/
	private $data_type;
	
	/**
	* @var bool Print the queries run through this transaction (Yes or No)
	**/
	private $print_query;

	/**
	* @var Object Internal Data Object 
	**/
	private $data_object;

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
	public function __construct($data_src='')
	{
        //=================================================================
		// Data Source
        //=================================================================
		if ($data_src != '') {
			if (!isset($_SESSION[$data_src])) {
				trigger_error("Error: [DataTrans]::__construct(): Invalid Data Source '{$data_src}'.");
				return false;
			}
		}
		else {
			if (isset($_SESSION['default_data_source'])) {
				$data_src = $_SESSION['default_data_source'];
			}
			else {
				trigger_error('Error: [DataTrans]::__construct(): Data Source not given and default data source is not set.');
				return false;
			}
		}
		$this->data_src = $data_src;

        //=================================================================
		// Convert MySQL to MySQLi Database Driver
        //=================================================================
        if ($_SESSION[$this->data_src]['type'] == 'mysql') {
            $_SESSION[$this->data_src]['type'] = 'mysqli';
        }

        //=================================================================
        // Create Object based on Data Source Type
        //=================================================================
        $this->data_type = $_SESSION[$this->data_src]['type'];
        $dt_class = '\phpOpenFW\Database\Drivers\DataTrans\dt_' . $this->data_type;
        $this->data_object = new $dt_class($this->data_src);

		//------------------------------------------------------------
		// Check if we are setting the character set
		//------------------------------------------------------------
		if (!empty($_SESSION[$this->data_src]['charset'])) {
			$this->data_object->set_opt('charset', $_SESSION[$this->data_src]['charset']);
		}
		
        return $this->data_object;
	}

	//*************************************************************************
	/**
	* Destructor Function
	**/
	//*************************************************************************
    public function __destruct()
    {
        $this->data_object->shutdown();
    }

	//*************************************************************************
	/**
	* Executes a query based on the data source type
	* @param string SQL Statement
	* @param array Bind Parameters (Optional)
	**/
	//*************************************************************************
	// Execute a query and store the record set
	//*************************************************************************
	public function data_query($query, $params=false)
	{
		//------------------------------------------------------------
		// Unset last_id / Clear results
		//------------------------------------------------------------
		$this->data_object->clear_last_insert_id();
		$this->data_object->clear_result();

		//------------------------------------------------------------
		// Execute the Query
		//------------------------------------------------------------
		$query_result = $this->data_object->query($query, $params);

		//------------------------------------------------------------
		// Print Query (if necessary)
		//------------------------------------------------------------
		if ($this->print_query) {
    		$this->display_query();
        }

		//------------------------------------------------------------
		// Return Results
		//------------------------------------------------------------
		return $query_result;
	}
	
	//*************************************************************************
	/**
	* Return the current record set in the form of an associative array
	* @return array current record set in the form of an associate array 
	**/
	//*************************************************************************
	// Extract the record set to local variables
	//*************************************************************************
	public function data_assoc_result()
	{
		return $this->data_object->assoc_result();
	}
	
	/**
	* Return an abbreviated form of the current record set in the form of an associative array
	* @param string field to be used as the 'key' in the associative array
	* @param string field to be used as the 'value' in the associative array
	* @return array abbreviated form of the current record set in the form of an associative array 
	**/
	//*************************************************************************
	// Extract an abbreviated record set to a 'key' => 'value' array
	//*************************************************************************
	public function data_key_val($key, $value)
	{
		return $this->data_object->key_val_result($key, $value);
	}

	//*************************************************************************
	/**
	* Return the current record set in the form of an associative array with $key as the key for each record
	* @param string The index of the field to be used as the 'key' for each record
	* @return array Return the current record set in the form of an associative array with $key as the key for each record
	**/
	//*************************************************************************
	public function data_key_assoc($key)
	{
		return $this->data_object->key_assoc_result($key);
	}

	/**
	* Return ID of the last inserted row for the current session
	* @return integer ID of the last inserted row for the current session
	**/
	//*************************************************************************
	// Return the ID of the last inserted row
	//*************************************************************************
	public function last_insert_id()
	{
		return $this->data_object->last_insert_id();
	}

	//*************************************************************************
	/**
	* Return the number rows in the current record set
	* @return the number rows in the current record set
	**/
	//*************************************************************************
	public function data_num_rows()
	{
		return $this->data_object->num_rows();
	}

	//*************************************************************************
	/**
	* Return the number fields in the current record set
	* @return integer The number fields in the current record set
	**/
	//*************************************************************************
	public function data_num_fields()
	{
		return $this->data_object->num_fields();
	}

	//*************************************************************************
	/**
	* Return the number rows in the current record set
	* @return the number rows in the current record set
	**/
	//*************************************************************************
	public function data_affected_rows()
	{
		return $this->data_object->affected_rows();
	}

	//*************************************************************************
	/**
	* Show the current record set in raw format
	**/
	//*************************************************************************	
	public function data_raw_output()
	{
		print "<pre>\n";
		print_r($this->data_object->assoc_result());
		print "</pre>\n";
	}

	//*************************************************************************
	/**
	* Print all queries run through this transaction
	* @param bool True (default) = Enable print of queries, False = Disable printing of queries
	**/
	//*************************************************************************
	public function data_debug($print_queries=true)
	{
    	$this->print_query = (bool)$print_queries;
    }

	//*************************************************************************
	/**
	* Return whether the current user is bound to the current data source
	* @return whether the current user is bound to the current data source
	**/
	//*************************************************************************
	public function is_bound()
	{
    	return $this->data_object->is_bound();
    }

	//*************************************************************************
	/**
	* Set Transactions auto commit flag (if supported)
	* @param bool True (default) = auto commit, False = no auto commit
	**/
	//*************************************************************************
	public function auto_commit($auto_commit=true)
	{
    	return $this->data_object->auto_commit($auto_commit);
    }

	//*************************************************************************
	/**
	* Start a new Database Transaction
	**/
	//*************************************************************************
	public function start_trans()
	{
    	return $this->data_object->start_trans();
    }

	//*************************************************************************
	/**
	* Commit current Outstanding Statements / Transaction(s)
	**/
	//*************************************************************************
	public function commit($start_new=true)
	{
    	return $this->data_object->commit($start_new);
    }

	//*************************************************************************
	/**
	* Rollback current Outstanding Statements / Transaction(s)
	**/
	//*************************************************************************
	public function rollback()
	{
    	return $this->data_object->rollback();
    }

	//*************************************************************************
	/**
	* Prepares an SQL statement to be executed
	* @param string SQL Statement
	**/
	//*************************************************************************
	public function prepare($statement=false)
	{
		//------------------------------------------------------------
		// Unset last_id / Clear results
		//------------------------------------------------------------
		$this->data_object->clear_last_insert_id();
		$this->data_object->clear_result();

		if (!$this->data_object->is_open()) {
    		return false;
        }

		if (!$statement) {
			trigger_error('Error: [DataTrans]::prepare(): No statement or invalid statement passed.');
			return false;
		}
		else {
			//------------------------------------------------------------
			// Arguments
			//------------------------------------------------------------
			$args = array();
			foreach (func_get_args() as $arg) {
    			$args[] = $arg;
            }
			return call_user_func_array(array($this->data_object, 'prepare'), $args);
		}
	}

	//*************************************************************************
	/**
	* Executes a prepared SQL statement given parameters
	* @param array An array of parameters to be passed during binding.
	**/
	//*************************************************************************
	public function execute($params=false)
	{
		//------------------------------------------------------------
		// Unset last_id / Clear results
		//------------------------------------------------------------
		$this->data_object->clear_last_insert_id();
		$this->data_object->clear_result();

		//------------------------------------------------------------
		// Check if Connection Open
		//------------------------------------------------------------
		if (!$this->data_object->is_open()) {
    		return false;
        }

		//------------------------------------------------------------
		// Extra Function Arguments
		//------------------------------------------------------------
		$args = array();
		foreach (func_get_args() as $arg) {
    		$args[] = $arg;
        }

		//------------------------------------------------------------
		// Execute Query
		//------------------------------------------------------------
		$exec_result = call_user_func_array(array($this->data_object, 'execute'), $args);

		//------------------------------------------------------------
		// Print Query (if necessary)
		//------------------------------------------------------------
		if ($this->print_query) {
    		$this->display_query();
        }

		return $exec_result;
	}

	//*************************************************************************
	/**
	* Display the current query being run
	**/
	//*************************************************************************
	protected function display_query()
	{
		//------------------------------------------------------------
		// Pull Query and Bind Params
		//------------------------------------------------------------
		$query = $this->data_object->get_query();
		if (!$query) {
			return false;
		}

		print 'Current Query: ';

		if (is_array($query)) {
			if (isset($_SERVER)) {
				print "<pre>\n";
				print_r($query);
				print "</pre>\n";
			}
			else {
    			print_r($query);
            }
		}
		else {
			if (isset($_SERVER)) {
    			print "{$query}<br/>\n";
            }
			else {
    			print "{$query}\n";
            }
		}
	}

	/**
	* Get Transaction Option
	* @param string Option Key
	* @return The value of the option or false if it does not exist
	**/
	//*************************************************************************
	// Get Transaction Option
	//*************************************************************************
	public function get_opt($opt)
	{
		return $this->data_object->get_opt($opt);
	}

	/**
	* Set Transaction Option
	* @param string Option Key
	* @param string Option Value
	**/
	//*************************************************************************
	// Set Transaction Option
	//*************************************************************************
	public function set_opt($opt, $val=false)
	{
		return $this->data_object->set_opt($opt, $val);
	}

	/**
	* Get Connection Handle
	* @return The database connection handle being used
	**/
	//*************************************************************************
	// Get Connection Handle
	//*************************************************************************
	public function get_conn_handle()
	{
		return $this->data_object->get_conn_handle();
	}

}
