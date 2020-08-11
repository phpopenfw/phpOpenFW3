<?php
//**************************************************************************************
//**************************************************************************************
/**
 * SQL Values Trait
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 **/
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\Builders\SQL\Traits;

//**************************************************************************************
/**
 * SQL Values Trait
 */
//**************************************************************************************
trait Values
{
    //=========================================================================
	// Trait Memebers
    //=========================================================================
	protected $set_fields = [];

    //=========================================================================
    //=========================================================================
	// Values Method
    //=========================================================================
    //=========================================================================
	public function Values(Array $values)
	{
        foreach ($values as $key => $value) {
            if (!is_numeric($key)) {
                if (is_array($value)) {
                    $value = array_merge([$key], $value);
                }
                else {
                    $value = [$key, $value];
                }
            }
        	if (!isset($value[0]) || !array_key_exists(1, $value)) {
            	throw new \Exception('Invalid value specified while setting values. (1)');
        	}
        	$val1 = $value[0];
        	$val2 = $value[1];
        	$val3 = (!empty($value[2])) ? ($value[2]) : (false);
        	$this->Value($val1, $val2, $val3);
        }

        return $this;
	}

    //=========================================================================
    //=========================================================================
	// Values Method
    //=========================================================================
    //=========================================================================
	public function Value($field, $value, $type='s')
	{
        if (!is_scalar($field) || is_numeric($field)) {
            throw new \Exception('Invalid field name specified while setting value.');
        }
        if (!is_scalar($value) && !is_null($value)) {
            throw new \Exception('Invalid value specified while setting value.');
        }
        if (!$type) { $type = 's'; }
        $this->set_fields[] = [$field, $value, $type];
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
    // Format Values Method
    //=========================================================================
    //=========================================================================
    protected function FormatValues()
    {
        //-------------------------------------------------------
        // Were Values Set?
        //-------------------------------------------------------
        if (!$this->set_fields) {
            throw new \Exception("No values have been set for the {$this->sql_type} statement.");
        }

        //-------------------------------------------------------
        // Insert Statements
        //-------------------------------------------------------
        if ($this->sql_type == 'insert') {
            $fields = '';
            $place_holders = '';
            foreach ($this->set_fields as $set_field) {
                if ($fields) { $fields .= ','; }
                $fields .= "\n  "; 
                if ($place_holders) { $place_holders .= ','; }
                $place_holders .= "\n  ";
                $fields .= $set_field[0];
                if (is_null($set_field[1])) {
                    $place_holders .= 'NULL';
                }
                else {
                    $place_holders .= $this->AddBindParam($set_field[1], $set_field[2]);
                }
            }
            return [$fields, $place_holders];
        }
        //-------------------------------------------------------
        // Update Statements
        //-------------------------------------------------------
        else if ($this->sql_type == 'update') {
            $set = '';
            foreach ($this->set_fields as $set_field) {
                if ($set) { $set .= ', '; }
                $set .= "\n  ";
                if (is_null($set_field[1])) {
                    $place_holder = 'NULL';
                }
                else {
                    $place_holder = $this->AddBindParam($set_field[1], $set_field[2]);
                }
                $set .= "{$set_field[0]} = {$place_holder}";
            }
            return $set;
        }

        //-------------------------------------------------------
        // Everything Else, False.
        //-------------------------------------------------------
        return false;
	}

}
