<?php
//**************************************************************************************
//**************************************************************************************
/**
 * MS SQL Server Database Structure Class
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
 * SQLSrv Structure Class
 */
//**************************************************************************************
class SQLSrv
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
        $database = $ds_data['source'];
        $tmp = Structure\Table::DetermineSchema($data_source, $table);
        $table = $tmp['table'];
        $schema = (!$tmp['schema'] && $schema) ? ($schema) : ($tmp['schema']);

        //=======================================================================
        // Build SQL Query to Pull Table Structure Data
        //=======================================================================
		$strsql = "
		    select * from 
		        information_schema.columns 
            where 
                table_name = '{$table}'
        ";
		if (!empty($schema)) {
    		$strsql .= " and table_schema = '{$schema}'";
        }

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
            $table_info[$field['COLUMN_NAME']]['length'] = $field['CHARACTER_MAXIMUM_LENGTH'];
            $table_info[$field['COLUMN_NAME']]['nullable'] = (strtoupper($field['IS_NULLABLE']) == 'YES') ? (1) : (0);
            $table_info[$field['COLUMN_NAME']]['load_default'] = $field['COLUMN_DEFAULT'];
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
        	'char' => 'char',
        	'varchar' => 'varchar',
        	'text' => 'text',
        	'nchar' => 'nchar',
        	'nvarchar' => 'nvarchar',
        	'ntext' => 'ntext',
        	'date' => 'date',
        	'datetimeoffset' => 'datetimeoffset',
        	'datetime' => 'datetime',
        	'datetime2' => 'datetime2',
        	'smalldatetime' => 'smalldatetime',
        	'time' => 'time',
        	'xml' => 'xml'
		];
    }

}
