<?php
//*****************************************************************************
//*****************************************************************************
/**
 * Application Configuration Class
 *
 * @package         phpopenfw/phpopenfw3
 * @author          Christian J. Clark
 * @copyright       Copyright (c) Christian J. Clark
 * @website         https://phpopenfw.org
 * @license         https://mit-license.org
 **/
//*****************************************************************************
//*****************************************************************************

namespace phpOpenFW\Core;

//*****************************************************************************
/**
 * AppConfig Class
 */
//*****************************************************************************
class AppConfig extends \phpOpenFW\Config\Config
{
    //*************************************************************************
    //*************************************************************************
    // Constructor function
    //*************************************************************************
    //*************************************************************************
    public function __construct(Array $args=[])
    {
        //---------------------------------------------------------------------
        // Parent Constructor
        //---------------------------------------------------------------------
        parent::__construct($args);

        //---------------------------------------------------------------------
        // Check that phpOpenFW has been bootstrapped
        //---------------------------------------------------------------------
        \phpOpenFW\Core::CheckBootstrapped();

        //---------------------------------------------------------------------
        // Set Config
        //---------------------------------------------------------------------
        $this->config_data =& $GLOBALS[PHPOPENFW_CONFIG_INDEX];
    }

    //*************************************************************************
    //*************************************************************************
    // Load Configuration
    //*************************************************************************
    //*************************************************************************
    public function Load($config_file)
    {
        //---------------------------------------------------------------------
        // No Config File Set? Use default.
        //---------------------------------------------------------------------
        if (!$config_file || !file_exists($config_file)) {
            $config_file = PHPOPENFW_APP_FILE_PATH . '/config.inc.php';
        }

        //---------------------------------------------------------------------
        // Validate Config File
        //---------------------------------------------------------------------
        if (!file_exists($config_file)) {
            if ($this->display_errors) {
                throw new \Exception('Configuration file is invalid or cannot be opened.');
            }
            return false;
        }

        //---------------------------------------------------------------------
        // Include Config File
        //---------------------------------------------------------------------
        include($config_file);

        //---------------------------------------------------------------------
        // Check if config object was overridden
        //---------------------------------------------------------------------
        if (gettype($this->config_data) != 'object') {
            throw new \Exception('Configuration object has been overridden. Do not make $config global in configuration.');
        }

        //---------------------------------------------------------------------
        // Check for Configuration Data
        //---------------------------------------------------------------------
        if (isset($config) && is_iterable($config)) {
            $this->SetConfigValues($config);
        }
        else if (isset($config_arr) && is_iterable($config_arr)) {
            $this->SetConfigValues($config_arr);
        }
        else {
            if ($this->display_errors) {
                throw new \Exception('Configuration not set. $config not found.');
            }
            return false;
        }

        //---------------------------------------------------------------------
        // Configuration Loaded Successfully
        //---------------------------------------------------------------------
        if (!defined('PHPOPENFW_CONFIG_SET')) {
            define('PHPOPENFW_CONFIG_SET', 1);
        }
        return true;
    }

}
