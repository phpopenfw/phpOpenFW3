<?php
//**************************************************************************************
//**************************************************************************************
/**
 * IBM DB2 Database Structure Class
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
 * DB2 Structure Class
 */
//**************************************************************************************
class DB2
{
	//**********************************************************************************
    // Traits
	//**********************************************************************************
    use Core;

	//**********************************************************************************
	/**
	 * Get the structure for a given database table
	 *
	 * @param string Data source handle
	 * @param string Table name
	 * @param string Schema name
	 * @return array Table Structure
	 */
	//**********************************************************************************
	public static function TableStructure($data_source, $table, $schema=false)
	{
        //=======================================================================
        // Validate Data Source and Table
        //=======================================================================
        if (!$ds_data = self::ValidateDataSource($data_source)) {
            return false;
        }
        if ($table == '') {
            trigger_error('Invalid table name.');
            return false;
        }

        //=======================================================================
        // Start Table Info
        //=======================================================================
    	$table_info = [];

        //=======================================================================
        // Determine Table and Schema
        //=======================================================================
        $tmp = Structure\Table::DetermineSchema($data_source, $table);
        $table = $tmp['table'];
        $schema = (!$tmp['schema'] && $schema) ? ($schema) : ($tmp['schema']);

        //=======================================================================
        // Check for BOTH Table AND Schema
        //=======================================================================
		if (!$table || !$schema) {
			trigger_error('Table and schema must be specified in the format of [SCHEMA]/[TABLE]');
			return false;
		}

        //=======================================================================
        // Build SQL Query to Pull Table Structure Data
        //=======================================================================
		$strsql = "
			SELECT
				*
			FROM
				QSYS2/SYSCOLUMNS
			WHERE
				TABLE_NAME = '{$table}'
				and TABLE_SCHEMA = '{$schema}'
		";

        //=======================================================================
        // Pull Table Data
        //=======================================================================
		$data1 = new DataTrans($data_source);
		$data1->data_query($strsql);
        $meta_data = rs_trim($data1->data_assoc_result(), true, true);

        //=======================================================================
        // Format Table Data
        //=======================================================================
        foreach ($meta_data as $field) {
            $table_info[$field['COLUMN_NAME']] = array();
            $table_info[$field['COLUMN_NAME']]['data_type'] = $field['DATA_TYPE'];
            $table_info[$field['COLUMN_NAME']]['length'] = $field['LENGTH'];
            $table_info[$field['COLUMN_NAME']]['nullable'] = (strtoupper($field['IS_NULLABLE']) == 'Y') ? (1) : (0);
            $table_info[$field['COLUMN_NAME']]['load_default'] = (strtoupper($field['HAS_DEFAULT']) == 'Y') ? ($field['COLUMN_DEFAULT']) : ('');
			$load_def = &$table_info[$field['COLUMN_NAME']]['load_default'];
			if ($load_def[0] == "'") { $load_def = substr($load_def, 1); }
			if ($load_def[strlen($load_def) - 1] == "'") { $load_def = substr($load_def, 0, strlen($load_def) - 1); }
			$load_def = trim($load_def);
            $table_info[$field['COLUMN_NAME']]['no_save'] = false;
            $table_info[$field['COLUMN_NAME']]['no_load'] = false;
            $table_info[$field['COLUMN_NAME']]['quotes'] = 'auto';
            $table_info[$field['COLUMN_NAME']]['can_bind_param'] = true;
		}

        //=======================================================================
        // Return Table Structure
        //=======================================================================
        return $table_info;
    }

	//**********************************************************************************
	//**********************************************************************************
    // Determine Schema from a Table
	//**********************************************************************************
	//**********************************************************************************
    public static function DetermineSchema($data_source, $table, $default=false, $separator=false)
	{
        return Core::DetermineSchema($data_source, $table, $default, '/');
    }

	//**********************************************************************************
	/**
	 * Return the column data types that require quotes for this database type
	 *
	 * @return array An array of column types that require quotes (non-bind parameters)
	 */
	//**********************************************************************************
	public static function QuotedTypes()
	{
        return [
        	'CHARACTER' => 'CHARACTER',
        	'VARCHAR' => 'VARCHAR',
        	'DATE' => 'DATE',
        	'TIME' => 'TIME',
        	'TIMESTAMP' => 'TIMESTAMP'
		];
    }

}
