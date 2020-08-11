<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Session Data Cache Object
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 **/
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\Cache\Objects;

//**************************************************************************************
/**
 * Session Level Data Cache Class
 */
//**************************************************************************************
class SessionCache extends Core
{

	//*************************************************************************
	// Constructor Function
	//*************************************************************************
    public function __construct($key)
    {
    	if (!$key) {
    		trigger_error('You must specify a valid cache key to be used as a cache reference.');
    		return false;
    	}
    	$this->key = (string)$key;
    	$this->existed = true;
    	if (!isset($_SESSION['dco'][$this->key])) {
    		$_SESSION['dco'][$this->key] = array();
    		$this->existed = false;
    	}
        $this->container =& $_SESSION['dco'][$this->key];
        $this->scope = 'session';
    }

}
