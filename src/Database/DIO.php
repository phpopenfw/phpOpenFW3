<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Database Interface Object Plugin
 * An abstract class for building Database to Object programmatic bridges
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 */
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\Database;
use \phpOpenFW\Database\DataTrans;
use \phpOpenFW\Database\DataQuery;

//**************************************************************************************
/**
 * Database Interface Object Class
 */
//**************************************************************************************
abstract class DIO
{

	//====================================================================
	// Member Variables
	//====================================================================
	protected $data_source;
	protected $db_type;
	protected $schema;
	protected $schema_separator = '.';
	protected $table;
	protected $primary_key = 'id';
	protected $data;
	protected $table_info;
	protected $quoted_types;
	protected $load_prefix;
	protected $unset_fields;
	protected $class_name;
	protected $no_save_empty_types;
	protected $save_default_types;
	protected $use_bind_params;
	protected $bind_params;
	protected $bind_param_count;
	protected $charset;
	protected $last_query = false;
	protected $execute_queries;
	protected $disabled_methods = [];
	protected $pkey_values = false;
	protected $last_insert_id = false;

	//=====================================================================
	// Member Functions
	//=====================================================================

	//***********************************************************************
	//***********************************************************************
	// Load Values
	//***********************************************************************
	//***********************************************************************
	public function load($pkey_values=false)
	{
		//===============================================================
        // Set Primary Key Value(s)
        // Reset Last Insert ID
		//===============================================================
    	$this->pkey_values = $pkey_values;
    	$this->last_insert_id = false;

		//===============================================================
		// Load data from database
		//===============================================================
		if (!empty($pkey_values)) {

            //-----------------------------------------------------------------
			// Build SQL
            //-----------------------------------------------------------------
			$strsql = 'select * from ' . $this->full_table_name();
			$where = $this->build_where($pkey_values);
			if (!$where) {
    			$this->trigger_error(__METHOD__, 'An error occurred generating SQL where clause.');
    			return false;
			}
			$strsql .= $where;

            //-----------------------------------------------------------------
            // Store last query
            //-----------------------------------------------------------------
            $this->last_query = ['strsql' => $strsql];
            if ($this->use_bind_params) {
                $this->last_query['bind_params'] = $this->bind_params;
            }

            //-----------------------------------------------------------------
            // Is query execution disabled?
            //-----------------------------------------------------------------
            if (!$this->execute_queries) {
                return false;
            }

            //-----------------------------------------------------------------
            // Create a new data transaction and execute query
            //-----------------------------------------------------------------
            $data1 = new DataTrans($this->data_source);

            //-----------------------------------------------------------------
			// Use Bind Parameters
            //-----------------------------------------------------------------
			if ($this->use_bind_params) {

				//-------------------------------------------------
				// Prepare / Execute Query
				//-------------------------------------------------
				$prep_status = $data1->prepare($strsql);
				$exec_status = $data1->execute($this->bind_params);

				//-------------------------------------------------
				// Reset Bind Variables
				//-------------------------------------------------
				$this->reset_bind_vars();
            }
            //-----------------------------------------------------------------
            // Do NOT Use Bind Parameters
            //-----------------------------------------------------------------
            else {
	            $query_result = $data1->data_query($strsql);
			}

            //-----------------------------------------------------------------
            // Get Results
            //-----------------------------------------------------------------
			$result = $data1->data_assoc_result();

            //-----------------------------------------------------------------
            // Set / Unset Appropriate Fields
            //-----------------------------------------------------------------
			foreach ($this->table_info as $field => $info) {

				//-------------------------------------------------
                // No Load
                //-------------------------------------------------
                if ($info['no_load']) {
                    unset($result[0][$field]);
                }
				//-------------------------------------------------
                // Alias
				//-------------------------------------------------
                else if (isset($info['alias'])) {
                    $result[0][$info['alias']] = $result[0][$field];
                    unset($result[0][$field]);
                }
			}

            //-----------------------------------------------------------------
            // Was there data returned?
            //-----------------------------------------------------------------
			if (isset($result[0])) {
				$this->data = $result[0];
				return 1;
			}
			else {
				$this->data = array();
				return 0;
			}
		}
		//===============================================================
		// Load data from defaults
		//===============================================================
		else {
            foreach ($this->table_info as $key => $info) {
                if (!$info['no_load']) {
                    if (isset($info['alias'])) {
                        $key = $info['alias'];
                    }
                    $this->data[$key] = $info['load_default'];
                }
            }
            return 2;
		}
	}

