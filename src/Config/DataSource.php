<?php
//*****************************************************************************
//*****************************************************************************
/**
 * Data Source Class
 *
 * @package         phpopenfw/phpopenfw3/phpopenfw3
 * @author          Christian J. Clark
 * @copyright       Copyright (c) Christian J. Clark
 * @website         https://phpopenfw.org
 * @license         https://mit-license.org
 **/
//*****************************************************************************
//*****************************************************************************

namespace phpOpenFW\Config;

//*****************************************************************************
/**
 * Data Source Class
 */
//*****************************************************************************
class DataSource
{
    //*************************************************************************
    // Class Members
    //*************************************************************************
    protected $data_sources;
    protected $params = [];
    protected $registered = false;
    protected $is_existing = false;
    protected $ds_index = false;
    protected $db_type_obj = false;

    //*************************************************************************
    //*************************************************************************
    // Get Instance
    //*************************************************************************
    //*************************************************************************
    public static function Instance($in)
    {
        //=====================================================================
        // Return New DataSource Object
        //=====================================================================
        return new static($in);
    }

    //*************************************************************************
    //*************************************************************************
    // Constructor function
    //*************************************************************************
    //*************************************************************************
    public function __construct($in)
    {
        //---------------------------------------------------------------------
        // Check that phpOpenFW has been bootstrapped
        //---------------------------------------------------------------------
        \phpOpenFW\Core::CheckBootstrapped();

        //---------------------------------------------------------------------
        // Set Data Sources Storage
        //---------------------------------------------------------------------
        $this->data_sources =& $GLOBALS['PHPOPENFW_DATA_SOURCES'];

        //---------------------------------------------------------------------
        // Load existing data source
        //---------------------------------------------------------------------
        if (is_scalar($in)) {
            $this->is_existing = true;
            $this->Load($in);
        }
        //---------------------------------------------------------------------
        // Create new data source
        //---------------------------------------------------------------------
        else if (is_array($in)) {
            $this->Create($in);
        }
        //---------------------------------------------------------------------
        // Invalid parameter
        //---------------------------------------------------------------------
        else {
            $msg = 'Invalid parameter passed for instantiating DataSource object.';
            $msg .= ' An array of data source parameters or a string index representing an existing data source must be passed.';
            throw new \Exception($msg);
        }
    }

    //*************************************************************************
    //*************************************************************************
    // Register
    //*************************************************************************
    //*************************************************************************
    public function Register($key, $overwrite=false)
    {
        //---------------------------------------------------------------------
        // Existing data source?
        //---------------------------------------------------------------------
        if ($this->is_existing) {
            throw new \Exception('Existing data sources cannot be registered.');
        }

        //---------------------------------------------------------------------
        // Already registered?
        //---------------------------------------------------------------------
        if ($this->registered) {
            return $this;
        }

        //---------------------------------------------------------------------
        // Validate Key
        //---------------------------------------------------------------------
        if (!is_scalar($key) || $key == '') {
            throw new \Exception('Invalid index given for data source registration.');
        }

        //---------------------------------------------------------------------
        // Check if data source already exists
        //---------------------------------------------------------------------
        if (array_key_exists($key, $this->data_sources) && !$overwrite) {
            return $this;
        }

        //---------------------------------------------------------------------
        // Register data source in storage
        //---------------------------------------------------------------------
        $this->data_sources[$key] = $this->params;
        $this->params['index'] = $key;
        $this->ds_index = $key;

        //---------------------------------------------------------------------
        // Return object for chaining
        //---------------------------------------------------------------------
        return $this;
    }

    //*************************************************************************
    //*************************************************************************
    // Unregister
    //*************************************************************************
    //*************************************************************************
    public function Unregister($key)
    {
        //---------------------------------------------------------------------
        // Existing data source?
        //---------------------------------------------------------------------
        if (!$this->is_existing) {
            throw new \Exception('Only existing data sources can be unregistered.');
        }

        //---------------------------------------------------------------------
        // Unregister data source in storage
        //---------------------------------------------------------------------
        unset($this->data_sources[$key]);
        $this->is_existing = false;
        $this->registered = false;
        $this->params['index'] = false;
        $this->ds_index = false;

        //---------------------------------------------------------------------
        // Return object for chaining
        //---------------------------------------------------------------------
        return $this;
    }

    //*************************************************************************
    //*************************************************************************
    // Set Default Data Source
    //*************************************************************************
    //*************************************************************************
    public function SetDefault()
    {
        //---------------------------------------------------------------------
        // Set Default Data Source
        //---------------------------------------------------------------------
        if ($this->registered != '') {
            $GLOBALS['PHPOPENFW_DEFAULT_DATA_SOURCE'] = $this->ds_index;
        }
        else {
            throw new \Exception('Data source not registered. Only registered data sources can be set as default.');
        }

        //---------------------------------------------------------------------
        // Return object for chaining
        //---------------------------------------------------------------------
        return $this;
    }

    //*************************************************************************
    //*************************************************************************
    // Get Database Type Object
    //*************************************************************************
    //*************************************************************************
    public function GetDatabaseTypeObject()
    {
        return $this->db_type_obj;
    }

