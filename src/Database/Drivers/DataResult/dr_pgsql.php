<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Data Result / PostgreSQL Plugin
 * A PostgreSQL plugin to the (data_result) class
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
 * dr_pgsql Class
 */
//**************************************************************************************
class dr_pgsql extends dr_structure
{

	//*************************************************************************
	/**
	* Set the Number of Rows in the current result set
	**/
	//*************************************************************************
	public function set_num_rows()
	{
		$this->num_recs = pg_num_rows($this->resource);
	}

	//*************************************************************************
	/**
	* Set the Number of Fields in the current result set
	**/
	//*************************************************************************
	public function set_num_fields()
	{
		$this->num_fields = pg_num_fields($this->resource);
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
		return pg_result_seek($this->resource, $offset);
	}

	//*************************************************************************
	/**
	* Fetch a row from the result set
	**/
	//*************************************************************************
	public function fetch_row()
	{
		return pg_fetch_assoc($this->resource);
	}

}

