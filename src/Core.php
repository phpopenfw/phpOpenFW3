<?php
//*****************************************************************************
//*****************************************************************************
/**
 * Framework Core Class
 *
 * @package		phpOpenFW2
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 **/
//*****************************************************************************
//*****************************************************************************

namespace phpOpenFW;

//*****************************************************************************
/**
 * Framework Core Class
 */
//*****************************************************************************
class Core
{
	//*************************************************************************
	//*************************************************************************
    /**
    * Bootstrap Method
    */
	//*************************************************************************
	//*************************************************************************
    public static function Bootstrap($file_path=false)
    {
		//=====================================================================
		// Define Framework Path?
		//=====================================================================
	    if (!defined('PHPOPENFW_FRAME_PATH')) {
	        $frame_path = realpath(__DIR__ . '/../../');
	        define('PHPOPENFW_FRAME_PATH', $frame_path);
	        $_SESSION['frame_path'] = PHPOPENFW_FRAME_PATH;
		}

		//=====================================================================
		// Define File Path?
		//=====================================================================
		if (!defined('PHPOPENFW_APP_FILE_PATH')) {
			if ($file_path && !is_dir($file_path)) {
				trigger_error('Invalid file path given to Core Bootstrap method.');
				return false;
			}
			define('PHPOPENFW_APP_FILE_PATH', $file_path);
			$_SESSION['file_path'] = PHPOPENFW_APP_FILE_PATH;
		}

		//=====================================================================
		// Setup Methods
		//=====================================================================
        self::set_version();
        self::detect_env();
    }

    //*************************************************************************
    //*************************************************************************
    /**
    * Get URL Path Function
    */
    //*************************************************************************
    //*************************************************************************
    public static function get_url_path()
    {
    	//=====================================================================
    	// If $_SERVER['REDIRECT_URL'] is set
    	//=====================================================================
    	if (isset($_SERVER['REDIRECT_URL'])) {
    		return $_SERVER['REDIRECT_URL'];
    	}
    	//=====================================================================
    	// If $_SERVER['PATH_INFO'] is set
    	//=====================================================================
    	else if (isset($_SERVER['PATH_INFO'])) {
    		return $_SERVER['PATH_INFO'];
    	}
    	//=====================================================================
    	// If $_SERVER['REQUEST_URI'] is set
    	//=====================================================================
    	else if (isset($_SERVER['REQUEST_URI'])) {
    		$qs_start = strpos($_SERVER['REQUEST_URI'], '?');
    		if ($qs_start === false) {
    			return $_SERVER['REQUEST_URI'];
    		}
    		else {
    			return substr($_SERVER['REQUEST_URI'], 0, $qs_start);
    		}
    	}

    	return false;
    }

    //*************************************************************************
    //*************************************************************************
    /**
    * Get HTML Path Function
    */
    //*************************************************************************
    //*************************************************************************
    public static function get_html_path()
    {
    	$path = '';
    	if (isset($_SERVER['DOCUMENT_ROOT']) && isset($_SERVER['SCRIPT_FILENAME'])) {
    		$doc_root = $_SERVER['DOCUMENT_ROOT'];
    		$doc_root_parts = explode('/', $doc_root);
    		$script_file = $_SERVER['SCRIPT_FILENAME'];
    		$script_file_parts = explode('/', $script_file);

    		foreach ($script_file_parts as $key => $part) {
    			if (!isset($doc_root_parts[$key])) {
    				if ($part != 'index.php') { $path .= '/' . $part; }
    			}
    		}
    	}
    	else {
    		$_SESSION['html_path'] = $path;
    		$self = $_SERVER['PHP_SELF'];
    		$self_arr = explode('/', $self);
    		foreach ($self_arr as $item) {
    			if (!empty($item) && $item != 'index.php') { $path .= "/$item"; }
    		}
    		if ($path == '/') { $path = ''; }
    	}
    	return $path;
    }

