<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Database Table Structure Class
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 */
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\Database\Structure;
use phpOpenFW\Database\DataTrans;

//**************************************************************************************
/**
 * Table Structure Class
 */
//**************************************************************************************
class Table
{

	//**************************************************************************************
	/**
	 * Get the structural information for a given database table
	 *
	 * @param string Data source handle
	 * @param string Table name
	 * @param string Schema name
	 * @return array Table Structure
	 */
	//**************************************************************************************
	public static function TableStructure($data_source, $table, $schema=false)
	{
        //=======================================================================
        // Validate Data Source
        //=======================================================================
        $ds_data = \phpOpenFW\Framework\Core\DataSources::GetOne($data_source);
        if (!$ds_data) {
            trigger_error('Invalid data source.');
            return false;
        }

        //=======================================================================
        // Get Table Structure Based on Database Type
        //=======================================================================
        switch ($ds_data['type']) {
            case 'mysql':
            case 'mysqli':
                return DatabaseType\MySQL::TableStructure($data_source, $table, $schema);
                break;

            case 'pgsql':
                return DatabaseType\PgSQL::TableStructure($data_source, $table, $schema);
                break;

			case 'oracle':
                return DatabaseType\Oracle::TableStructure($data_source, $table, $schema);
				break;

			case 'sqlsrv':
			case 'mssql':
                return DatabaseType\SQLSrv::TableStructure($data_source, $table, $schema);
				break;

			case 'sqlite':
			    return DatabaseType\SQLite::TableStructure($data_source, $table, $schema);
				break;

			case 'db2':
                return DatabaseType\DB2::TableStructure($data_source, $table, $schema);
				break;

        }

        return false;
    }

	//**************************************************************************************
	/**
	 * Determine a schema from a table name
	 *
	 * @param string Data Source Handle
	 * @param string Table Name
	 * @return array An Array containing the table name and schema found.
	 */
	//**************************************************************************************
	public static function DetermineSchema($data_source, $table)
	{
        //=======================================================================
        // Validate Data Source
        //=======================================================================
        $ds_data = \phpOpenFW\Framework\Core\DataSources::GetOne($data_source);
        if (!$ds_data) {
            trigger_error('Invalid data source.');
            return false;
        }

        //=======================================================================
        // Get Table and Schema Based on Database Type
        //=======================================================================
        switch ($ds_data['type']) {
            case 'mysql':
            case 'mysqli':
                return DatabaseType\MySQL::DetermineSchema($data_source, $table);
                break;

            case 'pgsql':
                return DatabaseType\PgSQL::DetermineSchema($data_source, $table);
                break;

			case 'oracle':
                return DatabaseType\Oracle::DetermineSchema($data_source, $table);
				break;

			case 'sqlsrv':
			case 'mssql':
                return DatabaseType\SQLSrv::DetermineSchema($data_source, $table);
				break;

			case 'sqlite':
			    return DatabaseType\SQLite::DetermineSchema($data_source, $table);
				break;

			case 'db2':
                return DatabaseType\DB2::DetermineSchema($data_source, $table);
				break;

        }

        return false;
    }

	//**************************************************************************************
	/**
	 * Get the column data types that require quotes for a specified database type
	 *
	 * @param string Database type or data source handle
	 * @param string Strict mode (true / false). If false, check if $db_type is a data source handle
	 * @return array An array of column types that require quotes (non-bind parameters)
	 */
	//**************************************************************************************
	public static function QuotedTypes($db_type, $strict=false)
	{
        //=======================================================================
        // Cast $db_type as string and validate
        //=======================================================================
        settype($db_type, 'string');
        if (!$db_type) { return false; }

        //=======================================================================
        // Return Quoted Column Types based on Database Type
        //=======================================================================
        switch ($db_type) {

            //-------------------------------------------------------------
            // MySQL
            //-------------------------------------------------------------
            case 'mysqli':
            case 'mysql':
                return DatabaseType\MySQL::QuotedTypes();
                break;

            //-------------------------------------------------------------
            // PostgreSQL
            //-------------------------------------------------------------
            case 'pgsql':
                return DatabaseType\PgSQL::QuotedTypes();
                break;

            //-------------------------------------------------------------
            // Oracle
            //-------------------------------------------------------------
            case 'oracle':
                return DatabaseType\Oracle::QuotedTypes();
                break;

            //-------------------------------------------------------------
            // SQL Server
            //-------------------------------------------------------------
            case 'sqlsrv':
            case 'mssql':
                return DatabaseType\SQLSrv::QuotedTypes();
                break;

            //-------------------------------------------------------------
            // SQLite
            //-------------------------------------------------------------
            case 'sqlite':
                return DatabaseType\SQLite::QuotedTypes();
                break;

            //-------------------------------------------------------------
            // IBM DB2
            //-------------------------------------------------------------
        	case 'db2':
        	    return DatabaseType\DB2::QuotedTypes();
                break;

        }

        //=======================================================================
        // Unknown Database Type
        //=======================================================================
        // If not strict mode, try to interpret as data source handle
        //=======================================================================
        if (!$strict) {
            if ($data_source = \phpOpenFW\Framework\Core\DataSources::GetOne($db_type)) {
                return self::QuotedTypes($data_source['type'], true);
            }
        }

        return false;
    }

}