	//***********************************************************************
	//***********************************************************************
	// Export data as an associative array
	//***********************************************************************
	//***********************************************************************
	public function export($pre_args=array())
	{
		//===============================================================
		// Check for pre_export()
		//===============================================================
		if (!$this->method_disabled('pre_export') && method_exists($this, 'pre_export')) {
			if (!is_array($pre_args)) {
				$pre_args = array($pre_args);
			}
			call_user_func_array(array($this, 'pre_export'), $pre_args);
		}

		//===============================================================
		// Load Prefix
		//===============================================================
        if (isset($this->load_prefix)) {
        	$return_data = array();
            foreach ($this->data as $field => $info) {
                $return_data[$this->load_prefix . $field] = $this->data[$field];
            }
            if (count($return_data) <= 0) {
                unset($return_data);
            }
        }
        else {
        	if (isset($this->data)) {
            	$return_data = $this->data;
            }
        }

		return (isset($return_data)) ? ($return_data) : (false);
	}

	//***********************************************************************
	//***********************************************************************
	// Import data from $_POST, $_GET, or a pre-defined array
	//***********************************************************************
	//***********************************************************************
	public function import($in_array='', $pre_args=array())
	{
		//===============================================================
		// Check for pre_import()
		//===============================================================
		if (!$this->method_disabled('pre_import') && method_exists($this, 'pre_import')) {
			if (!is_array($pre_args)) {
				$pre_args = array($pre_args);
			}
			call_user_func_array(array($this, 'pre_import'), $pre_args);
		}

        $this->unset_fields = array();
		foreach ($this->table_info as $field => $info) {

			//-------------------------------------------------
            // Load Prefix
			//-------------------------------------------------
            if (isset($this->load_prefix)) {
                $var_field = $this->load_prefix . $field;
            }
            else {
                $var_field = $field;
            }

			//-------------------------------------------------
            // Search Input Array
            //-------------------------------------------------
            if (isset($in_array) && !empty($in_array)) {
            	if (array_key_exists($var_field, $in_array)) {
                	$this->data[$field] = $in_array[$var_field];
                	continue;
                }
            }
			//-------------------------------------------------
            // Search POST and GET
            //-------------------------------------------------
            else {
            	if (array_key_exists($var_field, $_POST)) {
                	$this->data[$field] = $_POST[$var_field];
                	continue;
                }
            	elseif (array_key_exists($var_field, $_GET)) {
                	$this->data[$field] = $_GET[$var_field];
                	continue;
                }
            }

            //-------------------------------------------------
        	// Check for a default save value
        	//-------------------------------------------------
        	if (isset($info['save_default'])) {
            	$this->data[$field] = $info['save_default'];
            }
        	else {
            	$this->unset_fields[$field] = '';
            }
		}

		return $this->unset_fields;
	}

