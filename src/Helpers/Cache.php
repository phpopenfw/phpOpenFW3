<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Cache Helper Methods Plugin
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 **/
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\Helpers;

//**************************************************************************************
/**
 * Cache Class
 */
//**************************************************************************************
class Cache
{

	//=============================================================================
	//=============================================================================
	/**
	* Create and return a cache key for use in MemCache for example.
	*
	* @param string Cache Key Stub
	* @param array The parameters passed to the original function.
	*
	* @return string A unique cache key.
	*/
	//=============================================================================
	//=============================================================================
	public static function make_cache_key($stub, $args)
	{
		if (empty($stub) || empty($args)) { return false; }
		$cache_key = (defined('MC_KEY_STUB')) ? (MC_KEY_STUB . ':' . $stub) : ($stub);

		if (is_array($args)) {
			foreach ($args as $arg) {
				if (is_array($arg)) {
					$cache_key .= ':' . serialize($arg);
				}
				else {
					$cache_key .= ":{$arg}";
				}
			}
		}
		else {
			$cache_key .= ":{$args}";
		}

		return md5($cache_key);
	}

}