    //*************************************************************************
    //*************************************************************************
    /**
    * Load Configuration Function
    */
    //*************************************************************************
    //*************************************************************************
    public static function load_config($config_file=false, Array $args=[])
    {
	    extract($args);

    	//=====================================================================
    	// Initialize the Arrays
    	//=====================================================================
    	$config_arr = array();
    	$config = array();
    	$data_arr = array();

    	//=====================================================================
    	// Include the configuration file
    	//=====================================================================
        if ($config_file && file_exists($config_file)) {
            require($config_file);
        }
    	else {
        	require(PHPOPENFW_APP_FILE_PATH . '/config.inc.php');
        }

    	//=====================================================================
    	// Set HTML Path
    	//=====================================================================
    	if (isset($config_arr['html_path'])) {
    		$_SESSION['html_path'] = $config_arr['html_path'];
    	}
    	else {
    		$_SESSION['html_path'] = self::get_html_path();
    	}

    	//=====================================================================
    	// *** Configuration Array
    	//=====================================================================
    	$key_arr = array_keys($config_arr);
    	$key_arr2 = array_keys($config);
    	if (!empty($session_index)) {
	    	if (!isset($_SESSION[$session_index])) { $_SESSION[$session_index] = []; }
	    	foreach ($key_arr as $key) { $_SESSION[$session_index][$key] = $config_arr[$key]; }
	    	foreach ($key_arr2 as $key) { $_SESSION[$session_index][$key] = $config[$key]; }
    	}
    	else {
	    	foreach ($key_arr as $key) { $_SESSION[$key] = $config_arr[$key]; }
	    	foreach ($key_arr2 as $key) { $_SESSION[$key] = $config[$key]; }
		}

    	//=====================================================================
    	// *** Data Source Array
    	//=====================================================================
    	if (is_array($data_arr) && !empty($data_arr)) {
	    	$key_arr = array_keys($data_arr);
	    	foreach ($key_arr as $key) {
	    		$reg_code = Core\DataSources::Register($key, $data_arr[$key]);
	    	}
		}
    }

    //*************************************************************************
    //*************************************************************************
    /**
    * Load Database Sources Configuration Function
    * @param string Full file path to data source configuration file
    * @param bool Force the configuration to be reloaded
    */
    //*************************************************************************
    //*************************************************************************
    public static function load_db_config($db_config, $force_config=false)
    {
    	if ((bool)$force_config === true || !empty($_SESSION['PHPOPENFW_DB_CONFIG_SET'])) {
    		if (file_exists($db_config)) {
    			$data_arr = array();
    			require($db_config);

    			if (isset($data_arr) && count($data_arr) > 0) {
    				$key_arr = array_keys($data_arr);
    				foreach ($key_arr as $key){
    					$reg_code = Core\DataSources::Register($key, $data_arr[$key]);
    					if (!$reg_code) { $_SESSION[$key]['handle'] = 0; }
    				}
    				$_SESSION['PHPOPENFW_DB_CONFIG_SET'] = true;
    			}
    			else {
    				trigger_error('Error: load_db_config(): No data sources defined!');
    				$_SESSION['PHPOPENFW_DB_CONFIG_SET'] = false;
    			}
    		}
    		else {
    			trigger_error('Error: load_db_config(): Data Source Configuration file does not exist!');
    			$_SESSION['PHPOPENFW_DB_CONFIG_SET'] = false;
    		}
    	}
    }

    //*************************************************************************
    //*************************************************************************
    /**
    * Kill a Session Function
    */
    //*************************************************************************
    //*************************************************************************
    public static function session_kill()
    {
    	if (isset($_SESSION)) {
    		$_SESSION = array();

    		if (ini_get('session.use_cookies')) {
    		    $params = session_get_cookie_params();
    		    setcookie(session_name(), '', time() - 42000,
    		        $params['path'], $params['domain'],
    		        $params['secure'], $params['httponly']
    		    );
    		}

			session_unset();
    		session_destroy();
    		return true;
    	}
    	return false;
    }

    //*************************************************************************
    //*************************************************************************
    /**
    * Passthrough methods to Data Sources Class
    */
    //*************************************************************************
    //*************************************************************************
    public static function reg_data_source($ds_index, $ds_params)
    {
        return Core\DataSources::Register($ds_index, $ds_params);
    }
    public static function default_data_source($index)
    {
        return Core\DataSources::SetDefault($index);
    }
    public static function get_default_data_source()
    {
        return Core\DataSources::GetDefault();
    }
    public static function get_data_source($index)
    {
        return Core\DataSources::GetOne($index);
    }

    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    // Private Methods
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    //*************************************************************************
    //*************************************************************************
    /**
    * Detect Environment Function
    */
    //*************************************************************************
    //*************************************************************************
    private static function detect_env()
    {
        if (!defined('POPOPENFW_IS_CLI')) {
            $env = (php_sapi_name() == 'cli') ? (true) : (false);
            define('POPOPENFW_IS_CLI', $env);
        }
        return POPOPENFW_IS_CLI;
    }

    //*************************************************************************
    //*************************************************************************
    /**
    * Set Version Function
    */
    //*************************************************************************
    //*************************************************************************
    private static function set_version()
    {
        if (defined('PHPOPENFW_VERSION')) {
            return PHPOPENFW_VERSION;
        }
        else if (isset($_SESSION['PHPOPENFW_VERSION'])) {
            $version = $_SESSION['PHPOPENFW_VERSION'];
        }
        else {
        	$version = false;
        	$ver_file = PHPOPENFW_FRAME_PATH . '/VERSION';
        	if (file_exists($ver_file)) {
        		$version = trim(file_get_contents($ver_file));
        	}
        	$_SESSION['PHPOPENFW_VERSION'] = $version;
        }
    	define('PHPOPENFW_VERSION', $version);
    	return PHPOPENFW_VERSION;
    }

}
