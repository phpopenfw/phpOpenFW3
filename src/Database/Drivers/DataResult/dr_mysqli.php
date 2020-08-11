<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Data Result / MySQL Improved (mysqli) Plugin
 * A MySQLi plugin to the (data_result) class
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 * @access		private
 */
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\Database\Drivers\DataResult;

//**************************************************************************************
/**
 * dr_mysqli Class
 */
//**************************************************************************************
class dr_mysqli extends dr_structure
{

	//*************************************************************************
	// Constructor function
	//*************************************************************************
	protected function pre_setup()
	{
		if (method_exists($this->stmt, 'get_result')) {
			$this->set_opt('mysqlnd', 1);
		}
		
		if (!$this->get_opt('mysqlnd') && $this->get_opt('prepared_query')) {
			$meta_data = $this->stmt->result_metadata();
		    $this->fields = array();
		    $this->row = array();
		    $count = 0;
		    while ($field = $meta_data->fetch_field()) {
		        $this->fields[$count] = &$this->row[$field->name];
		        $count++;
		    }	       
		    call_user_func_array(array($this->stmt, 'bind_result'), $this->fields);
		}
	}

	//*************************************************************************
	/**
	* Set the Number of Rows in the current result set
	**/
	//*************************************************************************
	public function set_num_rows()
	{
		$this->num_recs = $this->resource->num_rows;
	}

	//*************************************************************************
	/**
	* Set the Number of Fields in the current result set
	**/
	//*************************************************************************
	public function set_num_fields()
	{
		if (!$this->get_opt('mysqlnd') && $this->get_opt('prepared_query')) {
			$this->num_fields = count($this->fields);
		}
		else {
			$this->num_fields = $this->resource->field_count;
		}
	}

	//*************************************************************************
	/**
	* Set Result Pointer
	*
	* @param integer The numeric position to set the pointer at.
	**/
	//*************************************************************************
	public function set_result_pointer($offset=0)
	{
		return $this->resource->data_seek($offset);
	}

	//*************************************************************************
	/**
	* Fetch a row from the result set
	**/
	//*************************************************************************
	public function fetch_row()
	{
		if (!$this->get_opt('mysqlnd') && $this->get_opt('prepared_query')) {
			$fetch_status = $this->resource->fetch();
			if ($fetch_status) {
				$new_row = array();
			    foreach ($this->row as $key => $value) { $new_row[$key] = $value; }
			    return $new_row;
			}
			return $fetch_status;
		}
		else {
			return $this->resource->fetch_assoc();
		}
	}

}

