<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Data Formatting Class
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 **/
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\Format;

//*****************************************************************************
/**
 * Data Formatting Class
 */
//*****************************************************************************
class Data
{
	//=============================================================================
	//=============================================================================
	// Format Records Method
	//=============================================================================
	//=============================================================================
	public static function format_records(&$recs, $fields)
	{
		if (!is_array($fields)) {
			$msg = 'Second parameter must be an array of key/value pairs that specify the field name and the formatting function name respectively.';
			trigger_error(__FUNCTION__ . "() :: {$msg}");
			return false;
		}
		else if (!$recs) { return false; }
		else if (!$fields) { return false; }
	
		//------------------------------------------------------------
		// Process Records
		//------------------------------------------------------------
		$processed = 0;
		foreach ($recs as &$rec) {
			if (!is_array($rec)) { continue; }
			foreach ($fields as $field => $fn) {
				if (isset($rec[$field])) {
					if (is_array($fn)) {
						$sub_procs = 0;
						foreach ($fn as $sub_fn) {
							if (function_exists($sub_fn)) {
								$rec[$field] = call_user_func($sub_fn, $rec[$field]);
								$sub_procs++;
							}
						}
						if ($sub_procs) { $processed++; }
					}
					else {
						if (is_callable($fn)) {
							$rec[$field] = $fn($rec[$field]);
							$processed++;
						}
						else if (function_exists($fn)) {
							$rec[$field] = call_user_func($fn, $rec[$field]);
							$processed++;					
						}
					}
				}
			}
		}
	
		return $processed;
	}

}
