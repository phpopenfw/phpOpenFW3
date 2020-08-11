<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Quick Database Actions Class
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 */
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\Database;
use phpOpenFW\Database\DataTrans;

//**************************************************************************************
/**
 * Quick Database Actions Class
 */
//**************************************************************************************
class QDB
{

	//**************************************************************************************
	/**
	* Run a Query or Prepare and execute a query. Return a "DataResult" object.
	*
	* @param string Index of DB config
	* @param string SQL Statement
	* @param array Bind Parameters
	* @param array Options ('debug')
	* @return object A "DataResult" object which can be used to get the records.
	*/
	//**************************************************************************************
	public static function qdb_result($db_config, $strsql, $bind_params=[], $opts=false)
	{
		//------------------------------------------------------------------
		// Use Bind Parameters?
		//------------------------------------------------------------------
		$use_bind_params = (is_array($bind_params)) ? (true) : (false);

		//------------------------------------------------------------------
		// New Data Transaction
		//------------------------------------------------------------------
		$data1 = new DataTrans($db_config);
		if (!empty($opts['debug'])) { $data1->data_debug(true); }
		$data1->set_opt('make_bind_params_refs', 1);

		if (!empty($opts['charset'])) {
			$data1->set_opt('charset', $opts['charset']);
		}

		//------------------------------------------------------------------
		// Query: With or Without Bind Parameters
		//------------------------------------------------------------------
		if ($use_bind_params) {
			$prep_status = $data1->prepare($strsql);
			return $data1->execute($bind_params);
		}
		else {
			return $data1->data_query($strsql);
		}
	}

	//**************************************************************************************
	/**
	* Execute a query. Return the first row if possible.
	*
	* @param string Index of DB config
	* @param string SQL Statement
	* @param array Bind Parameters
	* @param array Options ('debug')
	* @return mixed An array representing the first row or FALSE if that row does not exist.
	*/
	//**************************************************************************************
	public static function qdb_first_row($db_config, $strsql, $bind_params=false, $opts=false)
	{
		//------------------------------------------------------------------
		// Use Bind Parameters?
		//------------------------------------------------------------------
		$use_bind_params = (is_array($bind_params) && count($bind_params)) ? (true) : (false);

		//------------------------------------------------------------------
		// New Data Transaction
		//------------------------------------------------------------------
		$data1 = new DataTrans($db_config);
		if (!empty($opts['debug'])) { $data1->data_debug(true); }
		$data1->set_opt('make_bind_params_refs', 1);

		if (!empty($opts['charset'])) {
			$data1->set_opt('charset', $opts['charset']);
		}

		//------------------------------------------------------------------
		// Query: With or Without Bind Parameters
		//------------------------------------------------------------------
		if ($use_bind_params) {
			$prep_status = $data1->prepare($strsql);
			$result = $data1->execute($bind_params);
		}
		else {
			$result = $data1->data_query($strsql);
		}

		if (gettype($result) == 'object' && get_class($result) == 'phpOpenFW\Database\DataResult') {
			return $result->fetch_row();
		}

		return false;
	}

