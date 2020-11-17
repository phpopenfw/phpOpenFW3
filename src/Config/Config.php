<?php
//*****************************************************************************
//*****************************************************************************
/**
 * Config Class
 *
 * @package         phpOpenFW
 * @author          Christian J. Clark
 * @copyright       Copyright (c) Christian J. Clark
 * @license         https://mit-license.org
 **/
//*****************************************************************************
//*****************************************************************************

namespace phpOpenFW\Config;

//*****************************************************************************
/**
 * Config Class
 */
//*****************************************************************************
class Config
{
    //*************************************************************************
    // Class Members
    //*************************************************************************
    protected $config_data = [];
    protected $display_errors = false;

    //*************************************************************************
    //*************************************************************************
    // Get Instance
    //*************************************************************************
    //*************************************************************************
    public static function Instance(Array $args=[])
    {
        //=====================================================================
        // Return New AppConfig Object
        //=====================================================================
        return new static($args);
    }

    //*************************************************************************
    //*************************************************************************
    // Constructor function
    //*************************************************************************
    //*************************************************************************
    public function __construct(Array $args=[])
    {
        //---------------------------------------------------------------------
        // Set Defaults / Args
        //---------------------------------------------------------------------
        $display_errors = false;
        extract($args);
        $this->display_errors = $display_errors;
    }

    //*************************************************************************
    //*************************************************************************
    // Export Configuration
    //*************************************************************************
    // Formats: array (default), json, raw
    //*************************************************************************
    //*************************************************************************
    public function Export($format='')
    {
        if ($format == 'raw') {
            return $this->config_data;
        }
        else if ($format == 'json') {
            return json_encode($this->config_data);
        }
        else {
            return (array)$this->config_data;
        }
    }

    //*************************************************************************
    //*************************************************************************
    // Set Config Values
    //*************************************************************************
    //*************************************************************************
    public function SetConfigValues($values)
    {
        if (!is_iterable($values)) {
            return false;
        }

        foreach ($values as $index => $value) {
            if (is_iterable($value)) {
                $this->config_data->$index = new \stdClass();
                $this->SetConfigValues($value);
            }
            else {
                $this->SetConfigValue($index, $value);
            }
        }

        return true;
    }

    //*************************************************************************
    //*************************************************************************
    // Set Config Value
    //*************************************************************************
    //*************************************************************************
    public function SetConfigValue($index, $value, $overwrite=false)
    {
        if (isset($this->config_data->$index)) {
            if (!$overwrite) {
                return false;
            }
            $this->config_data->$index = $value;
        }
        else {
            $this->config_data->$index = $value;
        }
        return true;
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
            $this->config_data->$index = $value;
        }
        else {
            throw new \Exception('Invalid index used to set value.');
        }
    }

    //*************************************************************************
    //*************************************************************************
    // Get
    //*************************************************************************
    //*************************************************************************
    public function &__get($index)
    {
        if (is_scalar($index) && $index != '') {
            if (isset($this->config_data->$index)) {
                return $this->config_data->$index;
            }
            return null;
        }
        else {
            throw new \Exception('Invalid index used to get value.');
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
            return isset($this->config_data->$index);
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
            unset($this->config_data->$index);
        }
    }
}
