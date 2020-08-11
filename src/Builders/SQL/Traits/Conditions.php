<?php
//**************************************************************************************
//**************************************************************************************
/**
 * SQL Conditions Trait
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 **/
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\Builders\SQL\Traits;
use \Closure;
use \phpOpenFW\Builders\SQL\Conditions\Condition;

//**************************************************************************************
/**
 * SQL Conditions Trait
 */
//**************************************************************************************
trait Conditions
{
    //==================================================================================
    //==================================================================================
	// Add Condition Method
    //==================================================================================
    //==================================================================================
	protected function AddCondition(&$conditions, $field, $op, $val, $type='s', $andor='and')
	{
        //-----------------------------------------------------------------
        // Get Lowercase Operator
        //-----------------------------------------------------------------
    	$lower_op = trim(strtolower($op));

        //-----------------------------------------------------------------
        // Validate Parameters
        //-----------------------------------------------------------------
        if (!$field) {
            $no_field_allowed = ['exists', 'not exists'];
            if (!in_array($lower_op, $no_field_allowed)) {
                throw new \Exception('Invalid first parameter. First parameter must be an object or a string indicating the field for the condition.');
            }
        }

        //-----------------------------------------------------------------
        // Single / Multiple Unnested Conditions
        //-----------------------------------------------------------------
        if (is_scalar($field) && is_string($field)) {
            $multi_value_ops = [
                'between', 'not between',
                'in', 'not in'
            ];
            if (is_array($val) && $val && !in_array($lower_op, $multi_value_ops)) {
                foreach ($val as $val2) {
                    $conditions[] = [$andor, Condition::Instance($this, $this->depth, $field, $op, $val2, $type)];
                }
            }
            else {
                $conditions[] = [$andor, Condition::Instance($this, $this->depth, $field, $op, $val, $type)];
            }
        }
        //-----------------------------------------------------------------
        // Nested Conditions, Sub-queries
        //-----------------------------------------------------------------
        else {
            $conditions[] = [$andor, Condition::Instance($this, $this->depth, $field, $op, $val, $type)];
        }
	}

    //==================================================================================
    //==================================================================================
	// Format Conditions Method
    //==================================================================================
    //==================================================================================
	protected function FormatConditions($conditions)
	{
        $clause = '';
        $front_pad = str_repeat(' ', ($this->depth * 2) + 2);
        foreach ($conditions as $condition) {
            if (is_array($condition)) {
                $str_condition = (string)$condition[1];
                if ($str_condition) {
                    if ($clause) {
                        $clause .= "\n{$front_pad}{$condition[0]} {$str_condition}";
                    }
                    else {
                        $clause .= "\n{$front_pad}{$str_condition}";
                    }
                }
            }
            else {
                $str_condition = (string)$condition;
                if ($str_condition) {
                    $clause .= "\n{$front_pad}{$str_condition}";
                }
            }
        }
        return $clause;
	}

}