	//**************************************************************************************
	/**
	* Execute a query. Return one row or value if possible.
	*
	* @param string Index of DB config
	* @param string SQL Statement
	* @param string The row index to use to pick out the row to return,
	* @param string The data format to index the dataset by ("", "key", "key:value")
	* @param array Bind Parameters
	* @param array Options
	* @return Mixed Either a record or a value
	*/
	//**************************************************************************************
	public static function qdb_row($db_config, $strsql, $row_index=0, $data_format=false, $bind_params=false, $opts=false)
	{
		//------------------------------------------------------------------
		// Use Bind Parameters?
		//------------------------------------------------------------------
		$use_bind_params = (is_array($bind_params) && count($bind_params)) ? (true) : (false);

		//------------------------------------------------------------------
		// Type Cast Row Key into String
		//------------------------------------------------------------------
		$row_index = (string)$row_index;
		if ($row_index == '') {
			trigger_error("ERROR: QDB::row(): Invalid row index passed.");
			return false;
		}

		//------------------------------------------------------------------
		// New Data Transaction
		//------------------------------------------------------------------
		$data1 = new DataTrans($db_config);
		if (!empty($opts['debug'])) { $data1->data_debug(true); }
		$data1->set_opt('make_bind_params_refs', 1);

		if (!empty($opts['charset'])) {
			$data1->set_opt('charset', $opts['charset']);
		}

		//------------------------------------------------------------------
		// Query: With or Without Bind Parameters
		//------------------------------------------------------------------
		if ($use_bind_params) {
			$prep_status = $data1->prepare($strsql);
			$exec_status = $data1->execute($bind_params);
		}
		else {
			$data1->data_query($strsql);
		}

		//------------------------------------------------------------------
		// Pull Data
		//------------------------------------------------------------------
		if (empty($data_format)) {
			$data = $data1->data_assoc_result();
		}
		else {
			$rf_arr = explode(':', $data_format);
			if (empty($rf_arr[0])) { unset($rf_arr[0]); }
			if (count($rf_arr) < 1) {
				$data = $data1->data_assoc_result();
			}
			else if (count($rf_arr) == 1) {
				$data = $data1->data_key_assoc($rf_arr[0]);
			}
			else if (count($rf_arr) > 1) {
				$data = $data1->data_key_val($rf_arr[0], $rf_arr[1]);
			}
		}
		//print_array($data);print $row_index;

		if (isset($data[$row_index])) { return $data[$row_index]; }

		return false;
	}

	//**************************************************************************************
	/**
	* Prepare and execute a query. Return results if they exist.
	*
	* @param string Index of DB config
	* @param string SQL Statement
	* @param array Bind Parameters
	* @param string Return Format to return ("", "key", "key:value")
	* @param array Options ('debug')
	* @return Array Record Set
	*/
	//**************************************************************************************
	public static function qdb_exec($db_config, $strsql, $bind_params, $return_format='', Array $opts=[])
	{
		//------------------------------------------------------------------
		// New Data Transaction
		//------------------------------------------------------------------
		$data1 = new DataTrans($db_config);
		if (!empty($opts['debug'])) { $data1->data_debug(true); }
		$data1->set_opt('make_bind_params_refs', 1);

		if (!empty($opts['charset'])) {
			$data1->set_opt('charset', $opts['charset']);
		}

		//------------------------------------------------------------------
		// Prepare Query
		//------------------------------------------------------------------
		$prep_status = $data1->prepare($strsql);

		//------------------------------------------------------------------
		// Execute Query
		//------------------------------------------------------------------
		$exec_status = $data1->execute($bind_params);

		//------------------------------------------------------------------
        // Return Execute Result?
		//------------------------------------------------------------------
        if (!empty($opts['return_result'])) {
            return $exec_status;
        }
		//------------------------------------------------------------------
        // Return Last Insert ID?
		//------------------------------------------------------------------
        else if (!empty($opts['return_insert_id'])) {
            return $data1->last_insert_id();
        }
        
		//------------------------------------------------------------------
		// Return Result Set
		//------------------------------------------------------------------
		$rf_arr = explode(':', $return_format);
		if (empty($rf_arr[0])) { unset($rf_arr[0]); }
		if (count($rf_arr) < 1) {
			return $data1->data_assoc_result();
		}
		else if (count($rf_arr) == 1) {
			return $data1->data_key_assoc($rf_arr[0]);
		}
		else if (count($rf_arr) > 1) {
			return $data1->data_key_val($rf_arr[0], $rf_arr[1]);
		}
	}

