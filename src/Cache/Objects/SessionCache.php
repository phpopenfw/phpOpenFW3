<?php
//*****************************************************************************
//*****************************************************************************
/**
 * Session Data Cache Object
 *
 * @package         phpopenfw/phpopenfw3/phpopenfw3
 * @author          Christian J. Clark
 * @copyright       Copyright (c) Christian J. Clark
 * @website         https://phpopenfw.org
 * @license         https://mit-license.org
 **/
//*****************************************************************************
//*****************************************************************************

namespace phpOpenFW\Cache\Objects;

//*****************************************************************************
/**
 * Session Level Data Cache Class
 */
//*****************************************************************************
class SessionCache extends Core
{
    //=========================================================================
    // Constructor Function
    //=========================================================================
    public function __construct($key)
    {
        if (!$key) {
            trigger_error('You must specify a valid cache key to be used as a cache reference.');
            return false;
        }
        $this->key = (string)$key;
        $this->existed = true;
        if (!isset($_SESSION['PHPOPENFW_DCO'][$this->key])) {
            $_SESSION['PHPOPENFW_DCO'][$this->key] = array();
            $this->existed = false;
        }
        $this->container =& $_SESSION['PHPOPENFW_DCO'][$this->key];
        $this->scope = 'session';
    }
}
