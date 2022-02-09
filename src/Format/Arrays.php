<?php
//******************************************************************************
//******************************************************************************
/**
 * Array Formatting Class
 *
 * @package         phpOpenFW
 * @author          Christian J. Clark
 * @copyright       Copyright (c) Christian J. Clark
 * @website         https://phpopenfw.org
 * @license         https://mit-license.org
 **/
//******************************************************************************
//******************************************************************************

namespace phpOpenFW\Format;

//******************************************************************************
/**
 * Array Formatting Class
 */
//******************************************************************************
class Arrays
{
    //==========================================================================
    /**
     * Get Array Reference Values
     */
    //==========================================================================
    public static function RefValues(Array $arr)
    {
        $refs = array();
        foreach($arr as $key => $value) {
            $refs[$key] = &$arr[$key];
        }
        return $refs;
    }

    //==========================================================================
    /**
     * Remove Array Elements
     */
    //==========================================================================
    public static function RemoveElements(Array &$arr, Array $remove, Array $args=[])
    {
        if (isset($args['arr'])) {
            unset($args['arr']);
        }
        extract($args);

        if (is_array($remove)) {
            foreach ($remove as $index) {
                if (array_key_exists($index, $arr)) {
                    unset($arr[$index]);
                    $removed++;
                }
            }
            return $removed;
        }

        return false;
    }

}