	//**************************************************************************************
	/**
	* Pull a list of records from a table
	*
	* @param string Index of DB config
	* @param string SQL Statement
	* @param string Return Format to return ("", "key", "key:value")
	* @param array Options ('debug')
	* @return Array Record Set
	*/
	//**************************************************************************************
	public static function qdb_list($db_config, $strsql, $return_format='', $opts=false)
	{
		//------------------------------------------------------------------
		// New Data Transaction
		//------------------------------------------------------------------
		$data1 = new DataTrans($db_config);
		if (!empty($opts['debug'])) { $data1->data_debug(true); }

		if (!empty($opts['charset'])) {
			$data1->set_opt('charset', $opts['charset']);
		}

		//------------------------------------------------------------------
		// Execute Query
		//------------------------------------------------------------------
		$query_result = $data1->data_query($strsql);

		//------------------------------------------------------------------
		// Return Result Set
		//------------------------------------------------------------------
		$rf_arr = explode(':', $return_format);
		if (empty($rf_arr[0])) { unset($rf_arr[0]); }
		if (count($rf_arr) < 1) {
			return $data1->data_assoc_result();
		}
		else if (count($rf_arr) == 1) {
			return $data1->data_key_assoc($rf_arr[0]);
		}
		else if (count($rf_arr) > 1) {
			return $data1->data_key_val($rf_arr[0], $rf_arr[1]);
		}
	}

	//**************************************************************************************
	/**
	* Safe Delete Row or Rows from a table
	*
	* @param string Index of DB config
	* @param string Database Table
	* @param string Where clause (must not be empty, in order for execution to take place)
	* @param array Options
	* @return Array Record Set
	*/
	//**************************************************************************************
	public static function qdb_delete($db_config, $db_table, $where='', $opts=false)
	{
		if ($where == '') {
			trigger_error('Error: QDB::delete(): No where clause specified!');
			return false;
		}

		//------------------------------------------------------------------
		// New Data Transaction
		//------------------------------------------------------------------
		$data1 = new DataTrans($db_config);
		if (!empty($opts['debug'])) { $data1->data_debug(true); }
		$data1->set_opt('make_bind_params_refs', 1);
		if (!empty($opts['charset'])) {
			$data1->set_opt('charset', $opts['charset']);
		}
		$where = trim($where);

		//------------------------------------------------------------------
		// Check for empty Where
		//------------------------------------------------------------------
		if (!empty($where)) {
			$strsql = "delete from {$db_table} where {$where}";
		}

		//------------------------------------------------------------------
		// Execute Query
		//------------------------------------------------------------------
		if (!empty($where) && isset($strsql)) {
			return $data1->data_query($strsql);
		}

		return false;
	}

	//**************************************************************************************
	/**
	* Quick Database Field Lookup
	*
	* @param string Index of DB config
	* @param string SQL Statement
	* @param string The field from which to pull the value from
	* @param array (Optional) An array in the proper format of the data source being used, of bind parameters.
	* @param array Options
	* @return mixed The value at the field index or false if it did not exist.
	*/
	//**************************************************************************************
	public static function qdb_lookup($data_source, $sql, $fields='', $bind_params=false, $opts=false)
	{
		//------------------------------------------------------------------
		// Check if fields are not specified
		//------------------------------------------------------------------
		if ($fields == '') {
			trigger_error('ERROR: QDB::lookup(): No return fields specified!!');
		}

		//------------------------------------------------------------------
		// New Data Transaction
		//------------------------------------------------------------------
		$data1 = new DataTrans($data_source);
		if (!empty($opts['debug'])) { $data1->data_debug(true); }
		$data1->set_opt('make_bind_params_refs', 1);

		if (!empty($opts['charset'])) {
			$data1->set_opt('charset', $opts['charset']);
		}

		//------------------------------------------------------------------
		// Use Bind Parameters
		//------------------------------------------------------------------
		if (is_array($bind_params) && count($bind_params)) {

			//--------------------------------------------------------
			// Prepare Query
			//--------------------------------------------------------
			$prep_status = $data1->prepare($sql);

			//--------------------------------------------------------
			// Execute Query
			//--------------------------------------------------------
			$exec_status = $data1->execute($bind_params);
		}
		//------------------------------------------------------------------
		// Straight Query
		//------------------------------------------------------------------
		else {
			//--------------------------------------------------------
			// Execute Query
			//--------------------------------------------------------
			$query_result = $data1->data_query($sql);
		}

		//------------------------------------------------------------------
		// Pull result set
		//------------------------------------------------------------------
		$result = $data1->data_assoc_result();

		//------------------------------------------------------------------
		// If result set empty, return false
		//------------------------------------------------------------------
		if (count($result) <= 0) {
			return false;
		}
		//------------------------------------------------------------------
		// Else Check field values
		//------------------------------------------------------------------
		else {
			//--------------------------------------------------------
			// Multiple fields specified
			//--------------------------------------------------------
			if (is_array($fields)) {
				$return_vals = array();
				foreach ($fields as $index) {
					if (array_key_exists($index, $result[0])) {
						$return_vals[$index] = $result[0][$index];
					}
					else {
						trigger_error("ERROR: QDB::lookup(): Field '$index' does not exist in record set!!");
					}
				}
			}
			//--------------------------------------------------------
			// One field only
			//--------------------------------------------------------
			else {
				if (array_key_exists($fields, $result[0])) { return $result[0][$fields]; }
				else {
					trigger_error("ERROR: QDB::lookup(): Field '$fields' does not exist in record set!!");
				}
			}
		}
	}

