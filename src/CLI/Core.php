<?php
//*****************************************************************************
//*****************************************************************************
/**
 * Core Command Line Interface Class
 *
 * @package         phpopenfw/phpopenfw3
 * @author          Christian J. Clark
 * @copyright       Copyright (c) Christian J. Clark
 * @website         https://phpopenfw.org
 * @license         https://mit-license.org
 **/
//*****************************************************************************
//*****************************************************************************

namespace phpOpenFW\CLI;

//*****************************************************************************
/**
 * Core Class
 */
//*****************************************************************************
abstract class Core
{
    //*************************************************************************
    //*************************************************************************
    // Traits
    //*************************************************************************
    //*************************************************************************
    use \phpOpenFW\Traits\Opts;
    use Traits\Args;
    use Traits\Messages;
    use Traits\CustomMethods;
    use Traits\Environment;

    //*************************************************************************
    //*************************************************************************
    // Class Members
    //*************************************************************************
    //*************************************************************************
    protected $app_path = false;
    protected $config = false;
    protected $args = [];

    //*************************************************************************
    //*************************************************************************
    // Get Instance
    //*************************************************************************
    //*************************************************************************
    public static function GetInstance($app_path='', Array $args=[])
    {
        //=====================================================================
        // Return New CLI Object
        //=====================================================================
        return new static($app_path, $args);
    }

    //*************************************************************************
    //*************************************************************************
    // Constructor
    //*************************************************************************
    //*************************************************************************
    public function __construct($app_path, Array $args=[])
    {
        //=====================================================================
        // Valid Environment?
        //=====================================================================
        if (strtoupper(php_sapi_name()) != 'CLI') {
            self::PrintError('Invalid environment.');
            exit;
        }

        //=====================================================================
        // Check / Validate App Path and Args
        //=====================================================================
        if (is_array($app_path) && !$args) {
            $args = $app_path;
            $app_path = false;
        }
        if (!$app_path) {
            $app_path = realpath(__DIR__ . '/../../../../..');
        }

        //=====================================================================
        // Validate App Path
        //=====================================================================
        if (!is_dir($app_path)) {
            self::PrintError('Invalid application path.');
            return false;
        }

        //=====================================================================
        // Parse Arguments
        //=====================================================================
        if (!$args = self::ParseArgs($args)) {
            self::PrintError('Invalid arguments.');
            return false;
        }

        //=====================================================================
        // Initialize App
        //=====================================================================
        $this->app_path = $app_path;
        define('APP_PATH', $app_path);
        $this->args = $args;

        //=====================================================================
        // Initialize phpOpenFW
        //=====================================================================
        \phpOpenFW\Core::Bootstrap($app_path, [
            'load_config' => true,
            'load_data_sources' => true
        ]);

        //=====================================================================
        // Load Configuration
        //=====================================================================
        $this->config = new \phpOpenFW\Core\AppConfig();

        //=====================================================================
        // Namespace Set?
        //=====================================================================
        if (isset($this->config->app_namespace)) {
            $this->SetNamespace($this->config->app_namespace);
        }

        //=====================================================================
        // Check for Configuration Options
        //=====================================================================
        if (isset($this->config->auto_custom_methods)) {
            $this->auto_custom_methods = $this->config->auto_custom_methods;
        }

        //=====================================================================
        // Auto Register Custom Methods?
        //=====================================================================
        if ($this->auto_custom_methods && $this->app_namespace) {
            $this->AutoRegisterCustomMethods();
        }
    }

    //*************************************************************************
    //*************************************************************************
    // Run Job
    //*************************************************************************
    //*************************************************************************
    public function Run()
    {
        self::PrintWarning('No Run method.');
    }

}
