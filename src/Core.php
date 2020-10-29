<?php
//*****************************************************************************
//*****************************************************************************
/**
 * Framework Core Class
 *
 * @package         phpOpenFW
 * @author          Christian J. Clark
 * @copyright       Copyright (c) Christian J. Clark
 * @license         https://mit-license.org
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
        // Configuration Storage Initialization
        // Configuration Session / Globals Index
        //=====================================================================
        if (!defined('PHPOPENFW_CONFIG_INDEX')) {
            if (!$config_index) {
                $config_index = 'config';
            }
            define('PHPOPENFW_CONFIG_INDEX', $config_index);
            $_SESSION[PHPOPENFW_CONFIG_INDEX] = new \stdClass();
        }
        $GLOBALS[PHPOPENFW_CONFIG_INDEX] =& $_SESSION[PHPOPENFW_CONFIG_INDEX];

        //=====================================================================
        // Data Sources Storage Initialization
        //=====================================================================
        if (!isset($_SESSION['PHPOPENFW_DATA_SOURCES'])) {
            $_SESSION['PHPOPENFW_DATA_SOURCES'] = [];
        }
        $GLOBALS['PHPOPENFW_DATA_SOURCES'] =& $_SESSION['PHPOPENFW_DATA_SOURCES'];
        $_SESSION['PHPOPENFW_DEFAULT_DATA_SOURCE'] = false;

        //=====================================================================
        // Flag phpOpenFW as Bootstrapped
        //=====================================================================
        define('PHPOPENFW_BOOTSTRAPPED', true);

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
        $display_errors = false;
        extract($args);

        //=====================================================================
        // Load Configuration
        //=====================================================================
        $config = new \phpOpenFW\Core\AppConfig($args);
        if ($config->Load($config_file, $args)) {
            return true;
        }
        else if ($display_errors) {
            trigger_error('Unable to load configuration.');
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
            if (\phpOpenFW\Core\DataSources::Load($config_file, $args)) {
                $config = new \phpOpenFW\Core\AppConfig($args);
                if ($config->default_data_source) {
                    \phpOpenFW\Core\DataSources::SetDefault($config->default_data_source);
                }
                return true;
            }
            else if ($display_errors) {
                trigger_error('Unable to load data sources.');
            }
        }

        return false;
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
