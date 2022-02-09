<?php
//**************************************************************************************
//**************************************************************************************
/**
 * SQL Order By Trait
 *
 * @package         phpopenfw/phpopenfw3
 * @author          Christian J. Clark
 * @copyright       Copyright (c) Christian J. Clark
 * @website         https://phpopenfw.org
 * @license         https://mit-license.org
 **/
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\Builders\SQL\Traits;

//**************************************************************************************
/**
 * SQL Order By Trait
 */
//**************************************************************************************
trait OrderBy
{
    //=========================================================================
    // Trait Memebers
    //=========================================================================
    protected $order_by = [];

    //=========================================================================
    //=========================================================================
    // Add Order By Method
    //=========================================================================
    //=========================================================================
    public function OrderBy($order_by)
    {
        self::AddItemCSC($this->order_by, $order_by);
        return $this;
    }

    //=========================================================================
    //=========================================================================
    // Raw Order By Clause Method
    //=========================================================================
    //=========================================================================
    public function OrderByRaw($order_by)
    {
        self::AddItem($this->order_by, $order_by);
        return $this;
    }

    //##################################################################################
    //##################################################################################
    //##################################################################################
    // Protected / Internal Methods
    //##################################################################################
    //##################################################################################
    //##################################################################################

    //=========================================================================
    //=========================================================================
    // Format Order By Method
    //=========================================================================
    //=========================================================================
    protected function FormatOrderBy()
    {
        return self::FormatCSC('ORDER BY', $this->order_by);
    }

}
