<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Base Class Plugin
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 **/
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\Cores;

//**************************************************************************************
/**
 * Base Class
 */
//**************************************************************************************
abstract class Base
{

	//========================================================================
	// Class Members (Variables)
	//========================================================================
    protected $success;
    protected $error;
    protected $nodata;
    protected $loaded;

	//========================================================================
	// Reset Statuses
	//========================================================================
	protected function reset()
	{
		$this->success = false;
		$this->error = false;
		$this->nodata = true;
		$this->loaded = false;
	}

	//========================================================================
	/**
	* Display Error Function
	**/
	//========================================================================
	protected function display_error($function, $msg)
	{
		$class = __CLASS__;
		trigger_error("Error: [{$class}]::{$function}(): {$msg}");
	}

	//========================================================================
	/**
	* Get Option Function
	* @param string The key of the value to retrieve
	**/
	//========================================================================
    public function get_opt($opt)
    {
    	$opt = strtoupper((string)$opt);
    	if ($opt == '') { return false; }
    	if (isset($this->inst_opts[$opt])) { return $this->inst_opts[$opt]; }
    	return false;
    }

	//========================================================================
	/**
	* Set Option Function
	* @param string The key of the value to set
	* @param string The value to set
	**/
	//========================================================================
    public function set_opt($opt, $val=false)
    {
    	$opt = strtoupper((string)$opt);
    	if ($opt == '') { return false; }
    	if ($val === false) {
	    	unset($this->inst_opts[$opt]);
    	}
    	else {
    		$this->inst_opts[$opt] = $val;
    	}
    	return true;
    }

	//========================================================================
	/**
	* Function to display a "Not Available" Message
	**/
	//========================================================================
	protected function not_available($function)
	{
		$class = __CLASS__;
		trigger_error("The function {$function}() is not available with the {$class} class.", E_USER_WARNING);
		return false;
	}

	//========================================================================
	/**
	// Status / Value Functions
	**/
	//========================================================================
    public function success() { return $this->success; }
    public function error() { return $this->error; }
    public function nodata() { return $this->nodata; }
    public function loaded() { return $this->loaded; }

}
