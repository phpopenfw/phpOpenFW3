<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Data Result / Microsoft SQL Server Plugin
 * A Microsoft SQL Server plugin to the (data_result) class
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
 * dr_mssql Class
 */
//**************************************************************************************
class dr_mssql extends dr_structure
{

	//*************************************************************************
	/**
	* Set the Number of Rows in the current result set
	**/
	//*************************************************************************
	public function set_num_rows()
	{
		$this->num_recs = mssql_num_rows($this->resource);
	}

	//*************************************************************************
	/**
	* Set the Number of Fields in the current result set
	**/
	//*************************************************************************
	public function set_num_fields()
	{
		$this->num_fields = mssql_num_fields($this->resource);
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
		if ($this->num_rows()) {
			return mssql_data_seek($this->resource, $offset);
		}
		return false;
	}

	//*************************************************************************
	/**
	* Fetch a row from the result set
	**/
	//*************************************************************************
	public function fetch_row()
	{
		return mssql_fetch_assoc($this->resource);
	}

}

