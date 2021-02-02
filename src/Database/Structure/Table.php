<?php
//******************************************************************************
//******************************************************************************
/**
 * Database Table Structure Class
 *
 * @package         phpOpenFW
 * @author          Christian J. Clark
 * @copyright       Copyright (c) Christian J. Clark
 * @license         https://mit-license.org
 */
//******************************************************************************
//******************************************************************************

namespace phpOpenFW\Database\Structure;

//******************************************************************************
/**
 * Table Structure Class
 */
//******************************************************************************
class Table
{
    //==========================================================================
    // Class Members
    //==========================================================================
    protected $ds_obj = false;
    protected $table = false;
    protected $db_type_obj = false;

    //**************************************************************************
    //**************************************************************************
    // Get Instance
    //**************************************************************************
    //**************************************************************************
    public static function Instance($data_source, $table)
    {
        //======================================================================
        // Return New DataSources Object
        //======================================================================
        return new static($data_source, $table);
    }

    //**************************************************************************
    //**************************************************************************
    // Constructor function
    //**************************************************************************
    //**************************************************************************
    public function __construct($data_source, $table)
    {
        $this->ds_obj = \phpOpenFW\Core\DataSources::GetOneOrDefault($data_source);
        if (!$table) {
            throw new \Exception('Invalid or no table name given.');
        }
        $this->table = $table;
        $this->db_type_obj = $this->ds_obj->GetDatabaseTypeObject();
    }

    //**************************************************************************
    //**************************************************************************
    /**
     * Get the structural information for this database table
     *
     * @return array Table Structure
     */
    //**************************************************************************
    //**************************************************************************
    public function GetStructure()
    {
        $tmp = $this->DetermineSchema();
        return call_user_func_array([$this->db_type_obj, 'TableStructure'], [$this->ds_obj, $tmp['table'], $tmp['schema']]);
    }

    //**************************************************************************
    //**************************************************************************
    /**
     * Determine the schema from the table name
     *
     * @return array An Array containing the table name and schema found.
     */
    //**************************************************************************
    //**************************************************************************
    public function DetermineSchema()
    {
        return call_user_func_array([$this->db_type_obj, 'DetermineSchema'], [$this->ds_obj, $this->table]);
    }

}
