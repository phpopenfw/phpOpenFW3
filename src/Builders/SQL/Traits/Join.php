<?php
//*****************************************************************************
//*****************************************************************************
/**
 * SQL Join Trait
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
use \Closure;
use \phpOpenFW\Builders\SQL\Conditions\Condition;

//*****************************************************************************
/**
 * SQL Join Trait
 */
//*****************************************************************************
trait Join
{
    //=========================================================================
    // Trait Memebers
    //=========================================================================

    //=========================================================================
    //=========================================================================
    // Join Method
    //=========================================================================
    //=========================================================================
    public function Join(String $table, $field1, String $op='', String $field2='')
    {
        $this->AddJoin('join', $table, $field1, $op, $field2);
        return $this;
    }

    //=========================================================================
    //=========================================================================
    // Inner Join
    //=========================================================================
    //=========================================================================
    public function InnerJoin(String $table, $field1, String $op='', $field2='')
    {
        $this->AddJoin('inner', $table, $field1, $op, $field2);
        return $this;
    }

    //=========================================================================
    //=========================================================================
    // Outer Join
    //=========================================================================
    //=========================================================================
    public function OuterJoin(String $table, $field1, String $op='', String $field2='')
    {
        $this->AddJoin('outer', $table, $field1, $op, $field2);
        return $this;
    }

    //=========================================================================
    //=========================================================================
    // Cross Join
    //=========================================================================
    //=========================================================================
    public function CrossJoin(String $table)
    {
        $this->AddJoin('cross', $table, '');
        return $this;
    }

    //=========================================================================
    //=========================================================================
    // Left Join
    //=========================================================================
    //=========================================================================
    public function LeftJoin(String $table, $field1, String $op='', String $field2='')
    {
        $this->AddJoin('left', $table, $field1, $op, $field2);
        return $this;
    }

    //=========================================================================
    //=========================================================================
    // Right Join
    //=========================================================================
    //=========================================================================
    public function RightJoin(String $table, $field1, String $op='', String $field2='')
    {
        $this->AddJoin('right', $table, $field1, $op, $field2);
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
    // Add Join Clause
    //=========================================================================
    //=========================================================================
    protected function AddJoin(String $join_type, String $table, $field1, String $op='', String $field2='')
    {
        //---------------------------------------------------------------------
        // Validate Parameters
        //---------------------------------------------------------------------
        if (!$table) {
            throw new \Exception('Invalid table name given.');
        }
        $join_phrase = $this->GetJoinPhrase($join_type);

        //---------------------------------------------------------------------
        // Cross Join ONLY
        //---------------------------------------------------------------------
        if ($join_type == 'cross') {
            $this->from[] = ['join', 'CROSS JOIN ' . $table];
            return true;
        }

        //---------------------------------------------------------------------
        // Advanced Join Clause
        //---------------------------------------------------------------------
        if ($field1 instanceof Closure) {
            $this->from[] = [
                'join', 
                $join_phrase,
                $table,
                Condition::Instance($this, $this->depth, $field1, false, false, false)
            ];
            return true;
        }
        //---------------------------------------------------------------------
        // Single Condition Join
        //---------------------------------------------------------------------
        else if (is_scalar($field1) && is_string($field1)) {
            $this->from[] = [
                'join', 
                "{$join_phrase} {$table} ON {$field1} {$op} {$field2}"
            ];
            return true;
        }

        //---------------------------------------------------------------------
        // Invalid Join Parameters
        //---------------------------------------------------------------------
        throw new \Exception('Invalid join parameters given.');
    }

    //=========================================================================
    //=========================================================================
    // Get Join Phrase
    //=========================================================================
    //=========================================================================
    protected function GetJoinPhrase(String $join_type)
    {
        switch (strtolower($join_type)) {
            case 'join':
                return 'JOIN';
            case 'inner':
                return 'INNER JOIN';
            case 'outer':
                return 'OUTER JOIN';
            case 'cross':
                return 'CROSS JOIN';
            case 'left':
                return 'LEFT JOIN';
            case 'right':
                return 'RIGHT JOIN';
            default:
                return false;
        }
    }
}
