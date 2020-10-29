<?php
//*****************************************************************************
//*****************************************************************************
/**
 * SQL Having Trait
 *
 * @package         phpOpenFW
 * @author          Christian J. Clark
 * @copyright       Copyright (c) Christian J. Clark
 * @license         https://mit-license.org
 **/
//*****************************************************************************
//*****************************************************************************

namespace phpOpenFW\Builders\SQL\Traits;

//*****************************************************************************
/**
 * SQL Having Trait
 */
//*****************************************************************************
trait Having
{
    //=========================================================================
    // Trait Memebers
    //=========================================================================
    protected $having = [];

    //=========================================================================
    //=========================================================================
    // Having Method
    //=========================================================================
    //=========================================================================
    public function Having($field, $op=null, $val=false, $type='s', $andor='and')
    {
        $this->AddCondition($this->having, $field, $op, $val, $type, $andor);
        return $this;
    }

    //=========================================================================
    //=========================================================================
    // Or Having Method
    //=========================================================================
    //=========================================================================
    public function OrHaving($field, $op=null, $val=false, $type='s')
    {
        $this->Having($field, $op, $val, $type, 'or');        
        return $this;
    }

    //=========================================================================
    //=========================================================================
    // Having Raw Method
    //=========================================================================
    //=========================================================================
    public function HavingRaw(String $having_raw, $andor='and')
    {
        if ($having_raw) {
            $this->having[] = [$andor, $having_raw];
        }
        return $this;
    }

    //=========================================================================
    //=========================================================================
    // Or Having Raw Method
    //=========================================================================
    //=========================================================================
    public function OrHavingRaw(String $having_raw)
    {
        $this->HavingRaw($having_raw, 'or');
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
    // Format Having Clause Method
    //=========================================================================
    //=========================================================================
    protected function FormatHaving()
    {
        $clause = $this->FormatConditions($this->having);
        if ($clause) {
            $clause = "HAVING " . $clause;
        }
        return $clause;
    }

}