	//**************************************************************************************
	/**
	* Recordset Trim Function
	*
	* @param array The recordset to trim
	* @param bool True = Trim the data (default), False = Do not trim data
	* @param bool True = Trim the record row keys, False = Do not trim record row keys (default)
	* @param bool True = Trim the record column keys, False = Do not trim record column keys (default)
	* @return Array Modified Record Set
	*/
	//**************************************************************************************
	public static function rs_trim($rs, $trim_data=true, $trim_row_keys=false, $trim_col_keys=false)
	{
        //------------------------------------------------------------
        // Validate Recordset is an array
        //------------------------------------------------------------
		if (!is_array($rs)) {
			trigger_error("ERROR: QDB::rs_trim(): Recordset must be an array!");
			return false;
		}

        //------------------------------------------------------------
        // Process Data to be Trimmed
        //------------------------------------------------------------
		foreach ($rs as $key => $val) {

			//--------------------------------------------------------
            // Value is array, iterate through the values
			//--------------------------------------------------------
			if (is_array($val)) {
				foreach ($val as $key2 => $val2) {
    				//------------------------------------------------
					// Trim Column Keys
    				//------------------------------------------------
					if ($trim_col_keys) {
						unset($val[$key2]);

                        //--------------------------------------------
						// Trim Data
                        //--------------------------------------------
						if ($trim_data) {
    						$val[trim($key2)] = trim($val2);
    				    }
                        //--------------------------------------------
						// Do Not Trim Data
                        //--------------------------------------------
						else {
    						$val[trim($key2)] = $val2;
    				    }
					}
    				//------------------------------------------------
					// Do not Trim Column Keys
					// Trim Data
    				//------------------------------------------------
					else if ($trim_data) {
    					$val[$key2] = trim($val2);
    				}
				}
			}
			//--------------------------------------------------------
			// Trim Data
			//--------------------------------------------------------
			else if ($trim_data) {
    			$val = trim($val);
            }

			//--------------------------------------------------------
			// Trim Row Keys
			//--------------------------------------------------------
			if ($trim_row_keys) {
				unset($rs[$key]);
				$rs[trim($key)] = $val;
			}
			//--------------------------------------------------------
			// Do not Trim Row Keys
			//--------------------------------------------------------
			// If Trim Data or Trim Column Keys, 
			// reset in recordset array
			//--------------------------------------------------------
			else if ($trim_col_keys || $trim_data) {
    			$rs[$key] = $val;
            }
		}

		return $rs;
	}
}
