<?php
//**************************************************************************************
//**************************************************************************************
/**
 * SQLite Database Structure Class
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 */
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\Database\Structure\DatabaseType;
use phpOpenFW\Database\DataTrans;

//**************************************************************************************
/**
 * SQLite Structure Class
 */
//**************************************************************************************
class SQLite
{

	//**************************************************************************************
	/**
	 * Get the structure for a given database table
	 *
	 * @param string Data source handle
	 * @param string Table name
	 * @return array Table Structure
	 */
	//**************************************************************************************
	public static function TableStructure($data_source, $table)
	{
        return false;
    }

	//**************************************************************************************
	/**
	 * Return the column data types that require quotes for this database type
	 *
	 * @return array An array of column types that require quotes (non-bind parameters)
	 */
	//**************************************************************************************
	public static function QuotedTypes()
	{
        return [
			'TEXT' => 'TEXT'
		];
    }

}
