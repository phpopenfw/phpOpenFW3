<?php
//*****************************************************************************
//*****************************************************************************
/**
 * Records Formatting Class
 *
 * @package         phpopenfw/phpopenfw3
 * @author          Christian J. Clark
 * @copyright       Copyright (c) Christian J. Clark
 * @website         https://phpopenfw.org
 * @license         https://mit-license.org
 **/
//*****************************************************************************
//*****************************************************************************

namespace phpOpenFW\Format;

//*****************************************************************************
/**
 * Records Formatting Class
 */
//*****************************************************************************
class Records
{
    //=========================================================================
    //=========================================================================
    // Remove Data Elements
    //=========================================================================
    //=========================================================================
    public static function RemoveElements(Array &$data, Array $remove, Array $args=[])
    {
        if (isset($args['data'])) {
            unset($args['data']);
        }
        if (isset($args['remove'])) {
            unset($args['remove']);
        }
        $multiple = false;
        extract($args);

        if ($multiple) {
            $tmp_args = $args;
            $tmp_args['multiple'] = false;
            foreach ($data as $col => $val) {
                if (is_array($val)) {
                    static::RemoveElements($data[$col], $remove, $tmp_args);
                }
            }
        }
        else {
            foreach ($remove as $index) {
                if (array_key_exists($index, $data)) {
                    unset($data[$index]);
                }
            }
        }
    }

    //=========================================================================
    //=========================================================================
    // Keep Data Elements
    //=========================================================================
    //=========================================================================
    public static function KeepElements(Array &$data, Array $keep, Array $args=[])
    {
        if (isset($args['data'])) {
            unset($args['data']);
        }
        if (isset($args['keep'])) {
            unset($args['keep']);
        }
        $multiple = false;
        extract($args);

        foreach ($data as $col => $val) {
            if ($multiple) {
                $tmp_args = $args;
                $tmp_args['multiple'] = false;
                if (is_array($val)) {
                    static::KeepElements($data[$col], $keep, $tmp_args);
                }
            }
            else {
                if (!in_array($col, $keep)) {
                    unset($data[$col]);
                }
            }
        }
    }

    //=========================================================================
    //=========================================================================
    // Format Records
    //=========================================================================
    //=========================================================================
    public static function Format(Array $records, String $return_format, Array $args=[])
    {
        //---------------------------------------------------------------------
        // Return if no return format OR no records
        //---------------------------------------------------------------------
        if (!$return_format || !$records) {
            return $records;
        }

        //---------------------------------------------------------------------
        // Defaults, Options, and Initial Declarations
        //---------------------------------------------------------------------
        $return_recs = [];
        $allow_nonexistent_fields = false;
        if (!empty($args['allow_nonexistent_fields'])) {
            $allow_nonexistent_fields = true;
        }
        $use_field_indexes = true;
        if (array_key_exists('use_field_indexes', $args)) {
            $use_field_indexes = (bool)$args['use_field_indexes'];
        }

        //---------------------------------------------------------------------
        // Parse return format
        //---------------------------------------------------------------------
        $parts = explode(':', $return_format);
        $index = false;
        $fields = [];
        $field = false;
        if ($parts[0] != '') {
            $index = $parts[0];
        }
        if (count($parts) > 1 && $parts[1]) {
            if (stripos($parts[1], ',') !== false) {
                $tmp_fields = explode(',', $parts[1]);
                foreach ($tmp_fields as $tmp_field) {
                    $tmp_field = trim($tmp_field);
                    if (!$tmp_field) {
                        continue;
                    }
                    $fields[] = $tmp_field;
                }
                if (!$fields) {
                    throw new \Exception("No valid data fields given.");
                }
            }
            else {
                $field = trim($parts[1]);
                if (!$field) {
                    throw new \Exception("Invalid data field '{$field}'.");
                }
            }
        }

        //---------------------------------------------------------------------
        // Format Records
        //---------------------------------------------------------------------
        $rec_num = 1;
        foreach ($records as $rec) {
            if ($index && !array_key_exists($index, $rec)) {
                throw new \Exception("Index field '{$index}' does not exist in record #{$rec_num}.");
            }

            //-----------------------------------------------------------------
            // Build the record
            //-----------------------------------------------------------------
            $tmp_rec = $rec;
            if ($field) {
                if (!array_key_exists($field, $rec)) {
                    throw new \Exception("Data field '{$field}' does not exist in record #{$rec_num}.");
                }
                $tmp_rec = $rec[$field];
            }
            else if ($fields) {
                $tmp_rec = [];
                foreach ($fields as $tmp_field) {
                    if (!array_key_exists($tmp_field, $rec)) {
                        if (!$allow_nonexistent_fields) {
                            throw new \Exception("Data field '{$field}' does not exist in record #{$rec_num}.");
                        }
                    }
                    else {
                        if ($use_field_indexes) {
                            $tmp_rec[$tmp_field] = $rec[$tmp_field];
                        }
                        else {
                            $tmp_rec[] = $rec[$tmp_field];
                        }
                    }
                }
            }

            //-----------------------------------------------------------------
            // Add Record to new record set
            //-----------------------------------------------------------------
            if ($index) {
                $return_recs[$rec[$index]] = $tmp_rec;
            }
            else {
                $return_recs[] = $tmp_rec;
            }

            //-----------------------------------------------------------------
            // Increment Counter
            //-----------------------------------------------------------------
            $rec_num++;
        }

        //---------------------------------------------------------------------
        // Return Formatted Records
        //---------------------------------------------------------------------
        return $return_recs;
    }
}
