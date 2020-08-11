<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Output Formatting Class
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
 * Output Formatting Class
 */
//**************************************************************************************
class Output
{

	//*****************************************************************************
	//*****************************************************************************
	/**
	* Print a preformatted array or Simple XML Element Object (nicely viewable in HTML or CLI)
	* @param array Array to Print. Multiple Arrays can be passed.
	*/
	//*****************************************************************************
	//*****************************************************************************
	public static function print_array()
	{
		$sapi = strtoupper(php_sapi_name());
		$arg_list = func_get_args();
		foreach ($arg_list as $in_array) {
			if (
				is_array($in_array) 
				|| (gettype($in_array) == 'object' 
				&& (get_class($in_array) == 'SimpleXMLElement' || get_class($in_array) == 'stdClass'))
			) {
				if ($sapi != 'CLI') { print "<pre>\n"; }
				print_r($in_array);
				if ($sapi != 'CLI') { print "</pre>\n"; }
			}
		}
	}
	
}
