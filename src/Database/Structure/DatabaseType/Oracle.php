<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Oracle Database Structure Class
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
 * Oracle Structure Class
 */
//**************************************************************************************
class Oracle
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
        // Build SQL Query to Pull Table Structure Data
        //=======================================================================
		$tmp_tbl = strtoupper($table);
		$strsql = "
		    select * from 
		        ALL_TAB_COLUMNS 
            where 
                table_name = '{$tmp_tbl}'
        ";

        //=======================================================================
        // Pull Table Data
        //=======================================================================
        $data1 = new DataTrans($data_source);
		$data1->data_query($strsql);
        $meta_data = $data1->data_assoc_result();

        //=======================================================================
        // Format Table Data
        //=======================================================================
        foreach ($meta_data as $field) {
            $table_info[$field['COLUMN_NAME']] = array();
            $table_info[$field['COLUMN_NAME']]['data_type'] = $field['DATA_TYPE'];
            $table_info[$field['COLUMN_NAME']]['length'] = $field['DATA_LENGTH'];
            $table_info[$field['COLUMN_NAME']]['nullable'] = (strtoupper($field['NULLABLE']) == 'YES') ? (1) : (0);
            $table_info[$field['COLUMN_NAME']]['load_default'] = $field['DATA_DEFAULT'];
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
	/**
	 * Return the column data types that require quotes for this database type
	 *
	 * @return array An array of column types that require quotes (non-bind parameters)
	 */
	//**********************************************************************************
	public static function QuotedTypes()
	{
        return [
        	'CHAR' => 'CHAR',
        	'NCHAR' => 'NCHAR',
        	'VARCHAR' => 'VARCHAR',
        	'VARCHAR2' => 'VARCHAR2',
        	'DATE' => 'DATE',
        	'TIMESTAMP' => 'TIMESTAMP',
        	'CLOB' => 'CLOB',
        	'NCLOB' => 'NCLOB'
        ];
    }

}
