<?php
//**************************************************************************************
//**************************************************************************************
/**
 * MemCache Functions Plugin
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 */
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\Cache;

//**************************************************************************************
/**
 * Memcache Class
 */
//**************************************************************************************
class Memcache extends \phpOpenFW\Cores\StaticCore
{

	//=============================================================================
	//=============================================================================
	/**
	* Get Data With Caching
	*
	* @param object A valid MemCached Object
	* @param string The cache key stub
	* @param mixed A function name or array of an object and the method to call
	* @param array An array of arguments to pass to the called function/method
	* @param integer The number of seconds the cached item should live
	* @param bool Cache the results? Default is yes. (True)
	*
	* @return string A unique cache key.
	*/
	//=============================================================================
	//=============================================================================
	// Example Usage:
	//=============================================================================
	//	$result = POP_memcache::get_data_with_caching(
	//		$memcache, 
	//		MC_KEY_STUB, 
	//		'POP_mysql::get_records', 
	//		['jobs', ['client_id' => ['i', 87]], $args], 
	//		60
	//	);
	//=============================================================================
	//=============================================================================
	public static function get_data_with_caching($m, $stub, $fn, $args, $ttl, $use_cache=true)
	{
		settype($ttl, 'int');

		//-----------------------------------------------------------
		// Did we get a valid MemCached object?
		//-----------------------------------------------------------
		if (get_class($m) != 'Memcached') {
			self::display_error(__METHOD__, 'First parameter must be a valid Memcached object.');
			return false;
		}

		//-----------------------------------------------------------
		// Determine Function / Method
		// Update Cache Key Stub
		//-----------------------------------------------------------
		if (is_array($fn)) {
			$obj = $fn[0];
			$stub .= ":{$obj}";
			$fn = $fn[1];
		}
		$stub .= ":{$fn}";

		//-----------------------------------------------------------
		// Create Cache Key
		//-----------------------------------------------------------
		$cache_key = self::make_cache_key($stub, $args);

		//-----------------------------------------------------
		// Attempt to pull data from MemCache
		//-----------------------------------------------------
		$results = null;
		if ($use_cache) {
			$results = $m->get($cache_key);
		}

		//-----------------------------------------------------
		// Results Not Found / Don't Use MemCache
		//-----------------------------------------------------
		if (empty($results)) {
			if (!empty($obj) && is_object($obj)) {
				$results = call_user_func_array(array($obj, $fn), $args);
			}
			else {
				$results = call_user_func_array($fn, $args);
			}

			//-----------------------------------------------------
			// Cache the Results in MemCache
			//-----------------------------------------------------
			if ($use_cache) {
				$m->set($cache_key, $results, time() + $ttl);
			}
		}

		return $results;		
	}

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
		return \phpOpenFW\Helpers\Cache::make_cache_key($stub, $args);
	}

}
