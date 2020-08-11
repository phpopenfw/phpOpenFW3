<?php
//************************************************************************************
//************************************************************************************
/**
 * Data Sources Class
 *
 * @package		phpOpenFW2
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 **/
//************************************************************************************
//************************************************************************************

namespace phpOpenFW\Framework\Core;

//**************************************************************************************
/**
 * Data Sources Class
 */
//**************************************************************************************
class DataSources
{
    //************************************************************************
	//************************************************************************
    /**
    * Register Data Source Method
    * Register a new data source in the Session
    * @param string Data source index name
    * @param array Data source parameter array
    */
	//************************************************************************
	//************************************************************************
    public static function Register($ds_index, $ds_params)
    {
    	$known_params = array(
    		'type',
    		'server',
    		'port',
    		'source',
    		'user',
    		'pass',
    		'instance',
    		'conn_str',
    		'options',
    		'persistent',
    		'reuse_connection',
    		'charset'
    	);
    	$optional_params = array(
    		'port',
    		'user',
    		'pass',
    		'instance',
    		'conn_str',
    		'options',
    		'persistent',
    		'reuse_connection',
    		'charset'
    	);
    	settype($ds_index, 'string');
    	if (!is_array($ds_params)) {
    		trigger_error("Error: Register(): Index: '{$ds_index}', Second parameter must be an array.");
    		return 2;
    	}
    	else {
    		$param_count = count($known_params);
    		$new_data_source = array();
    		foreach ($known_params as $param_index) {
    			if (isset($ds_params[$param_index])) {
    				$new_data_source[$param_index] = $ds_params[$param_index];
    				$param_count--;
    			}
    			else if (in_array($param_index, $optional_params)) {
    				$param_count--;
    			}
    		}

    		if ($param_count > 0) {
    			trigger_error("Error: Register(): Index: '{$ds_index}', Incorrect parameter count in parameter array.");
    			return 4;
    		}
    		else {
    			$_SESSION[$ds_index] = $new_data_source;
    			if (!isset($_SESSION['data_sources'])) {
        			$_SESSION['data_sources'] = [];
    			}
    			$_SESSION['data_sources'][$ds_index] = $ds_index;
	    		$_SESSION[$ds_index]['handle'] = 0;
    			return 0;
    		}
    	}
    }

	//************************************************************************
	//************************************************************************
    /**
    * Set Default Data Source Function
    * @param string Data Source Index
    */
	//************************************************************************
	//************************************************************************
    public static function SetDefault($index)
    {
    	settype($index, 'string');
    	if ($index != '') {
    		if (isset($_SESSION[$index])) {
    			$_SESSION['default_data_source'] = $index;
    			return 0;
    		}
    		else {
    			trigger_error("Error: SetDefault(): The data source '{$index}' does not exist.");
    			return 2;
    		}
    	}
    	else {
    		trigger_error('Error: SetDefault(): Data source index cannot be empty.');
    		return 1;
    	}
    }

	//************************************************************************
	//************************************************************************
    /**
    * Get the Default Data Source Function
    */
	//************************************************************************
	//************************************************************************
    public static function GetDefault()
    {
        if (isset($_SESSION['default_data_source'])) {
            return $_SESSION['default_data_source'];
        }
        return false;
    }

	//************************************************************************
	//************************************************************************
    /**
    * Get a Registered Data Source Function
    */
	//************************************************************************
	//************************************************************************
    public static function GetOne($index)
    {
        if ($index == '') { return false; }
        if (isset($_SESSION[$index])) {
            return $_SESSION[$index];
        }
        return false;
    }
}
