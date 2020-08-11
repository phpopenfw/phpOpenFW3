<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Cache Abstraction Object
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 */
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\Cache;

//**************************************************************************************
/**
 * Cache Class
 */
//**************************************************************************************
class Cache
{
	//**********************************************************************************
	// Class Members
	//**********************************************************************************
	protected $handle;
	protected $driver_obj;
	protected static $cache_types = [
	    'memcache',
	    //'redis'
    ];

	//**********************************************************************************
	// Constructor Method
	//**********************************************************************************
    public function __construct($handle)
    {
        $this->handle = $handle;
        $this->driver_obj = false;

        if (!self::CacheIsRegistered($handle)) {
            throw new \Exception("Cache '{$handle}' is not registered,");
        }
        if (isset($GLOBALS['POFW_Caches'][$handle]['driver_obj'])) {
            $this->driver_obj =& $GLOBALS['POFW_Caches'][$handle]['driver_obj'];
        }
        if (!$this->driver_obj) {
            throw new \Exception('Invalid cache object.');
        }
    }

	//**********************************************************************************
	// Set Option Method
	//**********************************************************************************
	public function setOption($key, $opt)
	{
        return $this->driver_obj->setOption($key, $opt);
	}

	//**********************************************************************************
	// Set Options Method
	//**********************************************************************************
	public function setOptions(Array $opts)
	{
        return $this->driver_obj->setOptions($opts);
	}

	//**********************************************************************************
	// Get Option Method
	//**********************************************************************************
	public function getOption($key)
	{
        return $this->driver_obj->getOption($key);
	}

	//**********************************************************************************
	// Get Options Method
	//**********************************************************************************
	public function getOptions(Array $keys=[])
	{
        return $this->driver_obj->getOptions($keys);
	}

	//**********************************************************************************
	// Set Method
	//**********************************************************************************
	public function set($key, $data, $ttl=0, Array $args=[])
	{
        return $this->driver_obj->set($key, $data, $ttl, $args);
	}

	//**********************************************************************************
	// Set Multiple Method
	//**********************************************************************************
	public function setMulti(Array $values, $ttl=0, Array $args=[])
	{
        return $this->driver_obj->setMulti($values, $ttl, $args);
	}

	//**********************************************************************************
	// Get Method
	//**********************************************************************************
	public function get($key, Array $args=[])
	{
        return $this->driver_obj->get($key, $args);
	}

	//**********************************************************************************
	// Get Multiple Method
	//**********************************************************************************
	public function getMulti(Array $keys, Array $args=[])
	{
        return $this->driver_obj->getMulti($keys, $args);
	}

	//**********************************************************************************
	// Delete Method
	//**********************************************************************************
	public function delete($key, Array $args=[])
	{
        return $this->driver_obj->delete($key, $args);
	}

	//**********************************************************************************
	// Delete Multiple Method
	//**********************************************************************************
	public function deleteMulti(Array $keys, Array $args=[])
	{
        return $this->driver_obj->deleteMulti($keys, $args);
	}

    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    // Static Methods
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	//**********************************************************************************
	// Register Caches Method
	//**********************************************************************************
	public static function RegisterCaches(Array $caches)
	{
        foreach ($caches as $handle => $cache) {
            self::RegisterCache($handle, $cache);
        }
    }

	//**********************************************************************************
	// Register Cache Method
	//**********************************************************************************
	public static function RegisterCache($handle, Array $params)
	{
        //==============================================================================
        // No Data Source Parameters?
        //==============================================================================
        if (!$handle) {
            throw new \Exception('Invalid cache handle passed.');
        }

        //==============================================================================
        // No Data Source Parameters?
        //==============================================================================
        if (!$params) {
            throw new \Exception('Invalid cache data source parameters.');
        }

        //==============================================================================
        // Validate Data Source Type
        //==============================================================================
        if (!isset($params['type'])) {
            throw new \Exception('Cache data source type not set.');
        }
        $params['type'] = strtolower(trim($params['type']));
        if (!in_array($params['type'], static::$cache_types)) {
            throw new \Exception('Invalid cache data source type.');
        }

        //==============================================================================
        // Require Namespace
        //==============================================================================
        if (!isset($params['namespace']) || !self::IsValidKey($params['namespace'])) {
            throw new \Exception('Namespace is required.');
        }

        //==============================================================================
        // Setup / Check if Cache is already created
        //==============================================================================
        if (!isset($GLOBALS['POFW_Caches'])) {
            $GLOBALS['POFW_Caches'] = [];
        }
        if (isset($GLOBALS['POFW_Caches'][$handle])) {
            return true;
        }

        //==============================================================================
        // Create New Cache
        //==============================================================================
        switch ($params['type']) {

            //--------------------------------------------------------------------------
            // Memcache
            //--------------------------------------------------------------------------
            case 'memcache':
                $cache_driver = new Drivers\Memcache($params);
                break;

            //--------------------------------------------------------------------------
            // Redis
            //--------------------------------------------------------------------------
            case 'redis':
                $cache_driver = new Drivers\Redis($params);
                break;

        }
        $GLOBALS['POFW_Caches'][$handle]['driver_obj'] = $cache_driver;
        return true;
    }

	//**********************************************************************************
	// Is Cache Registered Method
	//**********************************************************************************
	public static function CacheIsRegistered($handle)
	{
        if (!self::IsValidKey($handle)) {
            throw new \Exception('Invalid cache handle.');
        }
        if (isset($GLOBALS['POFW_Caches'][$handle])) {
            return true;
        }
        return false;
    }

	//**********************************************************************************
	// Is Valid Key Method
	//**********************************************************************************
	public static function IsValidKey($key)
	{
        if (is_scalar($key) && (string)$key != '') {
            return true;
        }
        return false;
	}

}
