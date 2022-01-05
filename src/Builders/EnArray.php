<?php
//*****************************************************************************
//*****************************************************************************
/**
 * Enhanced Array Builder Class
 *
 * @package         phpopenfw/phpopenfw3/phpopenfw3
 * @author          Christian J. Clark
 * @copyright       Copyright (c) Christian J. Clark
 * @website         https://phpopenfw.org
 * @license         https://mit-license.org
 **/
//*****************************************************************************
//*****************************************************************************

namespace phpOpenFW\Builders;

//*****************************************************************************
/**
 * Enhanced Array Class
 */
//*****************************************************************************
class EnArray
{
    //=========================================================================
    // Class Members
    //=========================================================================
    protected $arr;

    //=========================================================================
    //=========================================================================
    // Constructor Method
    //=========================================================================
    //=========================================================================
    public function __construct(Array &$arr)
    {
        $this->arr = &$arr;
    }

    //=========================================================================
    //=========================================================================
    // To String Method
    //=========================================================================
    //=========================================================================
    public function __toString()
    {
        return json_encode($this->arr);
    }

    //=========================================================================
    //=========================================================================
    // To String Method
    //=========================================================================
    //=========================================================================
    public function Export()
    {
        return $this->arr;
    }

    //=========================================================================
    //=========================================================================
    // Get Method
    //=========================================================================
    //=========================================================================
    public function Get($path)
    {
        return \phpOpenFW\Helpers\UPN::Get('array:/' . $path, $this->arr);
    }

    //=========================================================================
    //=========================================================================
    // Set Method
    //=========================================================================
    //=========================================================================
    public function Set($path, $value, $set_root=false)
    {
        return \phpOpenFW\Helpers\UPN::Set('array:/' . $path, $value, $this->arr, $set_root);
    }

}
