<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Class for Globalizing Functions
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
 * Globalizing Class
 */
//**************************************************************************************
class Globalize
{

	//*****************************************************************************
	/**
	 * Bootstrap Method
	 */
	//*****************************************************************************
	protected static function Bootstrap()
	{
		//============================================================
		// Bootstrap the Core?
		//============================================================
		if (!defined('PHPOPENFW_FRAME_PATH')) {
			\phpOpenFW\Framework\Core::Bootstrap();
		}
	}

	//*****************************************************************************
	/**
	 * Globalize ALL Functions / Classes
	 */
	//*****************************************************************************
	public static function All(Array $args=[])
	{
		extract($args);
		self::Bootstrap();
		self::Core($args);
		self::XML($args);
		self::Database($args);
		self::Form($args);
		self::HTML($args);
		if (!empty($args['Utility'])) {
			self::Utility($args);
		}
		if (!empty($args['App'])) {
			self::App($args);
		}
		self::phpOpenPlugins($args);
		if (!isset($excluded['LoadHTMLHelpers'])) {
			self::LoadHTMLHelpers($args);
		}
		if (!isset($excluded['LoadSessionMessageHelpers'])) {
			self::LoadSessionMessageHelpers($args);
		}
	}

	//*****************************************************************************
	/**
	 * Globalize Core Functions / Classes
	 */
	//*****************************************************************************
	public static function Core(Array $args=[])
	{
		extract($args);
		self::Bootstrap();
		include_once(PHPOPENFW_FRAME_PATH . '/src/globals/Globalize/Core.php');
	}

	//*****************************************************************************
	/**
	 * Globalize XML Functions / Classes
	 */
	//*****************************************************************************
	public static function XML(Array $args=[])
	{
		extract($args);
		self::Bootstrap();
		include_once(PHPOPENFW_FRAME_PATH . '/src/globals/Globalize/XML.php');
	}

	//*****************************************************************************
	/**
	 * Globalize Database Functions / Classes
	 */
	//*****************************************************************************
	public static function Database(Array $args=[])
	{
		extract($args);
		self::Bootstrap();
		include_once(PHPOPENFW_FRAME_PATH . '/src/globals/Globalize/Database.php');
	}

	//*****************************************************************************
	/**
	 * Globalize Database Functions / Classes
	 */
	//*****************************************************************************
	public static function Form(Array $args=[])
	{
		extract($args);
		self::Bootstrap();
		include_once(PHPOPENFW_FRAME_PATH . '/src/globals/Globalize/Form.php');
	}

	//*****************************************************************************
	/**
	 * Globalize HTML Functions / Classes
	 */
	//*****************************************************************************
	public static function HTML(Array $args=[])
	{
		extract($args);
		self::Bootstrap();
		include_once(PHPOPENFW_FRAME_PATH . '/src/globals/Globalize/HTML.php');
	}

	//*****************************************************************************
	/**
	 * Globalize Utility Functions / Classes
	 */
	//*****************************************************************************
	public static function Utility(Array $args=[])
	{
		extract($args);
		self::Bootstrap();
		include_once(PHPOPENFW_FRAME_PATH . '/src/globals/Globalize/Utility.php');
	}

	//*****************************************************************************
	/**
	 * Globalize App Functions / Classes
	 */
	//*****************************************************************************
	public static function App(Array $args=[])
	{
		extract($args);
		self::Bootstrap();
		include_once(PHPOPENFW_FRAME_PATH . '/src/globals/Globalize/App.php');
	}

	//*****************************************************************************
	/**
	 * Globalize UPN Functions / Classes
	 */
	//*****************************************************************************
	public static function UPN(Array $args=[])
	{
		extract($args);
		self::Bootstrap();
		include_once(PHPOPENFW_FRAME_PATH . '/src/globals/Globalize/UPN.php');
	}

	//*****************************************************************************
	/**
	 * Globalize phpOpenPlugins Functions / Classes
	 */
	//*****************************************************************************
	public static function phpOpenPlugins(Array $args=[])
	{
		extract($args);
		self::Bootstrap();
		include_once(PHPOPENFW_FRAME_PATH . '/src/globals/Globalize/phpOpenPlugins.php');
	}

	//*****************************************************************************
	/**
	 * Load Global HTML Helper Functions
	 */
	//*****************************************************************************
	public static function LoadHTMLHelpers()
	{
		self::Bootstrap();
		include_once(PHPOPENFW_FRAME_PATH . '/src/globals/xhtml_gen.inc.php');
	}

	//*****************************************************************************
	/**
	 * Load Session Messages Helper Functions
	 */
	//*****************************************************************************
	public static function LoadSessionMessageHelpers()
	{
		self::Bootstrap();
		include_once(PHPOPENFW_FRAME_PATH . '/src/globals/SessionMessages.inc.php');
	}

}
