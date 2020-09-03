<?php
//*****************************************************************************
//*****************************************************************************
/**
 * V2 Bridge Plugin
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
 * V@ Bridge Class
 */
//*****************************************************************************
class V2_Bridge
{
	//=========================================================================
	/**
	 * Bootstrap Method
	 */
	//=========================================================================
	protected static function Bootstrap()
	{
		//---------------------------------------------------------------------
		// Bootstrap the Core?
		//---------------------------------------------------------------------
		if (!defined('PHPOPENFW_FRAME_PATH')) {
			\phpOpenFW\Core::Bootstrap();
		}
	}

	//=========================================================================
	/**
	 * Bridge ALL V2 Classes
	 */
	//=========================================================================
	public static function All(Array $args=[])
	{
		extract($args);
		self::Bootstrap();
		self::Core($args);
		self::Database($args);
		self::Form($args);
	}

	//=========================================================================
	/**
	 * Bridge Core Classes
	 */
	//=========================================================================
	public static function Core(Array $args=[])
	{
		extract($args);
		self::Bootstrap();
		//include_once(PHPOPENFW_FRAME_PATH . '/src/bridge/Core.php');

        class_alias('\phpOpenFW\Core', '\phpOpenFW\Framework\Core');
        class_alias('\phpOpenFW\Core\DataSources', '\phpOpenFW\Framework\Core\DataSources');

	}

	//=========================================================================
	/**
	 * Bridge Database Classes
	 */
	//=========================================================================
	public static function Database(Array $args=[])
	{
		extract($args);
		self::Bootstrap();
		//include_once(PHPOPENFW_FRAME_PATH . '/src/bridge/Database.php');
	}
}
