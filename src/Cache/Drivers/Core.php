<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Cache Core Driver Object
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
 * Core Class
 */
//**************************************************************************************
abstract class Core
{
	//**********************************************************************************
	// Class Members
	//**********************************************************************************
	protected $cache_type = false;
    protected $_namespace = false;
    protected $cache_key_stub = '';
	protected $server = '127.0.0.1';
	protected $port = false;
	protected $weight = 1;
    protected $cache_obj = false;

	//**********************************************************************************
	// Constructor Method
	//**********************************************************************************
    public function __construct($params)
    {
        $this->_namespace = $params['namespace'];
        if (isset($params['server'])) {
            if (is_array($this->server) && !count($this->server)) {
                throw new \Exception('Empty server list passed.');
            }
            else if (empty($this->server)) {
                throw new \Exception('Invalid server given.');
            }
            $this->server = $params['server'];
        }
        if (isset($params['port'])) {
            $this->port = $params['port'];
        }
        if (isset($params['weight'])) {
            $this->weight = $params['weight'];
        }
        if (isset($params['stub']) && $params['stub']) {
            $this->cache_key_stub = $params['stub'];
        }
    }

	//**********************************************************************************
	// Set Option Method
	//**********************************************************************************
	public function setOption($key, $opt)
	{
        return $this->cache_obj->setOption($key, $opt);
	}

	//**********************************************************************************
	// Set Options Method
	//**********************************************************************************
	public function setOptions(Array $opts)
	{
        $opts_set = 0;
        foreach ($opts as $opt_key => $opt_val) {
            if (\phpOpenFW\Cache\Cache::IsValidKey($opt_key)) {
                if ($this->setOption($opt_key, $opt_val)) {
                    $opts_set++;
                }
            }
        }
        return $opts_set;
	}

	//**********************************************************************************
	// Get Option Method
	//**********************************************************************************
	public function getOption($key)
	{
        return $this->cache_obj->getOption($key);
	}

	//**********************************************************************************
	// Get Options Method
	//**********************************************************************************
	public function getOptions(Array $keys)
	{
        $ret_vals = [];
        foreach ($keys as $key) {
            if (\phpOpenFW\Cache\Cache::IsValidKey($key)) {
                $ret_vals[$key] = $this->getOption($key);
            }
        }
        return $ret_vals;
	}

	//**********************************************************************************
	// Set Method
	//**********************************************************************************
	public function set($key, $data, $ttl=0, Array $args=[])
	{
    	$cache_key = $this->makeCacheKey($key);
    	if (\phpOpenFW\Cache\Cache::IsValidKey($cache_key)) {
            return $this->cache_obj->set($cache_key, $data, $ttl);
        }
        return false;
	}

	//**********************************************************************************
	// Set Multiple Method
	//**********************************************************************************
	public function setMulti(Array $values, $ttl=0, Array $args=[])
	{
        $vals_set = 0;
        foreach ($values as $val_key => $val_val) {
            if ($this->set($val_key, $val_val, $ttl)) {
                $vals_set++;
            }
        }
        return $vals_set;
	}

	//**********************************************************************************
	// Get Method
	//**********************************************************************************
	public function get($key, Array $args=[])
	{
    	$cache_key = $this->makeCacheKey($key);
        if (\phpOpenFW\Cache\Cache::IsValidKey($cache_key)) {
            return $this->cache_obj->get($cache_key);
        }
        return false;
	}

	//**********************************************************************************
	// Get Multiple Method
	//**********************************************************************************
	public function getMulti(Array $keys, Array $args=[])
	{
        $ret_vals = [];
        foreach ($keys as $key) {
            $ret_vals[$key] = $this->get($key, $args);
        }
        return $ret_vals;
	}

	//**********************************************************************************
	// Delete Method
	//**********************************************************************************
	public function delete($key, Array $args=[])
	{
    	$cache_key = $this->makeCacheKey($key);
        if (\phpOpenFW\Cache\Cache::IsValidKey($cache_key)) {
            return $this->cache_obj->delete($cache_key);
        }
        return false;
	}

	//**********************************************************************************
	// Delete Multiple Method
	//**********************************************************************************
	public function deleteMulti(Array $keys, Array $args=[])
	{
        $vals_deleted = 0;
        foreach ($vals_deleted as $val_key => $val_val) {
            if ($this->delete($val_key)) {
                $vals_deleted++;
            }
        }
        return $vals_deleted;
	}

    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    // Internal Methods
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	//**********************************************************************************
	// Make Cache Key
	//**********************************************************************************
	protected function makeCacheKey($args)
	{
        $cache_key = '';

        //-----------------------------------------------------------------
        // Build Cache Key
        //-----------------------------------------------------------------
		if (is_array($args)) {
			foreach ($args as $arg) {
				if (is_array($arg)) {
					$cache_key .= ':' . serialize($arg);
				}
				else {
					$cache_key .= ":{$arg}";
				}
			}
		}
		else {
			$cache_key .= ":{$args}";
		}

        //-----------------------------------------------------------------
        // Check if Cache Key is empty
        //-----------------------------------------------------------------
        if (!$cache_key) {
            return false;
        }

        //-----------------------------------------------------------------
        // Hash and return Cache Key
        //-----------------------------------------------------------------
        if ($this->cache_key_stub) {
            $cache_key = $this->cache_key_stub . ':' . $cache_key;
        }
        $hashed_cache_key = md5($cache_key);
        if ($this->cache_type == 'redis') {
            $hashed_cache_key = $this->_namespace . ':' . $hashed_cache_key;
        }
		return $hashed_cache_key;
	}

}
