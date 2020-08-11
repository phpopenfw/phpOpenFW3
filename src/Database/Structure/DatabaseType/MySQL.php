<?php
//**************************************************************************************
//**************************************************************************************
/**
 * MySQL Database Structure Class
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
 * MySQL Structure Class
 */
//**************************************************************************************
class MySQL
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
        $strsql = "SHOW COLUMNS FROM {$table}";

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
            $table_info[$field['Field']] = array();
            $fld_type = explode('(', $field['Type']);
            if (count($fld_type) > 1) {
                $table_info[$field['Field']]['data_type'] = $fld_type[0];
                if ($fld_type[0] != 'enum') {
                    $table_info[$field['Field']]['length'] = substr($fld_type[1], 0, strlen($fld_type[1]) - 1);
                }
            }
            else {
                $table_info[$field['Field']]['data_type'] = $field['Type'];
                $table_info[$field['Field']]['length'] = NULL;
            }
            $table_info[$field['Field']]['nullable'] = (strtoupper($field['Null']) == 'YES') ? (1) : (0);
            $table_info[$field['Field']]['load_default'] = $field['Default'];
            $table_info[$field['Field']]['no_save'] = false;
            $table_info[$field['Field']]['no_load'] = false;
            $table_info[$field['Field']]['quotes'] = 'auto';
            $table_info[$field['Field']]['can_bind_param'] = true;
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
        	'tinytext' => 'tinytext',
        	'mediumtext' => 'mediumtext',
        	'longtext' => 'longtext',
        	'varchar' => 'varchar',
        	'enum' => 'enum',
        	'timestamp' => 'timestamp',
        	'datetime' => 'datetime',
        	'time' => 'time',
        	'year' => 'year'
        ];
    }

	//**********************************************************************************
	/**
	 * Return the bind types map for MySQL field types
	 *
	 * @return Array The field type to bind type mapping array
	 */
	//**********************************************************************************
	public static function BindTypes()
	{
        return [
        	//-------------------------------------------------
        	// Integer
        	//-------------------------------------------------
        	'TINYINT' => 'i',
        	'SMALLINT' => 'i',
        	'MEDIUMINT' => 'i',
        	'INT' => 'i',
        	'BIGINT' => 'i',
        
        	'BIT' => 'i',
        	'BOOL' => 'i',
        	'SERIAL' => 'i',
        
        	//-------------------------------------------------
        	// Double
        	//-------------------------------------------------
        	'DECIMAL' => 'd',
        	'FLOAT' => 'd',
        	'DOUBLE' => 'd',
        	'REAL' => 'd',
        
        	//-------------------------------------------------
        	// Blob
        	//-------------------------------------------------
        	'TINYBLOB' => 'b',
        	'MEDIUMBLOB' => 'b',
        	'BLOB' => 'b',
        	'LONGBLOB' => 'b'
        ];
    }

	//**********************************************************************************
	/**
	 * Return the bind type character for a given MySQL field type
	 *
	 * @param string The field type to get a bind character for
	 * @return character The bind type character
	 */
	//**********************************************************************************
	public static function GetBindType($field_type)
	{
    	if (!$field_type) {
        	return false;
        }
    	$field_type = strtoupper($field_type);
        $bind_types = self::BindTypes();
        return (isset($bind_types[$field_type])) ? ($bind_types[$field_type]) : ('s');
    }

}
