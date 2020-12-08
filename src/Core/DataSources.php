<?php
//*****************************************************************************
//*****************************************************************************
/**
 * Data Sources Class
 *
 * @package         phpOpenFW
 * @author          Christian J. Clark
 * @copyright       Copyright (c) Christian J. Clark
 * @license         https://mit-license.org
 **/
//*****************************************************************************
//*****************************************************************************

namespace phpOpenFW\Core;

//*****************************************************************************
/**
 * Data Sources Class
 */
//*****************************************************************************
class DataSources
{

    //*************************************************************************
    //*************************************************************************
    /**
     * Load Data Sources from data sources file
     * @param string Data source file name
     */
    //*************************************************************************
    //*************************************************************************
    public static function Load($config_file=false, Array $args=[])
    {
        //---------------------------------------------------------------------
        // Check that phpOpenFW has been bootstrapped
        //---------------------------------------------------------------------
        \phpOpenFW\Core::CheckBootstrapped();

        //---------------------------------------------------------------------
        // Defaults / Extract Args
        //---------------------------------------------------------------------
        $force_reload = false;
        $display_errors = false;
        extract($args);

        //---------------------------------------------------------------------
        // Set Default data source configuration file?
        //---------------------------------------------------------------------
        if (!$config_file || !file_exists($config_file)) {
            $config_file = PHPOPENFW_APP_FILE_PATH . '/config/data_sources.php';
        }

        //---------------------------------------------------------------------
        // Data source configuration file exists? Load data sources.
        //---------------------------------------------------------------------
        if (file_exists($config_file)) {
            $data_arr = [];
            $data_sources = [];
            require($config_file);

            if (!empty($data_arr) && empty($data_sources)) {
                $data_sources = $data_arr;
            }

            if (!empty($data_sources) && is_array($data_sources)) {
                $key_arr = array_keys($data_sources);
                foreach ($key_arr as $key) {
                    self::Register($key, $data_sources[$key]);
                }
                if (!defined('PHPOPENFW_DB_CONFIG_SET')) {
                    define('PHPOPENFW_DB_CONFIG_SET', true);
                }
                return true;
            }
            else {
                if ($display_errors) {
                    trigger_error('No data sources defined.');
                }
            }
        }
        //---------------------------------------------------------------------
        // Data sources configuration file does not exist.
        //---------------------------------------------------------------------
        else {
            if ($display_errors) {
                trigger_error('Data Source Configuration file does not exist.');
            }
        }

        return false;
    }

    //*************************************************************************
    //*************************************************************************
    /**
     * List Registered Data Sources
     */
    //*************************************************************************
    //*************************************************************************
    public static function List()
    {
        //---------------------------------------------------------------------
        // Check that phpOpenFW has been bootstrapped
        //---------------------------------------------------------------------
        \phpOpenFW\Core::CheckBootstrapped();

        //---------------------------------------------------------------------
        // Build and return a list of registered data sources
        //---------------------------------------------------------------------
        $indexes = [];
        foreach ($_SESSION['PHPOPENFW_DATA_SOURCES'] as $index => $ds) {
            $indexes[$index] = $index;
        }
        return $indexes;
    }

    //*************************************************************************
    //*************************************************************************
    /**
     * Register Data Source Method
     * @param string Data source index name
     * @param array Data source parameter array
     */
    //*************************************************************************
    //*************************************************************************
    public static function Register($index, $params)
    {
        return \phpOpenFW\Config\DataSource::Instance($params)->Register($index);
    }

    //*************************************************************************
    //*************************************************************************
    /**
     * Unregister Data Source Method
     * @param string Data source index name
     */
    //*************************************************************************
    //*************************************************************************
    public static function Unregister($index)
    {
        return \phpOpenFW\Config\DataSource::Instance($index)->Unregister();
    }

    //*************************************************************************
    //*************************************************************************
    /**
     * Set Default Data Source Function
     * @param string Data Source Index
     */
    //*************************************************************************
    //*************************************************************************
    public static function SetDefault($index)
    {
        //---------------------------------------------------------------------
        // Set Data Source as Default
        //---------------------------------------------------------------------
        \phpOpenFW\Config\DataSource::Instance($index)->SetDefault($index);
    }

