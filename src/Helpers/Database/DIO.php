<?php
//*****************************************************************************
//*****************************************************************************
/**
 * Database Interface Object Helper Object
 *
 * @package         phpopenfw/phpopenfw3/phpopenfw3
 * @author          Christian J. Clark
 * @copyright       Copyright (c) Christian J. Clark
 * @website         https://phpopenfw.org
 * @license         https://mit-license.org
 **/
//*****************************************************************************
//*****************************************************************************

namespace phpOpenFW\Helpers\Database;

//*****************************************************************************
/**
 * DIO Class
 */
//*****************************************************************************
class DIO
{

    //*************************************************************************
    //*************************************************************************
    // Set DIO Field to NULL
    //*************************************************************************
    //*************************************************************************
    public static function set_field_null(&$obj, $field_name)
    {
        $obj->set_field_quotes($field_name, 'disable');
        $obj->set_field_data($field_name, 'NULL');
        $obj->set_use_bind_param($field_name, false);
    }

    //*************************************************************************
    //*************************************************************************
    // Set DIO Field to Current Date/Time
    //*************************************************************************
    //*************************************************************************
    public static function set_field_current_dttm(&$obj, $field_name)
    {
        $obj->set_field_quotes($field_name, 'disable');
        $obj->set_field_data($field_name, 'NOW()');
        $obj->set_use_bind_param($field_name, false);
    }

    //*************************************************************************
    //*************************************************************************
    // Save DIO Record Function
    //*************************************************************************
    //*************************************************************************
    public static function save_record($obj_name, $data, $pkey=false, $args=false)
    {
        //------------------------------------------------------
        // Transaction Type
        //------------------------------------------------------
        $trans_type = (empty($pkey)) ? ('add') : ('update');
    
        //------------------------------------------------------
        // Validate that Data is an array
        //------------------------------------------------------
        if (!is_array($data)) { return false; }
    
        //------------------------------------------------------
        // Optional Parameters / Arguments
        //------------------------------------------------------
        if (is_array($args)) { extract($args); }

        //------------------------------------------------------
        // Create Object
        //------------------------------------------------------
        $o = new $obj_name();
    
        //------------------------------------------------------
        // If a Primary Key was passed...
        //------------------------------------------------------
        if ($pkey) {
            $load_status = $o->load($pkey);
            if ($load_status != 1) {
                if (empty($add_if_no_exist)) { return false; }
                else { $trans_type = 'add'; }
            }
        }
    
        //------------------------------------------------------
        // Import Data
        //------------------------------------------------------
        $o->import($data);

        //------------------------------------------------------
        // Do not save field "id" unless explicitly
        // told to do so
        //------------------------------------------------------
        if (empty($save_id)) { $o->no_save("id"); }
    
        //------------------------------------------------------
        // Print Only (Debug)
        //------------------------------------------------------
        if (!empty($print_only)) { $o->print_only(); }
    
        //------------------------------------------------------
        // Save
        //------------------------------------------------------
        $save_val = $o->save($pkey, false, $pkey);
        if (!empty($print_only)) { print $save_val; }
    
        return array(
            'trans_type' => $trans_type,
            'save_val' => $save_val
        );
    }
    
}
