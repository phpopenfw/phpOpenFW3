<?php
//**************************************************************************************
//**************************************************************************************
/**
 * URL Formatting Class
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
 * URL Formatting Class
 */
//**************************************************************************************
class URL
{

	//=============================================================================
	//=============================================================================
	// Add URL Parameters Method
	//=============================================================================
	//=============================================================================
	/**
	* Given a URL, add another paramter to it and return it.
	* @param string A URL
	* @param array An array in the form of [Key] => [Value] to be used for paramters.
	* @param bool Separator: [False] = '&' (default), [True] = '&amp;'
	* @param bool [True] = URL Encode Data (default), [False] = Do Not URL Encode Data
	* @return string New URL with update arguments/parameters
	*/
	//=============================================================================
	//=============================================================================
	public static function add_url_params($in_url, $params, $xml_escape=false, $url_encode=true)
	{
		$out_url = $in_url;
		if (!is_array($params)) {
			trigger_error('Error: [add_url_params] :: Second argument must be an array.', E_USER_WARNING );
			return $out_url;
		}
		else if (count($params) <= 0) {
			return $out_url;
		}
		else {
			$args_started = false;
			foreach ($params as $arg => $val) {
				if (!$args_started && stristr($out_url, '?') === false) {
					$out_url .= '?';
					$args_started = true;
				}
				else {
					if ($xml_escape) { $out_url .= '&amp;'; }
					else { $out_url .= '&'; }
				}
				if (!$url_encode) { $out_url .= $arg . '=' . $val; }
				else { $out_url .= $arg . '=' . urlencode($val); }
			}
		}
		return $out_url;
	}

	//=============================================================================
	//=============================================================================
	// URL Friendly String Function
	//=============================================================================
	//=============================================================================
	public static function URLFriendly($str)
	{
		$str = strtolower($str);
		$str = str_replace(' ' , '-', $str);
		$str = preg_replace("/[^a-zA-Z0-9_\-s]/", "", $str);
		return $str;
	}

}
