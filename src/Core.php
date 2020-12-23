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
    // Class Members
    //*************************************************************************
    protected static $stateless = false;

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
        $stateless = false;
        $config_index = 'config';
        extract($args);

        //=====================================================================
        // Settings
        //=====================================================================
        self::$stateless = (bool)$stateless;

        //=====================================================================
        // Re-activate from Session?
        //=====================================================================
        if (!self::$stateless) {
            Core\Session::Reactivate();
        }

        //=====================================================================
        // Define Framework Path?
        //=====================================================================
        if (!defined('PHPOPENFW_FRAME_PATH')) {
            $frame_path = realpath(__DIR__ . '/../');
            define('PHPOPENFW_FRAME_PATH', $frame_path);
            $GLOBALS['PHPOPENFW_FRAME_PATH'] = PHPOPENFW_FRAME_PATH;
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
            $GLOBALS['PHPOPENFW_APP_FILE_PATH'] = PHPOPENFW_APP_FILE_PATH;
        }

        //=====================================================================
        // Setup Methods
        //=====================================================================
        self::SetVersion();
        self::DetectEnv();

        //=====================================================================
        // Configuration Storage Initialization
        // Start Configuration
        //=====================================================================
        if (!defined('PHPOPENFW_CONFIG_INDEX')) {
            if (!$config_index) {
                $config_index = 'config';
            }
            define('PHPOPENFW_CONFIG_INDEX', $config_index);
        }
        $GLOBALS[PHPOPENFW_CONFIG_INDEX] = new \stdClass();

        //=====================================================================
        // Data Sources Storage Initialization
        //=====================================================================
        if (!isset($GLOBALS['PHPOPENFW_DATA_SOURCES'])) {
            $GLOBALS['PHPOPENFW_DATA_SOURCES'] = [];
        }
        $GLOBALS['PHPOPENFW_DEFAULT_DATA_SOURCE'] = false;

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

        //=====================================================================
        // Activate Session?
        //=====================================================================
        if (!self::$stateless) {
            Core\Session::Activate();
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
     * Is app running stateless?
     */
    //*************************************************************************
    //*************************************************************************
    public static function IsStateless()
    {
        return self::$stateless;
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

    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    // Protected Methods
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
    protected static function DetectEnv()
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
    protected static function SetVersion()
    {
        if (defined('PHPOPENFW_VERSION')) {
            return PHPOPENFW_VERSION;
        }
        else if (isset($GLOBALS['PHPOPENFW_VERSION'])) {
            $version = $GLOBALS['PHPOPENFW_VERSION'];
        }
        else {
            $version = false;
            $ver_file = PHPOPENFW_FRAME_PATH . '/VERSION';
            if (file_exists($ver_file)) {
                $version = trim(file_get_contents($ver_file));
            }
            $GLOBALS['PHPOPENFW_VERSION'] = $version;
        }
        define('PHPOPENFW_VERSION', $version);
        return PHPOPENFW_VERSION;
    }

}
