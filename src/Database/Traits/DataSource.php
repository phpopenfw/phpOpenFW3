<?php
//*****************************************************************************
//*****************************************************************************
/**
 * Data Source Trait
 *
 * @package         phpOpenFW
 * @author          Christian J. Clark
 * @copyright       Copyright (c) Christian J. Clark
 * @license         https://mit-license.org
 **/
//*****************************************************************************
//*****************************************************************************

namespace phpOpenFW\Database\Traits;

//*****************************************************************************
/**
 * Data Source Trait
 */
//*****************************************************************************
trait DataSource
{

    //*************************************************************************
    // Class Members
    //*************************************************************************
    protected $data_source = '';
    protected $handle = false;
    protected $resource = false;
    protected $server = '127.0.0.1';
    protected $port = false;
    protected $source = false;
    protected $user = false;
    protected $pass = false;
    protected $persistent = true;

    //*************************************************************************
    //*************************************************************************
    // Get Object Instance
    //*************************************************************************
    //*************************************************************************
    public static function Instance($data_source='')
    {
        return new static($data_source);
    }

    //*************************************************************************
    //*************************************************************************
    // Is Data Source Valid
    //*************************************************************************
    //*************************************************************************
    public function IsDataSourceValid($data_source='')
    {
        return \phpOpenFW\Core\DataSources::Exists($data_source);
    }

    //*************************************************************************
    //*************************************************************************
    // Get Data Source
    //*************************************************************************
    //*************************************************************************
    public function GetDataSource($data_source='')
    {
        if ($data_source == '') {
            $data_source = \phpOpenFW\Core\DataSources::GetDefault();
            if (!$data_source) {
                throw new \Exception('Data source not given and default data source is not set.');
            }
        }
        return \phpOpenFW\Config\DataSource::Instance($data_source);
    }

    //*************************************************************************
    //*************************************************************************
    // Set Connection Parameters
    //*************************************************************************
    //*************************************************************************
    public function SetConnectionParameters()
    {
        $ds_obj = $this->GetDataSource($this->data_source);
        $this->handle = (!isset($ds_obj->handle)) ? (false) : ($ds_obj->handle);
        $this->server = (!isset($ds_obj->server)) ? ('127.0.0.1') : ($ds_obj->server);
        $this->port = (!isset($ds_obj->port)) ? (389) : ($ds_obj->port);
        $this->source = (!isset($ds_obj->source)) ? ('') : ($ds_obj->source);
        $this->user = (!isset($ds_obj->user)) ? ('') : ($ds_obj->user);
        $this->pass = (!isset($ds_obj->pass)) ? ('') : ($ds_obj->pass);
        $this->persistent = (!isset($ds_obj->persistent)) ? (true) : ($ds_obj->persistent);
    }

    //*************************************************************************
    //*************************************************************************
    // Set Connection Parameters
    //*************************************************************************
    //*************************************************************************
    public function SetDataSourceHandle()
    {
        $ds_obj = $this->GetDataSource($this->data_source);
        $ds_obj->handle = $this->handle;
    }

    //*************************************************************************
    //*************************************************************************
    // Get Connection Handle
    //*************************************************************************
    //*************************************************************************
    public function GetConnectionHandle()
    {
        return $this->handle;
    }

    //*************************************************************************
    //*************************************************************************
    // Get Query Resource
    //*************************************************************************
    //*************************************************************************
    public function GetResource()
    {
        return $this->resource;
    }

}
