<?php
//*****************************************************************************
//*****************************************************************************
/**
 * SQL Limit Trait
 *
 * @package         phpopenfw/phpopenfw3
 * @author          Christian J. Clark
 * @copyright       Copyright (c) Christian J. Clark
 * @website         https://phpopenfw.org
 * @license         https://mit-license.org
 **/
//*****************************************************************************
//*****************************************************************************

namespace phpOpenFW\Builders\SQL\Traits;

//*****************************************************************************
/**
 * SQL Limit Trait
 */
//*****************************************************************************
trait Limit
{
    //=========================================================================
    // Trait Memebers
    //=========================================================================
    protected $limit = false;
    protected $offset = false;

    //=========================================================================
    //=========================================================================
    // Limit Clause Method
    //=========================================================================
    //=========================================================================
    public function Limit($limit, $offset=false)
    {
        if (is_null($limit)) {
            $this->limit = false;
        }
        else if ($limit != '' && (int)$limit) {
            $this->limit = (int)$limit;
        }
        if (is_null($offset)) {
            $this->offset = false;
        }
        else if ($offset != '' && (int)$offset) {
            $this->offset = (int)$offset;
        }
        return $this;
    }

    //#########################################################################
    //#########################################################################
    //#########################################################################
    // Protected / Internal Methods
    //#########################################################################
    //#########################################################################
    //#########################################################################

    //=========================================================================
    //=========================================================================
    // Format Limit Clause Method
    //=========================================================================
    //=========================================================================
    protected function FormatLimit()
    {
        $ret_val = '';

        //---------------------------------------------------------------------
        // MySQL Limit / Offset
        //---------------------------------------------------------------------
        if ($this->db_type == 'mysql' || $this->db_type == 'pgsql') {
            if ($this->limit) {
                $ret_val = 'LIMIT ' . $this->limit;
            }
            if ($this->offset) {
                $ret_val .= ' OFFSET ' . $this->offset;
            }
        }
        //---------------------------------------------------------------------
        // Oracle
        //---------------------------------------------------------------------
        else if ($this->db_type == 'oracle') {
            if ($this->offset) {
                $ret_val = "OFFSET {$this->offset} ROWS";
            }
            if ($this->limit) {
                $ret_val .= " FETCH NEXT {$this->limit} ROWS ONLY";
            }            
        }
        //---------------------------------------------------------------------
        // SQL Server
        //---------------------------------------------------------------------
        else if ($this->db_type == 'sqlsrv') {
            $ret_val = 'SELECT TOP ' . $this->limit;
        }

        return $ret_val;
    }

}
