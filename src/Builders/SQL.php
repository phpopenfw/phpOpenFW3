<?php
//*****************************************************************************
//*****************************************************************************
/**
 * SQL Builder Class
 *
 * @package         phpopenfw/phpopenfw3
 * @author          Christian J. Clark
 * @copyright       Copyright (c) Christian J. Clark
 * @website         https://phpopenfw.org
 * @license         https://mit-license.org
 **/
//*****************************************************************************
//*****************************************************************************

namespace phpOpenFW\Builders;

//*****************************************************************************
/**
 * SQL Class
 */
//*****************************************************************************
class SQL
{
    //=========================================================================
    //=========================================================================
    // Select Method
    //=========================================================================
    //=========================================================================
    public static function Select($table, $data_source=false)
    {
        return new SQL\Select($table, $data_source);
    }

    //=========================================================================
    //=========================================================================
    // Insert Method
    //=========================================================================
    //=========================================================================
    public static function Insert($table, $data_source=false)
    {
        return new SQL\Insert($table, $data_source);
    }

    //=========================================================================
    //=========================================================================
    // Update Method
    //=========================================================================
    //=========================================================================
    public static function Update($table, $data_source=false)
    {
        return new SQL\Update($table, $data_source);
    }

    //=========================================================================
    //=========================================================================
    // Delete Method
    //=========================================================================
    //=========================================================================
    public static function Delete($table, $data_source=false)
    {
        return new SQL\Delete($table, $data_source);
    }
}