	//***********************************************************************
	//***********************************************************************
	// Save data to database
	//***********************************************************************
	//***********************************************************************
	public function save($pkey_values='', $pre_args=array(), $post_args=array())
	{
		//===============================================================
        // Set Primary Key Value(s)
        // Reset Last Insert ID
		//===============================================================
    	$this->pkey_values = $pkey_values;
    	$this->last_insert_id = false;

		//===============================================================
		// Check for pre_save()
		//===============================================================
		if (!$this->method_disabled('pre_save') && method_exists($this, 'pre_save')) {
			if (!is_array($pre_args)) {
				$pre_args = array($pre_args);
			}
			call_user_func_array(array($this, 'pre_save'), $pre_args);
		}

		//===============================================================
        // Variable Declarations
		//===============================================================
        $qa = array();
        $ret_val = false;

		//===============================================================
        // Set Table
		//===============================================================
        $qa['table'] = $this->full_table_name();

		//===============================================================
		// Check / Set Field Values
		//===============================================================
        foreach ($this->data as $field => &$value) {

			//-------------------------------------------------
			// Set to Save by default
			//-------------------------------------------------
        	$save = true;

			//-------------------------------------------------
        	// Check if field is not supposed to save
        	//-------------------------------------------------
        	if ($this->table_info[$field]['no_save']) {
        		$save = false;
        	}
        	//-------------------------------------------------
        	// Else if value is empty (but exists)
        	//-------------------------------------------------
        	else if ($value == '') {

	        	//-------------------------------------------------
        		// If Save Default set
        		//-------------------------------------------------
        		if (isset($this->table_info[$field]['save_default'])) {
        			$value = $this->table_info[$field]['save_default'];
        		}
        		//-------------------------------------------------
        		// Else if field data type save default set
        		//-------------------------------------------------
        		else if (array_key_exists($this->table_info[$field]['data_type'], $this->save_default_types)) {
        			$value = $this->save_default_types[$this->table_info[$field]['data_type']];
        		}
        		//-------------------------------------------------
        		// Else if field set to no save on empty
        		//-------------------------------------------------
        		else if (isset($this->table_info[$field]['no_save_empty']) && $this->table_info[$field]['no_save_empty']) {
        			$save = false;
        		}
        		//-------------------------------------------------
        		// Else if field data type set to no save on empty
        		//-------------------------------------------------
        		else if (array_key_exists($this->table_info[$field]['data_type'], $this->no_save_empty_types)) {
        			$save = false;
        		}

				//-------------------------------------------------
        		// If NULL
        		//-------------------------------------------------
        		if (is_null($value)) {
	        		if (!$this->use_bind_params) {
	        			$value = 'NULL';
						$this->table_info[$field]['quotes'] = 'disable';
					}
        		}
        	}

			//-------------------------------------------------
            // Check if field is not supposed to save
            //-------------------------------------------------
            if ($save) {

				//-------------------------------------------------
				// Use Bind Parameters
				//-------------------------------------------------
				if ($this->use_bind_params && $this->table_info[$field]['can_bind_param']) {

					switch ($this->db_type) {

		                case 'mysqli':
		                	$qa['fields'][$field] = '?';
		                	$this->bind_param_count++;
		                	$tmp_type = \phpOpenFW\Database\Structure\DatabaseType\MySQL::GetBindType($this->table_info[$field]['data_type']);
		                	$this->bind_params[0] .= $tmp_type;
		                	$this->bind_params[] = &$value;
		                    break;

						case 'sqlsrv':
		                	$this->bind_param_count++;
							$qa['fields'][$field] = '?';
		                	$this->bind_params[] = &$value;
							break;

		                case 'pgsql':
		                	$this->bind_param_count++;
		                	$tmp_param = '$' . $this->bind_param_count;
		                	$qa['fields'][$field] = $tmp_param;
		                	$this->bind_params[] = $value;
	                    	break;

						case 'oracle':
		                	$this->bind_param_count++;
							$tmp_param = 'p' . $this->bind_param_count;
		                	$qa['fields'][$field] = ':' . $tmp_param;
		                	$this->bind_params[$tmp_param] = $value;
							break;

						case 'mssql':
						case 'mysql':
						case 'sqlite':
							switch ($this->table_info[$field]['quotes']) {

								//-------------------------------------------------
			                    // Force quotes
								//-------------------------------------------------
			                    case 'force':
			                        $qa['fields'][$field] = "'{$value}'";
			                        break;

								//-------------------------------------------------
			                    // Disable quotes
			                    //-------------------------------------------------
			                    case 'disable':
			                        $qa['fields'][$field] = $value;
			                        break;

								//-------------------------------------------------
			                    // Auto detect if quotes are needed
			                    //-------------------------------------------------
			                    default:
			                        if (isset($this->quoted_types[$this->table_info[$field]['data_type']])) {
			                            $qa['fields'][$field] = "'{$value}'";
			                        }
			                        else {
			                            $qa['fields'][$field] = $value;
			                        }
			                        break;
			                }
							break;

						case 'db2':
		                	$qa['fields'][$field] = '?';
		                	$this->bind_params[] = $value;
		                	$this->bind_param_count++;
							break;

		            }
				}
				//-------------------------------------------------
				// Do NOT use Bind Parameters
				//-------------------------------------------------
				else {
	                switch ($this->table_info[$field]['quotes']) {

						//-------------------------------------------------
	                    // Force quotes
						//-------------------------------------------------
	                    case 'force':
	                        $qa['fields'][$field] = "'{$value}'";
	                        break;

						//-------------------------------------------------
	                    // Disable quotes
	                    //-------------------------------------------------
	                    case 'disable':
	                        $qa['fields'][$field] = $value;
	                        break;

						//-------------------------------------------------
	                    // Auto detect if quotes are needed
	                    //-------------------------------------------------
	                    default:
	                        if (isset($this->quoted_types[$this->table_info[$field]['data_type']])) {
	                            $qa['fields'][$field] = "'{$value}'";
	                        }
	                        else {
	                            $qa['fields'][$field] = $value;
	                        }
	                        break;
	                }
				}
            }
        }

		//===============================================================
        // Set Query Type (insert or update)
		//===============================================================
        if (!empty($pkey_values)) {
            $qa['type'] = 'update';
            $qa['filter_phrase'] = $this->build_where($pkey_values);
            if (!$qa['filter_phrase']) {
                $this->trigger_error(__METHOD__, 'An error occurred generating SQL where clause.');
                return false;
            }
        }
        else {
            $qa['type'] = 'insert';
        }

		//===============================================================
        // Render Query
		//===============================================================
        $query = new DataQuery($qa);
        $strsql = $query->render();

		//===============================================================
        // Store last query
		//===============================================================
        $this->last_query = ['strsql' => $strsql];
        if ($this->use_bind_params) {
            $this->last_query['bind_params'] = $this->bind_params;
        }

		//===============================================================
        // Execute Query
		//===============================================================
        if ($this->execute_queries) {

	        //-----------------------------------------------------------
            // Create a new data transaction and execute query
	        //-----------------------------------------------------------
            $data1 = new DataTrans($this->data_source);

	        //-----------------------------------------------------------
			// Set Character set?
	        //-----------------------------------------------------------
			if (!empty($this->charset)) {
				$data1->set_opt('charset', $this->charset);
			}

	        //-----------------------------------------------------------
			// Use Bind Parameters
	        //-----------------------------------------------------------
			if ($this->use_bind_params) {

				//-------------------------------------------------------
				// Prepare / Execute Query
				//-------------------------------------------------------
				$prep_status = $data1->prepare($strsql);
				$ret_val = $data1->execute($this->bind_params);

				//-------------------------------------------------------
				// Reset Bind Variables
				//-------------------------------------------------------
				$this->reset_bind_vars();
            }
	        //-----------------------------------------------------------
            // Do NOT Use Bind Parameters
	        //-----------------------------------------------------------
            else {
	            $ret_val = $data1->data_query($strsql);
			}

	        //-----------------------------------------------------------
			// Last Insert ID if Insert Statement performed 
			// and a valid ID is returned
	        //-----------------------------------------------------------
            if ($qa['type'] == 'insert') {
            	$this->last_insert_id = $data1->last_insert_id();
            	if ($this->last_insert_id !== false) {
                	$ret_val = $this->last_insert_id;
                }
            }
        }

		//===============================================================
        // Check for post_save()
		//===============================================================
		if (!$this->method_disabled('post_save') && method_exists($this, 'post_save')) {
			if (!is_array($post_args)) {
				$post_args = array($post_args);
			}
			call_user_func_array(array($this, 'post_save'), $post_args);
		}

		return $ret_val;
	}

