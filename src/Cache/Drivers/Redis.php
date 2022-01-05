<?php
//*****************************************************************************
//*****************************************************************************
/**
 * Redis Cache Driver Object
 *
 * @package         phpopenfw/phpopenfw3/phpopenfw3
 * @author          Christian J. Clark
 * @copyright       Copyright (c) Christian J. Clark
 * @website         https://phpopenfw.org
 * @license         https://mit-license.org
 */
//*****************************************************************************
//*****************************************************************************

namespace phpOpenFW\Cache\Drivers;

//*****************************************************************************
/**
 * Redis Class
 */
//*****************************************************************************
class Redis
{
    //=========================================================================
    // Class Members
    //=========================================================================
    protected $cache_type = 'redis';
    protected $port = 6379;

    //=========================================================================
    // Constructor Method
    //=========================================================================
    public function __construct($params)
    {
        parent::__construct($params);
        $this->cache_obj = new \Redis(); 
        $this->cache_obj->connect($this->server, $this->port); 
    }
}