    //*************************************************************************
    //*************************************************************************
    /**
     * Get the Default Data Source Function
     */
    //*************************************************************************
    //*************************************************************************
    public static function GetDefault($return_object=false)
    {
        //---------------------------------------------------------------------
        // Check that phpOpenFW has been bootstrapped
        //---------------------------------------------------------------------
        \phpOpenFW\Core::CheckBootstrapped();

        //---------------------------------------------------------------------
        // Default Data Source explicitly set
        //---------------------------------------------------------------------
        if (!empty($_SESSION['PHPOPENFW_DEFAULT_DATA_SOURCE'])) {
            if (!$return_object) {
                return $_SESSION['PHPOPENFW_DEFAULT_DATA_SOURCE'];
            }
            else {
                if ($_SESSION['PHPOPENFW_DEFAULT_DATA_SOURCE']) {
                    return \phpOpenFW\Config\DataSource::Instance($_SESSION['PHPOPENFW_DEFAULT_DATA_SOURCE']);
                }
            }
        }
        //---------------------------------------------------------------------
        // Default Data Source NOT explicitly set
        // Return first data source, if one is set
        //---------------------------------------------------------------------
        else {
            if (array_key_exists('PHPOPENFW_DATA_SOURCES', $_SESSION) && count($_SESSION['PHPOPENFW_DATA_SOURCES'])) {
                reset($_SESSION['PHPOPENFW_DATA_SOURCES']);
                $key = key($_SESSION['PHPOPENFW_DATA_SOURCES']);
                if (!$return_object) {
                    return $key;
                }
                else {
                    return \phpOpenFW\Config\DataSource::Instance($key);
                }
            }
        }

        return false;
    }

    //*************************************************************************
    //*************************************************************************
    /**
     * Get a Registered Data Source Function
     */
    //*************************************************************************
    //*************************************************************************
    public static function GetOne($index)
    {
        //---------------------------------------------------------------------
        // Is this already a Data Source Object? Return it.
        //---------------------------------------------------------------------
        if (self::IsDataSource($index)) {
            return $index;
        }

        //---------------------------------------------------------------------
        // Validate Index
        //---------------------------------------------------------------------
        if (!is_scalar($index) || $index == '') {
            throw new \Exception('Data source index not given.');
        }

        //---------------------------------------------------------------------
        // Return DataSource Object Instance
        //---------------------------------------------------------------------
        return \phpOpenFW\Config\DataSource::Instance($index);
    }

    //*************************************************************************
    //*************************************************************************
    /**
     * Get a Registered Data Source Function or the Default Data Source
     */
    //*************************************************************************
    //*************************************************************************
    public static function GetOneOrDefault($index)
    {
        //---------------------------------------------------------------------
        // Return DataSource Object Instance
        //---------------------------------------------------------------------
        if ($index != '') {

            //-----------------------------------------------------------------
            // Is this already a Data Source Object? Return it.
            //-----------------------------------------------------------------
            if (self::IsDataSource($index)) {
                return $index;
            }
            else {
                return \phpOpenFW\Config\DataSource::Instance($index);
            }
        }
        //---------------------------------------------------------------------
        // Return Default DataSource Object Instance
        //---------------------------------------------------------------------
        else {
            $ds_obj = self::GetDefault(true);
            if (!$ds_obj) {
                throw new \Exception('Data source not given and default data source is not set.');
            }
            return $ds_obj;
        }
    }

    //*************************************************************************
    //*************************************************************************
    /**
     * Data Source Exists Function
     */
    //*************************************************************************
    //*************************************************************************
    public static function Exists($index)
    {
        return (isset($_SESSION['PHPOPENFW_DATA_SOURCES'][$index]));
    }

    //*************************************************************************
    //*************************************************************************
    /**
     * Is Object a Data Source
     */
    //*************************************************************************
    //*************************************************************************
    public static function IsDataSource($obj)
    {
        if (gettype($obj) == 'object' && get_class($obj) == 'phpOpenFW\Config\DataSource') {
            return true;
        }
        return false;
    }

}