	//***********************************************************************
	//***********************************************************************
	// Delete record from database
	//***********************************************************************
	//***********************************************************************
	public function delete($pkey_values='', $pre_args=array(), $post_args=array())
	{
    	$ret_val = false;

		//===============================================================
        // Set Primary Key Value(s)
        // Reset Last Insert ID
		//===============================================================
    	$this->pkey_values = $pkey_values;
        $this->last_insert_id = false;

		//===============================================================
		// Check for pre_delete()
		//===============================================================
		if (!$this->method_disabled('pre_delete') && method_exists($this, 'pre_delete')) {
			if (!is_array($pre_args)) {
				$pre_args = array($pre_args);
			}
			call_user_func_array(array($this, 'pre_delete'), $pre_args);
		}

        //===============================================================
        // Validate Keys / Filtering Values
        //===============================================================
        if (empty($pkey_values)) {
            $this->trigger_error(__METHOD__, 'No primary key(s) given.');
            return false;
        }

        //===============================================================
        // Build Query
        //===============================================================
        $qa = [
            'type' => 'delete',
            'table' => $this->full_table_name(),
            'filter_phrase' => $this->build_where($pkey_values)
        ];
        if (!$qa['filter_phrase']) {
            $this->trigger_error(__METHOD__, 'An error occurred generating SQL where clause.');
            return false;
        }
        $query = new DataQuery($qa);
        $strsql = $query->render();

        //===============================================================
        // Store last query
        //===============================================================
        $this->last_query = ['strsql' => $strsql];
        if ($this->use_bind_params) {
            $this->last_query['bind_params'] = $this->bind_params;
        }

        //===============================================================
        // Execute Query
        //===============================================================
        if ($this->execute_queries) {

            //-----------------------------------------------------------
            // Create a new data transaction and execute query
            //-----------------------------------------------------------
            $data1 = new DataTrans($this->data_source);

            //-----------------------------------------------------------
			// Use Bind Parameters
            //-----------------------------------------------------------
			if ($this->use_bind_params) {

				//-------------------------------------------------------
				// Prepare / Execute Query
				//-------------------------------------------------------
				$prep_status = $data1->prepare($strsql);
				$ret_val = $data1->execute($this->bind_params);

				//-------------------------------------------------------
				// Reset Bind Variables
				//-------------------------------------------------------
				$this->reset_bind_vars();
            }
            //-----------------------------------------------------------
            // Do NOT Use Bind Parameters
            //-----------------------------------------------------------
            else {
	            $ret_val = $data1->data_query($strsql);
			}
        }

		//===============================================================
        // Check for post_delete()
		//===============================================================
		if (!$this->method_disabled('post_delete') && method_exists($this, 'post_delete')) {
			if (!is_array($post_args)) {
				$post_args = array($post_args);
			}
			call_user_func_array(array($this, 'post_delete'), $post_args);
		}

        return $ret_val;
	}

