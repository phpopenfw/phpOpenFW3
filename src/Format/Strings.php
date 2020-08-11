<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Strings Formatting Class
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 **/
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\Format;

//**************************************************************************************
/**
 * Strings Formatting Class
 */
//**************************************************************************************
class Strings
{
	//==================================================================================
	//==================================================================================
	// Replace the last occurrence of a string
	//==================================================================================
	//==================================================================================
	public static function str_replace_last($search, $replace, $subject)
	{
    	if ((string)$search == '') {
        	trigger_error('Invalid search string.');
    	}
        $pos = strrpos($subject, $search);
        if ($pos !== false) {
            $search = substr_replace($subject, $replace, $pos, strlen($search));
        }
        return $search;
    }

	//==================================================================================
	//==================================================================================
	// Replace the last occurrence of a string (Case-insensitive)
	//==================================================================================
	//==================================================================================
	public static function str_ireplace_last($search, $replace, $subject)
	{
    	if ((string)$search == '') {
        	trigger_error('Invalid search string.');
    	}
        $pos = strripos($subject, $search);
        if ($pos !== false) {
            $search = substr_replace($subject, $replace, $pos, strlen($search));
        }
        return $search;
    }

}
