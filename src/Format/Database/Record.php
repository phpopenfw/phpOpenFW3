<?php
//******************************************************************************
//******************************************************************************
/**
 * Data Record Formatting Class
 *
 * @package         phpOpenFW
 * @author          Christian J. Clark
 * @copyright       Copyright (c) Christian J. Clark
 * @license         https://mit-license.org
 **/
//******************************************************************************
//******************************************************************************

namespace phpOpenFW\Format\Database;

//******************************************************************************
/**
 * Record Class
 */
//******************************************************************************
class Record
{
    //==========================================================================
    /**
     * Remove Record Elements
     */
    //==========================================================================
    public static function RemoveElements(Array &$data, Array $remove, Array $args=[])
    {
        if (isset($args['data'])) {
            unset($args['data']);
        }
        extract($args);

        if (is_array($remove)) {
            foreach ($remove as $index) {
                if (array_key_exists($index, $data)) {
                    unset($data[$index]);
                    $removed++;
                }
            }
            return $removed;
        }

        return false;
    }

}
