<?php
//*****************************************************************************
//*****************************************************************************
/**
 * Framework Core Class
 *
 * @package        phpOpenFW
 * @author         Christian J. Clark
 * @copyright    Copyright (c) Christian J. Clark
 * @license        https://mit-license.org
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
    public static function Bootstrap($file_path=false, Array $args=[])
    {
        //=====================================================================
        // Default Args / Extract Args
        //=====================================================================
        $load_config = false;
        $config_file = false;
        $load_data_sources = false;
        $db_config_file = false;
        $display_errors = false;
        $config_index = 'config';
        extract($args);

        //=====================================================================
        // Define Framework Path?
        //=====================================================================
        if (!defined('PHPOPENFW_FRAME_PATH')) {
            $frame_path = realpath(__DIR__ . '/../../');
            define('PHPOPENFW_FRAME_PATH', $frame_path);
            $_SESSION['PHPOPENFW_FRAME_PATH'] = PHPOPENFW_FRAME_PATH;
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
            $_SESSION['PHPOPENFW_APP_FILE_PATH'] = PHPOPENFW_APP_FILE_PATH;
        }

        //=====================================================================
        // Setup Methods
        //=====================================================================
        self::SetVersion();
        self::DetectEnv();

        //=====================================================================
        // Configuration Session Index
        //=====================================================================
        if (!defined('PHPOPENFW_CONFIG_INDEX')) {
            if (!$config_index) {
                $config_index = 'config';
            }
            define('PHPOPENFW_CONFIG_INDEX', $config_index);
        }

        //=====================================================================
        // Load Configuration?
        //=====================================================================
        if ($load_config) {
            self::LoadConfiguration([
                'config_file' => $config_file,
                'display_errors' => $display_errors
            ]);
        }

        //=====================================================================
        // Load Data Sources?
        //=====================================================================
        if ($load_data_sources) {
            self::LoadDataSources([
                'config_file' => $db_config_file,
                'display_errors' => $display_errors
            ]);
        }

        //=====================================================================
        // Flag phpOpenFW as Bootstrapped
        //=====================================================================
        define('PHPOPENFW_BOOTSTRAPPED', true);
    }

    //*************************************************************************
    //*************************************************************************
    /**
     * Has phpOpenFW been bootsrapped?
     */
    //*************************************************************************
    //*************************************************************************
    public static function IsBootstrapped()
    {
        if (defined('PHPOPENFW_BOOTSTRAPPED') && PHPOPENFW_BOOTSTRAPPED) {
            return true;
        }
        return false;
    }

    //*************************************************************************
    //*************************************************************************
    /**
     * Check that phpOpenFW been bootstrapped
     */
    //*************************************************************************
    //*************************************************************************
    public static function CheckBootstrapped(Array $args=[])
    {
        if (!self::IsBootstrapped()) {
            $msg = 'An operation that requires initialization occurred before phpOpenFW was bootstrapped.';
            throw new \Exception($msg);
        }
    }

    //*************************************************************************
    //*************************************************************************
    /**
     * Load Configuration Function
     * @param Array Arguments / Options
     */
    //*************************************************************************
    //*************************************************************************
    public static function LoadConfiguration(Array $args=[])
    {
        //=====================================================================
        // Check that phpOpenFW has been bootstrapped
        //=====================================================================
        \phpOpenFW\Core::CheckBootstrapped();

        //=====================================================================
        // Defaults / Extract Args
        //=====================================================================
        $config_file = false;
        $session_index = 'config';
        $display_errors = false;
        extract($args);

        //=====================================================================
        // Configuration File Set?
        //=====================================================================
        if (!$config_file || !file_exists($config_file)) {
            $config_file = PHPOPENFW_APP_FILE_PATH . '/config.inc.php';
        }
        if (file_exists($config_file)) {
            $config = new Core\Config($config_file);
            if ($config->IsValid()) {
                if (!isset($_SESSION[PHPOPENFW_CONFIG_INDEX])) {
                    $_SESSION[PHPOPENFW_CONFIG_INDEX] = $config->Export();
                }
                else {
                    $_SESSION[PHPOPENFW_CONFIG_INDEX] = array_merge($_SESSION[PHPOPENFW_CONFIG_INDEX], $config->Export());
                }
                $GLOBALS['PHPOPENFW_CONFIG'] =& $_SESSION[PHPOPENFW_CONFIG_INDEX];
                return $GLOBALS['PHPOPENFW_CONFIG'];
            }
            else if ($display_errors) {
                trigger_error('Error: Invalid configuration.');
            }
        }
        else if ($display_errors) {
            trigger_error('Error: Configuration file does not exist.');
        }

        return false;
    }

    //*************************************************************************
    //*************************************************************************
    /**
     * Load Database Sources Configuration Function
     * @param Array Arguments / Options
     */
    //*************************************************************************
    //*************************************************************************
    public static function LoadDataSources(Array $args=[])
    {
        //=====================================================================
        // Check that phpOpenFW has been bootstrapped
        //=====================================================================
        \phpOpenFW\Core::CheckBootstrapped();

        //=====================================================================
        // Defaults / Extract Args
        //=====================================================================
        $config_file = false;
        $force_reload = false;
        $display_errors = false;
        extract($args);

        //=====================================================================
        // Load Data Sources?
        //=====================================================================
        if ($force_reload || !defined('PHPOPENFW_DB_CONFIG_SET')) {
            if (!$config_file || !file_exists($config_file)) {
                $config_file = PHPOPENFW_APP_FILE_PATH . '/config/data_sources.php';
            }
            if (file_exists($config_file)) {
                $data_arr = array();
                require($config_file);

                if (isset($data_arr) && !isset($data_sources)) {
                    $data_sources = $data_arr;
                }

                if (!empty($data_sources) && is_array($data_sources)) {
                    $key_arr = array_keys($data_sources);
                    foreach ($key_arr as $key) {
                        $reg_code = Core\DataSources::Register($key, $data_sources[$key]);
                    }
                    if (!defined('PHPOPENFW_DB_CONFIG_SET')) {
                        define('PHPOPENFW_DB_CONFIG_SET', true);
                    }
                    return true;
                }
                else {
                    if ($display_errors) {
                        trigger_error('Error: No data sources defined.');
                    }
                }
            }
            else {
                if ($display_errors) {
                    trigger_error('Error: Data Source Configuration file does not exist.');
                }
            }
        }

        return false;
    }

    //*************************************************************************
    //*************************************************************************
    /**
     * Get Configuration Value
     * @param Index of value to retrieve
     */
    //*************************************************************************
    //*************************************************************************
    public static function GetConfigValue($index)
    {
        if (is_scalar($index)) {
            if (defined('PHPOPENFW_CONFIG_INDEX') && PHPOPENFW_CONFIG_INDEX) {
                if (isset($_SESSION[PHPOPENFW_CONFIG_INDEX]) && isset($_SESSION[PHPOPENFW_CONFIG_INDEX][$index])) {
                    return $_SESSION[PHPOPENFW_CONFIG_INDEX][$index];
                }
            }
            else {
                if (isset($_SESSION[$index])) {
                    return $_SESSION[$index];
                }
            }
        }
        return null;
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
    private static function DetectEnv()
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
    private static function SetVersion()
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
