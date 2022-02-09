<?php
//*****************************************************************************
//*****************************************************************************
/**
 * Local Data Cache Object
 *
 * @package         phpopenfw/phpopenfw3
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
 * Local Level Data Cache Class
 */
//*****************************************************************************
class LocalCache extends Core
{
    //=========================================================================
    // Constructor Function
    //=========================================================================
    public function __construct()
    {
        $this->container = array();
        $this->scope = 'local';
        $this->existed = false;
    }
}