	//***********************************************************************
	//***********************************************************************
	// Set Data Source for this object
	//***********************************************************************
	//***********************************************************************
	protected function set_data_source($data_source, $table)
	{
		//===============================================================
		// Set Class Name
		//===============================================================
		$this->class_name = get_class($this);

		//===============================================================
        // Set default execution statuses
		//===============================================================
        $this->execute_queries = true;
        $this->disabled_methods = [];

		//===============================================================
        // Initialize No Save Empty Data Types 
        // and Save Default Data Types Arrays
		//===============================================================
        $this->no_save_empty_types = array();
        $this->save_default_types = array();

		//===============================================================
        // Validate / Set Data Source
		//===============================================================
		$this->data_source = (string)$data_source;
		$ds_data = \phpOpenFW\Framework\Core\DataSources::GetOne($this->data_source);
		if (!$ds_data) {
    		$this->trigger_error(__METHOD__, 'Data Source does not exist.');
    		return false;
		}

		//===============================================================
        // Set Database / Database Type
		//===============================================================
        $this->database = $ds_data['source'];
        $this->db_type = $ds_data['type'];

		//===============================================================
        // Set Quoted Types
		//===============================================================
        $this->quoted_types = Structure\Table::QuotedTypes($this->db_type, true);

		//===============================================================
        // Set Table and Schema
		//===============================================================
        $tmp = Structure\Table::DetermineSchema($this->data_source, $table);
        $this->table = $tmp['table'];
        if ($tmp['schema'] != '') {
            $this->schema = $tmp['schema'];
        }

		//===============================================================
        // Database Type Specific Options
		//===============================================================
        switch ($this->db_type) {

            case 'mysqli':
            case 'pgsql':
			case 'oracle':
			case 'sqlsrv':
				$this->use_bind_params = true;
				break;

			case 'db2':
				$this->use_bind_params = true;
                $this->schema_separator = '/';
				break;

			default:
				$this->use_bind_params = false;
				break;

        }

		//===============================================================
        // Pull Table Info
		//===============================================================
        $this->table_info = Structure\Table::TableStructure($this->data_source, $table);

		//===============================================================
		// Initialize variables via reset() method
		//===============================================================
		$this->reset();

        return true;
	}

	//***********************************************************************
    //***********************************************************************
	// Set the primary key for this object
	//***********************************************************************
	//***********************************************************************
	public function set_pkey($pkey, $save=false)
	{
		if (gettype($pkey) == 'array') {
            $this->primary_key = $pkey;
		    foreach ($this->primary_key as $field) {
                $this->no_load($field);
                if (!$save) {
                    $this->no_save($field);
                }
		    }
        }
		else {
            $this->primary_key = $pkey;
            settype($this->primary_key, 'string');
            $this->no_load($this->primary_key);
            if (!$save) {
                $this->no_save($this->primary_key);
            }
		}
	}

	//***********************************************************************
	//***********************************************************************
	// Set field load default (deprecated)
	//***********************************************************************
	//***********************************************************************
	public function set_field_default($field=null, $value=null)
	{
		if ($field === null || $value === null) {
			$this->trigger_error(__METHOD__, 'Invalid parameter count.');
			return false;
		}

        $this->set_load_default($field, $value);
        return true;
	}

	//***********************************************************************
	//***********************************************************************
	// Set field load default
	//***********************************************************************
	//***********************************************************************
	public function set_load_default($field=null, $value=null)
	{
		if ($field === null || $value === null) {
			$this->trigger_error(__METHOD__, 'Invalid parameter count.');
			return false;
		}
		else {
			if (isset($this->table_info[$field])) {
                $this->table_info[$field]['load_default'] = $value;
                return true;
            }
			else {
    			$this->trigger_error(__METHOD__, "Field '{$field}' does not exist.");
    			return false;
            }
		}
	}

