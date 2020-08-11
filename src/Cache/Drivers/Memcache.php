<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Memcache Cache Driver Object
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 */
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\Cache\Drivers;

//**************************************************************************************
/**
 * Memcache Class
 */
//**************************************************************************************
class Memcache extends Core
{
	//**********************************************************************************
	// Class Members
	//**********************************************************************************
	protected $cache_type = 'memcache';
	protected $port = 11211;

	//**********************************************************************************
	// Constructor Method
	//**********************************************************************************
    public function __construct($params)
    {
        parent::__construct($params);
        $this->cache_obj = new \Memcached();
        if (is_array($this->server)) {
            foreach ($this->server as $key => $server) {
                if (!is_array($server)) {
                    $this->server[$key] = [$server, $this->port, $this->weight];
                }
            }
            $mc_status = $this->cache_obj->addServers($this->server, $this->port);
        }
        else {
            $mc_status = $this->cache_obj->addServer($this->server, $this->port);
        }
        if (!$mc_status) {
            throw new \Exception('An error occurred adding the server(s).');
        }
        $this->cache_obj->setOption(\Memcached::OPT_PREFIX_KEY, $this->_namespace);
    }

	//**********************************************************************************
	// Set Options Method
	//**********************************************************************************
	public function setOptions(Array $opts)
	{
        return $this->cache_obj->setOptions($opts);
	}

	//**********************************************************************************
	// Get Options Method
	//**********************************************************************************
	public function getOptions(Array $keys)
	{
        return $this->cache_obj->getOptions($keys);
	}

}
