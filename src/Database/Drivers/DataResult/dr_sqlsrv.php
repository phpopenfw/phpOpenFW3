<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Data Result / Microsoft SQL Server Plugin for Microsoft's SQLSRV Driver
 * A Microsoft SQL Server (SQLSRV) plugin to the (data_result) class
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
 * dr_sqlsrv Class
 */
//**************************************************************************************
class dr_sqlsrv extends dr_structure
{

	//*************************************************************************
	/**
	* Set the Number of Rows in the current result set
	**/
	//*************************************************************************
	public function set_num_rows()
	{
		$this->num_recs = sqlsrv_num_rows($this->resource);
	}

	//*************************************************************************
	/**
	* Set the Number of Fields in the current result set
	**/
	//*************************************************************************
	public function set_num_fields()
	{
		$this->num_fields = sqlsrv_num_fields($this->resource);
	}

	//*************************************************************************
	/**
	* Fetch a row from the result set
	**/
	//*************************************************************************
	public function fetch_row()
	{
		if ($this->flags['fetch_all_rows']) {
			return false;
		}

		if ($this->num_fields) {
			$this->flags['fetch_row']++;
			return sqlsrv_fetch_array($this->resource, SQLSRV_FETCH_ASSOC, SQLSRV_SCROLL_NEXT);
		}

		return false;
	}

	//*************************************************************************
	/**
	* Fetch all rows in a result
	**/
	//*************************************************************************
	public function fetch_all_rows()
	{
		return $this->fetch_all_rows2();
	}
}

