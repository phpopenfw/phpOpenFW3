<?php
//*****************************************************************************
//*****************************************************************************
/**
 * Data Result Class
 * A data result abstraction class used to handle database results.
 *
 * @package         phpopenfw/phpopenfw3/phpopenfw3
 * @author          Christian J. Clark
 * @copyright       Copyright (c) Christian J. Clark
 * @website         https://phpopenfw.org
 * @license         https://mit-license.org
 */
//*****************************************************************************
//*****************************************************************************

namespace phpOpenFW\Database;

//*****************************************************************************
/**
 * Data Result Class
 */
//*****************************************************************************
class DataResult
{

    //************************************************************************    
    // Class variables
    //************************************************************************
    /**
    * @var string Data source type (mysql, mysqli, pgsql, oracle, mssql, db2, sqlsrv, sqlite)
    **/
    private $data_type;

    /**
    * @var Object Internal Data Object 
    **/
    private $data_object;

    //*************************************************************************
    /**
    * Constructor function
    *
    * Initializes data result object
    *
    * @param mixed The result resource id or object
    * @param string A valid data source handle as specified in the configuration
    **/
    //*************************************************************************
    public function __construct($resource, $data_src='', $opts=false)
    {
        //=====================================================================
        // Get Data Source
        //=====================================================================
        $ds_obj = \phpOpenFW\Core\DataSources::GetOneOrDefault($data_src);

        //=====================================================================
        // Create Object based on Data Source Type
        //=====================================================================
        $this->data_type = $ds_obj->type;
        $dr_class = '\phpOpenFW\Database\Drivers\DataResult\dr_' . $this->data_type;
        $this->data_object = new $dr_class($resource, $ds_obj, $opts);

        //=====================================================================
        // Check if we are setting the character set
        //=====================================================================
        if (!empty($ds_obj->charset)) {
            $this->data_object->set_opt('charset', $ds_obj->charset);
        }

        //=====================================================================
        // Return newly created object
        //=====================================================================
        return $this->data_object;
    }

    //*************************************************************************
    /**
    * Destructor Function
    **/
    //*************************************************************************
    public function __destruct() {}

    //*************************************************************************
    /**
    * Display Error Function
    **/
    //*************************************************************************
    private function display_error($function, $msg)
    {
        $class = __CLASS__;
        trigger_error("Error: [{$class}]::{$function}(): {$msg}");
    }

    //*************************************************************************
    /**
    * Get the number records in a result set. A False value means the value could not be determined.
    **/
    //*************************************************************************
    public function num_rows()
    {
        return $this->data_object->num_rows();
    }

    //*************************************************************************
    /**
    * Get the number fields in a result set. A False value means the value could not be determined.
    **/
    //*************************************************************************
    public function num_fields()
    {
        return $this->data_object->num_fields();
    }

    //*************************************************************************
    /**
    * Fetch all rows in a result
    **/
    //*************************************************************************
    public function fetch_all_rows()
    {
        return $this->data_object->fetch_all_rows();
    }

    //*************************************************************************
    /**
    * Fetch a row from the result set
    **/
    //*************************************************************************
    public function fetch_row()
    {
        return $this->data_object->fetch_row();
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
        settype($offset, 'int');
        return $this->data_object->set_result_pointer($offset);
    }

    //*************************************************************************
    /**
    * Get Transaction Option
    * @param string Option Key
    * @return The value of the option or false if it does not exist
    **/
    //*************************************************************************
    public function get_opt($opt)
    {
        return $this->data_object->get_opt($opt);
    }

    //*************************************************************************
    /**
    * Set Transaction Option
    * @param string Option Key
    * @param string Option Value
    **/
    //*************************************************************************
    public function set_opt($opt, $val=false)
    {
        return $this->data_object->set_opt($opt, $val);
    }

    //*************************************************************************
    /**
    * Get Raw Resource
    * @return The raw resource or statement handle returned from the query.
    **/
    //*************************************************************************
    public function get_raw_resource()
    {
        return $this->data_object->get_raw_resource();
    }

}