	//***********************************************************************
	//***********************************************************************
	// Set field save default value
	//***********************************************************************
	//***********************************************************************
	public function set_save_default($field=null, $value=null)
	{
		if ($field === null || $value === null) {
			$this->trigger_error(__METHOD__, 'Invalid parameter count.');
		}
		else {
			if (isset($this->table_info[$field])) {
    			$this->table_info[$field]['save_default'] = $value;
            }
			else {
    			$this->trigger_error(__METHOD__, "Field '{$field}' does not exist.");
            }
		}
	}

	//***********************************************************************
	//***********************************************************************
	// Set default save value for data type(s)
	//***********************************************************************
	//***********************************************************************
	public function set_save_default_types($types)
	{
		if (isset($types)) {
			if (is_array($types)) {
				foreach($types as $type => $value) {
					$this->save_default_types[$type] = $value;
				}
			}
			else {
				$err_msg = 'Data types and values must be passed as an associative array with each element';
				$err_msg .= ' in the following form: [data type] => [default value].';
				$this->trigger_error(__METHOD__, $err_msg);
			}
		}
		else {
    		$this->trigger_error(__METHOD__, 'No data type(s) passed.');
        }
	}

	//***********************************************************************
	//***********************************************************************
	// Set field data
	//***********************************************************************
	//***********************************************************************
	public function set_field_data($field, $value, $use_quotes='auto')
	{
		if (isset($this->table_info[$field])) {
            $this->data[$field] = $value;
        }
		else {
    		$this->trigger_error(__METHOD__, "Field '{$field}' does not exist.");
        }
	}

	//***********************************************************************
	//***********************************************************************
	// Set field alias
	//***********************************************************************
	//***********************************************************************
	public function set_field_alias($field, $alias)
	{
		if (isset($this->table_info[$field])) {
    		$this->table_info[$field]['alias'] = $alias;
        }
		else {
    		$this->trigger_error(__METHOD__, "Field '{$field}' does not exist.");
        }
	}

	//***********************************************************************
	//***********************************************************************
	// Set field quotes (Force or Disable)
	//***********************************************************************
	//***********************************************************************
	public function set_field_quotes($field, $mode)
	{
		if (isset($this->table_info[$field])) {
            switch (strtoupper($mode)) {
                case 'FORCE':
                    $this->table_info[$field]['quotes'] = 'force';
                    break;

                case 'DISABLE':
                    $this->table_info[$field]['quotes'] = 'disable';
                    break;
            }
        }
		else {
    		$this->trigger_error(__METHOD__, "Field '{$field}' does not exist.");
        }
	}

	//***********************************************************************
	//***********************************************************************
	// Set load prefix
	//***********************************************************************
	//***********************************************************************
	public function set_load_prefix($prefix)
	{
		$this->load_prefix = $prefix;
		settype($this->load_prefix, 'string');
	}

	//***********************************************************************
	//***********************************************************************
	// Enable/Disable a field from using Bind Parameters
	//***********************************************************************
	//***********************************************************************
	public function set_use_bind_param($field, $flag)
	{
		$flag = (bool)$flag;
		if (isset($this->table_info[$field])) {
    		$this->table_info[$field]['can_bind_param'] = $flag;
        }
		else {
    		$this->trigger_error(__METHOD__, "Field '{$field}' does not exist.");
        }
	}

	//***********************************************************************
	//***********************************************************************
	// Exclude a field from database transactions
	//***********************************************************************
	//***********************************************************************
	public function no_save($field)
	{
		if (isset($this->table_info[$field])) {
    		$this->table_info[$field]['no_save'] = true;
        }
		else {
    		$this->trigger_error(__METHOD__, "Field '{$field}' does not exist.");
        }
	}

	//***********************************************************************
	//***********************************************************************
	// Exclude a field from database transactions when empty
	//***********************************************************************
	//***********************************************************************
	public function no_save_empty($field)
	{
		if (isset($this->table_info[$field])) {
    		$this->table_info[$field]['no_save_empty'] = true;
        }
		else {
    		$this->trigger_error(__METHOD__, "Field '{$field}' does not exist.");
        }
	}

	//***********************************************************************
	//***********************************************************************
	// Exclude a data types from database transactions when empty
	//***********************************************************************
	//***********************************************************************
	public function no_save_empty_types($types)
	{
		if (isset($types)) {
			if (is_array($types)) {
				foreach($types as $type) {
					$this->no_save_empty_types[$type] = 1;
				}
			}
			else {
				$this->no_save_empty_types[$types] = 1;
			}
		}
		else {
    		$this->trigger_error(__METHOD__, 'No data type(s) passed.');
        }
	}