    //*************************************************************************
    //*************************************************************************
    // Get Quoted Data Types
    //*************************************************************************
    //*************************************************************************
    public function GetQuotedTypes()
    {
        return $this->db_type_obj->QuotedTypes();
    }

    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    // Internal Methods
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    //*************************************************************************
    //*************************************************************************
    // Create Data Source
    //*************************************************************************
    //*************************************************************************
    protected function Create($params)
    {
        $known_params = [
            'type',
            'server',
            'port',
            'source',
            'schema',
            'user',
            'pass',
            'instance',
            'conn_str',
            'options',
            'persistent',
            'reuse_connection',
            'charset'
        ];

        //---------------------------------------------------------------------
        // Count / Validate Parameters
        //---------------------------------------------------------------------
        $param_count = 0;
        $new_data_source = [];
        foreach ($known_params as $index) {
            $index = strtolower($index);
            if (array_key_exists($index, $params)) {

                //-------------------------------------------------------------
                // Type
                //-------------------------------------------------------------
                if ($index == 'type') {
                    $params[$index] = strtolower($params[$index]);
                    if ($params[$index] == 'mysql') {
                        $params[$index] = 'mysqli';
                    }
                }

                //-------------------------------------------------------------
                // Set Value
                // Increment Parameter Count
                //-------------------------------------------------------------
                $new_data_source[$index] = $params[$index];
                $param_count++;
            }
        }

        //---------------------------------------------------------------------
        // Validate that there are valid parameters set
        //---------------------------------------------------------------------
        if (count($new_data_source) == 0) {
            throw new \Exception('No valid data source parameters passed.');
        }

        //---------------------------------------------------------------------
        // Set Data Source Parameters
        //---------------------------------------------------------------------
        $this->params = $new_data_source;
        $this->params['handle'] = 0;
        $this->params['internal_id'] = $this->GenerateIndex();

        //---------------------------------------------------------------------
        // Set Database Type Class
        //---------------------------------------------------------------------
        $this->SetDatabaseTypeObject();

        //---------------------------------------------------------------------
        // Return true for success
        //---------------------------------------------------------------------
        return true;
    }

    //*************************************************************************
    //*************************************************************************
    // Generate Index
    //*************************************************************************
    //*************************************************************************
    protected function GenerateIndex()
    {
        return md5(serialize($this->params));
    }

    //*************************************************************************
    //*************************************************************************
    // Load Data Source
    //*************************************************************************
    //*************************************************************************
    protected function Load($key)
    {
        //---------------------------------------------------------------------
        // Does data source exist?
        //---------------------------------------------------------------------
        if (!isset($this->data_sources[$key])) {
            throw new \Exception("Data source '{$key}' does not exist.");
        }

        //---------------------------------------------------------------------
        // Load data source
        //---------------------------------------------------------------------
        $this->ds_index = $key;
        $this->params = $this->data_sources[$key];
        $this->params['index'] = $key;
        $this->registered = true;
        $this->is_existing = true;

        //---------------------------------------------------------------------
        // Set Database Type Class
        //---------------------------------------------------------------------
        $this->SetDatabaseTypeObject();

        //---------------------------------------------------------------------
        // Return true for success
        //---------------------------------------------------------------------
        return true;
    }

    //*************************************************************************
    //*************************************************************************
    // Set Datbase Type Object
    //*************************************************************************
    //*************************************************************************
    protected function SetDatabaseTypeObject()
    {
        if ($this->IsRelational()) {
            $this->db_type_obj = new \phpOpenFW\Database\Structure\DatabaseType($this->params['type']);
        }
    }

    //*************************************************************************
    //*************************************************************************
    // Is Data Source Relational?
    //*************************************************************************
    //*************************************************************************
    protected function IsRelational()
    {
        return (in_array($this->params['type'], [
            'mysql',
            'mysqli',
            'pgsql',
            'oracle',
            'sqlsrv',
            'mssql',
            'sqlite',
            'db2'
        ]));
    }

    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    // Magic Methods
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    //*************************************************************************
    //*************************************************************************
    // Set
    //*************************************************************************
    //*************************************************************************
    public function __set($index, $value)
    {
        if (is_scalar($index) && $index != '') {
            $this->params[$index] = $value;
        }
        else {
            throw new \Exception('Invalid index used to set data source value.');
        }
    }

    //*************************************************************************
    //*************************************************************************
    // Get
    //*************************************************************************
    //*************************************************************************
    public function __get($index)
    {
        if (is_scalar($index) && $index != '') {
            if (array_key_exists($index, $this->params)) {
                return $this->params[$index];
            }
            return null;
        }
        else {
            throw new \Exception('Invalid index used to get data source value.');
        }
    }

    //*************************************************************************
    //*************************************************************************
    // Isset
    //*************************************************************************
    //*************************************************************************
    public function __isset($index)
    {
        if (is_scalar($index) && $index != '') {
            return isset($this->params[$index]);
        }
        return null;
    }

    //*************************************************************************
    //*************************************************************************
    // Unset
    //*************************************************************************
    //*************************************************************************
    public function __unset($index)
    {
        if (is_scalar($index) && $index != '') {
            unset($this->params[$index]);
        }
    }

}
