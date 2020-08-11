<?php
//**************************************************************************************
//**************************************************************************************
/**
 * PostgreSQL Database Structure Class
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
 * PgSQL Structure Class
 */
//**************************************************************************************
class PgSQL
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
        // Set Table and Schema
        //=======================================================================
        $database = $ds_data['source'];
        $tmp = Structure\Table::DetermineSchema($data_source, $table);
        $table = $tmp['table'];
        $schema = (!$tmp['schema'] && $schema) ? ($schema) : ($tmp['schema']);

        //=======================================================================
        // Build SQL Query to Pull Table Structure Data
        //=======================================================================
        $strsql = "
            SELECT * FROM 
                information_schema.columns
            WHERE 
                table_catalog = '{$database}'
        ";
        if (!empty($schema)) {
            $strsql .= " and table_schema = '{$schema}'";
        };
        $strsql .= " and table_name = '{$table}' order by ordinal_position";

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
            $table_info[$field['column_name']] = array();
            $table_info[$field['column_name']]['data_type'] = $field['udt_name'];
            $table_info[$field['column_name']]['length'] = $field['character_maximum_length'];
            $table_info[$field['column_name']]['nullable'] = (strtoupper($field['is_nullable']) == 'YES') ? (1) : (0);
            $table_info[$field['column_name']]['load_default'] = $field['column_default'];
            $table_info[$field['column_name']]['no_save'] = false;
            $table_info[$field['column_name']]['no_load'] = false;
            $table_info[$field['column_name']]['quotes'] = 'auto';
            $table_info[$field['column_name']]['can_bind_param'] = true;
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
        	'date' => 'date',
        	'text' => 'text',
        	'varchar' => 'varchar',
        	'time' => 'time',
        	'timestamp' => 'timestamp',
        	'xml' => 'xml'
        ];
    }

}