	//***********************************************************************
	//***********************************************************************
	// Exclude a field from loading
	//***********************************************************************
	//***********************************************************************
	public function no_load($field)
	{
		if (isset($this->table_info[$field])) {
    		$this->table_info[$field]['no_load'] = true;
        }
		else {
    		$this->trigger_error(__METHOD__, "Field '{$field}' does not exist.");
        }
	}

	//***********************************************************************
	//***********************************************************************
	// Set transactions to print only
	//***********************************************************************
	//***********************************************************************
	public function print_only()
	{
        $this->disable_queries();
        $msg = 'This method has been deprecated. Please use the disable_queries() method instead.';
        $this->trigger_error(__METHOD__, $msg, E_USER_DEPRECATED);
	}

	//***********************************************************************
	//***********************************************************************
	// Disable database queries (insert, update, and delete only)
	//***********************************************************************
	//***********************************************************************
	public function disable_queries($disable=true)
	{
        $this->execute_queries = !$disable;
	}

	//***********************************************************************
	//***********************************************************************
	// Disable a method from executing
	//***********************************************************************
	//***********************************************************************
	public function disable_method($method)
	{
    	//===============================================================
    	// Validate Parameters
    	//===============================================================
    	$tmp_type = gettype($method);
        if (!$method || is_numeric($method) || in_array($tmp_type, ['array', 'object'])) {
            return false;
        }

    	//===============================================================
    	// Disable Method
    	//===============================================================
        $this->disabled_methods[$method] = $method;
        return true;
	}

	//***********************************************************************
	//***********************************************************************
	// Enable a method for executing
	//***********************************************************************
	//***********************************************************************
	public function enable_method($method)
	{
    	//===============================================================
    	// Validate Parameters
    	//===============================================================
    	$tmp_type = gettype($method);
        if (!$method || is_numeric($method) || in_array($tmp_type, ['array', 'object'])) {
            return false;
        }

    	//===============================================================
    	// Enable Method
    	//===============================================================
    	if (isset($this->disabled_methods[$method])) {
        	unset($this->disabled_methods[$method]);
        	return 1;
    	}

        return true;
	}

	//***********************************************************************
	//***********************************************************************
	// Get disabled methods
	//***********************************************************************
	//***********************************************************************
	public function disabled_methods()
	{
        return $this->disabled_methods;
    }

	//***********************************************************************
	//***********************************************************************
	// Is a method disabled?
	//***********************************************************************
	//***********************************************************************
	public function method_disabled($method)
	{
    	//===============================================================
    	// Validate Parameters
    	//===============================================================
    	$tmp_type = gettype($method);
        if (!$method || is_numeric($method) || in_array($tmp_type, ['array', 'object'])) {
            return null;
        }

    	//===============================================================
    	// Is method disabled?
    	//===============================================================
    	if (isset($this->disabled_methods[$method])) {
        	return true;
        }

        return false;
    }

	//***********************************************************************
	//***********************************************************************
	// Build a where clause from primary keys
	//***********************************************************************
	//***********************************************************************
	private function build_where(&$pkey_values)
	{
        $strsql = '';

		//===============================================================
		// If string passed transform into an array
		//===============================================================
        if (!is_array($pkey_values)) {
            if (!$this->primary_key) {
                $msg = 'Unable to build SQL where clause. Primary key(s) or indexes are not set or are invalid.';
                $this->trigger_error(__METHOD__, $msg);
                return false;
            }
            else if (!is_array($this->primary_key)) {
    	        $pkey_values = array($this->primary_key => $pkey_values);
            }
            else {
                $msg = 'Unable to build SQL where clause. Invalid primary key(s) or indexes given.';
                $this->trigger_error(__METHOD__, $msg);
                return false;
            }
	    }

		//===============================================================
        // Build where clause
		//===============================================================
		foreach ($pkey_values as $key => &$value) {

			//-------------------------------------------------
            // Check that field name is NOT numeric
			//-------------------------------------------------
            if (is_numeric($key)) {
                $msg = 'Unable to build SQL where clause. A primary key or index was found with an invalid name.';
                $this->trigger_error(__METHOD__, $msg);
                return false;
            }

			//-------------------------------------------------
            // Prepend And?
			//-------------------------------------------------
            if ($strsql) {
        		$strsql .= ' and';
			}

			//-------------------------------------------------
			// Bind Parameters
			//-------------------------------------------------
			if ($this->use_bind_params) {

				switch ($this->db_type) {

	                case 'mysqli':
	                	$strsql .= " {$key} = ?";
	                	$this->bind_param_count++;
	                	$tmp_type = \phpOpenFW\Database\Structure\DatabaseType\MySQL::GetBindType($this->table_info[$key]['data_type']);
	                	$this->bind_params[0] .= $tmp_type;
	                	$this->bind_params[] = &$value;
	                    break;

	                case 'pgsql':
	                	$this->bind_param_count++;
	                	$tmp_param = '$' . $this->bind_param_count;
	                	$strsql .= " {$key} = {$tmp_param}";
	                	$this->bind_params[] = $value;
	                    break;

					case 'oracle':
	                	$this->bind_param_count++;
						$tmp_param = ':p' . $this->bind_param_count;
	                	$strsql .= " {$key} = {$tmp_param}";
	                	$this->bind_params[$tmp_param] = $value;
						break;

					case 'sqlite':
						break;

					case 'db2':
					case 'sqlsrv':
	                	$strsql .= " {$key} = ?";
	                	$this->bind_params[] = $value;
	                	$this->bind_param_count++;
						break;

	            }
			}
			//-------------------------------------------------
			// No Bind Parameters
			//-------------------------------------------------
			else if (isset($this->quoted_types[$this->table_info[$key]['data_type']])) {
				$strsql .= " {$key} = '{$value}'";
			}
			else {
				$strsql .= " {$key} = {$value}";
			}
        }

        //===============================================================
        // Check if where clause was generated...
        // If not, return false
        //===============================================================
        if (!$strsql) {
            return false;
        }

        //===============================================================
        // Valid where clause, return it.
        //===============================================================
        return ' where' . $strsql;
	}

