<?php
//**************************************************************************************
//**************************************************************************************
/**
 * HTTP Helper Object
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
 * HTTP Helper Class
 */
//**************************************************************************************
class HTTP
{

	//=============================================================================
	//=============================================================================
	/**
	 * This method will redirect the user to the given page and also send
	 * a message with it if wanted
	 *
	 * @param string $location The location to send the user, if empty $_SERVER['REDIRECT_URL'] is used
	 * @param string $message. The message to display once redirected
	 * @param mixed $message_type The message type. Options are:
	 * 		'error_message', 'warn_message', 'action_message' (default), 'gen_message', 'page_message'
	 */
	//=============================================================================
	//=============================================================================
	public static function redirect($location=false, $message=false, $message_type='action_message')
	{
		//-----------------------------------------------------
		// Set flag to stop page render
		//-----------------------------------------------------
		define('POFW_SKIP_RENDER', 1);

		//-----------------------------------------------------
		// Set the location
		//-----------------------------------------------------
		if (empty($location)) {
			$qs_start = strpos($_SERVER['REQUEST_URI'], '?');
			if ($qs_start === false) {
				$location = $_SERVER['REQUEST_URI'];
			}
			else {
				$location = substr($_SERVER['REQUEST_URI'], 0, $qs_start);
			}
		}

		//-----------------------------------------------------
		// Add a Message?
		//-----------------------------------------------------
		$message_type = (!$message_type) ? ('action') : (strtolower(str_ireplace('_message', '', $message_type)));
		if (!empty($message)) {
			\phpOpenFW\Session\Messages::AddMessage($message, $message_type);
		}
	
		//-----------------------------------------------------
		// Redirect
		//-----------------------------------------------------
		header("Location: {$location}");
		exit;
	}

}
