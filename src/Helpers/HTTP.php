<?php
//*****************************************************************************
//*****************************************************************************
/**
 * HTTP Helper Object
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 **/
//*****************************************************************************
//*****************************************************************************

namespace phpOpenFW\Helpers;

//*****************************************************************************
/**
 * HTTP Helper Class
 */
//*****************************************************************************
class HTTP
{
	//=========================================================================
	//=========================================================================
	/**
	 * This method will redirect the user to the given page
	 */
	//=========================================================================
	//=========================================================================
	public static function redirect($location=false)
	{
		//---------------------------------------------------------------------
		// Set the location
		//---------------------------------------------------------------------
		if (empty($location)) {
			$qs_start = strpos($_SERVER['REQUEST_URI'], '?');
			if ($qs_start === false) {
				$location = $_SERVER['REQUEST_URI'];
			}
			else {
				$location = substr($_SERVER['REQUEST_URI'], 0, $qs_start);
			}
		}

		//---------------------------------------------------------------------
		// Redirect
		//---------------------------------------------------------------------
		header("Location: {$location}");
		exit;
	}

	//=========================================================================
	//=========================================================================
	/**
	 * Return All HTTP Request Headers
	 */
	//=========================================================================
	//=========================================================================
	public static function GetAllHeaders()
	{
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        $headers = [];
        foreach ($_SERVER as $name => $value) {
           if (substr($name, 0, 5) == 'HTTP_') {
               $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
               $headers[$name] = $value;
           }
           else if ($name == "CONTENT_TYPE") {
               $headers["Content-Type"] = $value;
           }
           else if ($name == "CONTENT_LENGTH") {
               $headers["Content-Length"] = $value;
           }
       }
       return $headers;
    }

}