	//***********************************************************************
	//***********************************************************************
	// Reset Method
	//***********************************************************************
	//***********************************************************************
	public function reset()
	{
        $this->data = array();
        $this->reset_bind_vars();
	}

	//***********************************************************************
	//***********************************************************************
	// Set / Reset Bind Parameter Variables
	//***********************************************************************
	//***********************************************************************
	private function reset_bind_vars()
	{
        $this->bind_params = [];
        if ($this->db_type == 'mysqli') {
    		$this->bind_params[0] = '';
        }
        $this->bind_param_count = 0;
	}

	//***********************************************************************
	//***********************************************************************
	// Set Use of Bind Parameters
	//***********************************************************************
	//***********************************************************************
	public function use_bind_params($flag=true)
	{
		$this->use_bind_params = (bool)$flag;
		$this->reset_bind_vars();
	}

	//***********************************************************************
	//***********************************************************************
	// Build Full Table Name
	//***********************************************************************
	//***********************************************************************
	public function full_table_name()
	{
        if (isset($this->schema)) {
	        return "{$this->schema}{$this->schema_separator}{$this->table}";
	    }
        else {
	        return $this->table;
	    }
    }

	//***********************************************************************
	//***********************************************************************
	// Get Structural Table Information
	//***********************************************************************
	//***********************************************************************
	public function get_table_info()
	{
		return $this->table_info;
	}

	//***********************************************************************
	//***********************************************************************
	// Get unset fields
	//***********************************************************************
	//***********************************************************************
	public function get_unset_fields()
	{
	   return (isset($this->unset_fields)) ? ($this->unset_fields) : (false);
	}

	//***********************************************************************
	//***********************************************************************
	// Get field data
	//***********************************************************************
	//***********************************************************************
	public function get_field_data($field)
	{
		if (isset($this->table_info[$field])) {
			if (isset($this->data[$field])) {
    			return $this->data[$field];
            }
			else {
    			return false;
            }
		}
		else {
			$this->trigger_error(__METHOD__, "Field '{$field}' does not exist.");
			return false;
		}
	}

	//***********************************************************************
	//***********************************************************************
	// Get Last Query
	//***********************************************************************
	//***********************************************************************
	public function get_last_query()
	{
		return $this->last_query;
	}

	//***********************************************************************
	//***********************************************************************
	// Class Method for Triggering an Error
	//***********************************************************************
	//***********************************************************************
	public function trigger_error($method, $msg, $error_type=E_USER_ERROR)
	{
		trigger_error("[{$this->class_name}]::{$method}(): {$msg}", $error_type);
	}

	//***********************************************************************
	//***********************************************************************
	// Dump Information
	//***********************************************************************
	//***********************************************************************
	public function dump($type=false)
	{
	   print "<pre>\n";
	   switch ($type) {
	       case 'data':
	           print_r($this->data);
	           break;

	       default:
	           print_r($this->table_info);
	           break;
	   }
	   print "</pre>\n";
	}

	//***********************************************************************
	//***********************************************************************
	/**
	 * Set Charset
	 * @param string $str Example: utf8
	 **/
	//***********************************************************************
	//***********************************************************************
	public function set_charset($charset)
	{
	    $this->charset = $charset;
	}

}
