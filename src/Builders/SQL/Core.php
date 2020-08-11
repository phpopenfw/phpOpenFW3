<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Statement Core Class
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 **/
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\Builders\SQL;

//**************************************************************************************
/**
 * Statement Core Class
 */
//**************************************************************************************
abstract class Core
{
    //==================================================================================
    // Traits
    //==================================================================================
    use Traits\Aux;
    use Traits\Conditions;

    //==================================================================================
	// Class Memebers
    //==================================================================================
	protected $db_type = 'mysql';
	protected $bind_params = [];
	protected $depth = 0;
	protected $parent_query = false;

    //==================================================================================
    //==================================================================================
    // Set Database Type Method
    //==================================================================================
    //==================================================================================
    public function SetDbType($type)
    {
        if ($type == 'mysqli') {
            $type = 'mysql';
        }
        if (!self::DbTypeIsValid($type)) {
            throw new \Exception('Invalid database type passed.');
        }
        $this->db_type = $type;
        return $this;
    }

    //==================================================================================
    //==================================================================================
    // Get Database Type Method
    //==================================================================================
    //==================================================================================
    public function GetDbType()
    {
        return $this->db_type;
    }

    //==================================================================================
    //==================================================================================
    // Get Bind Parameters Method
    //==================================================================================
    //==================================================================================
    public function &GetBindParams()
    {
		return $this->bind_params;
	}

    //==================================================================================
    //==================================================================================
    // Set Depth Method
    //==================================================================================
    //==================================================================================
    public function SetDepth(Int $depth)
    {
		$this->depth = $depth;
	}

    //==================================================================================
    //==================================================================================
    // Set Parent Query Method (Mainly used for Unions and Sub-queries)
    //==================================================================================
    //==================================================================================
    public function SetParentQuery($query)
    {
        $allowed_obj_types = [
            'phpOpenFW\Builders\SQL\Select',
            'phpOpenFW\Builders\SQL\Conditions\Condition',
            'phpOpenFW\Builders\SQL\Conditions\Nested'
        ];
        if (gettype($query) == 'object') {
            if (in_array(get_class($query), $allowed_obj_types)) {
                $this->parent_query = $query;
                return true;
            }
        }
		throw new \Exception('Invalid Parent Type.');
	}

    //==================================================================================
    //==================================================================================
    // Merge Bind Parameters Method
    //==================================================================================
    //==================================================================================
    public function MergeBindParams(Array $new_params)
    {
        //-----------------------------------------------------------------
        // Validate New Parameters
        //-----------------------------------------------------------------
        if (!$new_params) {
            return false;
        }
        $start_index = count($this->bind_params);

        //-----------------------------------------------------------------
        // MySQL
        //-----------------------------------------------------------------
		if ($this->db_type == 'mysql') {
    		if ($new_params <= 1) {
        		return false;
    		}
    		if ($start_index == 0) {
        		$this->bind_params[] = '';
    		}
    		foreach ($new_params as $np_index => $new_param) {
        		if ($np_index == 0) { continue; }
        		$np_index--;
        		$tmp_type = substr($new_params[0], $np_index, 1);
        		$this->bind_params[0] .= $tmp_type;
        		$this->bind_params[] = $new_param;
            }
		}
        //-----------------------------------------------------------------
        // Oracle
        //-----------------------------------------------------------------
		else if ($this->db_type == 'oracle') {
    		foreach ($new_params as $new_param) {
        		$new_index = 'p' . $start_index;
        		$this->bind_params[$new_index] = $new_param;
        		$start_index++;
    		}
		}
        //-----------------------------------------------------------------
        // Everything else. (PostgreSQL, SQL Server, etc.)
        //-----------------------------------------------------------------
		else {
    		$this->bind_params = array_merge($this->bind_params, $new_params);
		}
	}

    //==================================================================================
    //==================================================================================
    // Add a Bind Parameter
    //==================================================================================
    //==================================================================================
    protected function AddBindParam($value, $type='s')
    {
        //-----------------------------------------------------------------
        // Is Database Type Valid?
        //-----------------------------------------------------------------
        if (!self::DbTypeIsValid($this->db_type)) {
            throw new \Exception('Invalid database type.');
        }

        //-----------------------------------------------------------------
        // Validate that Value is Scalar
        //-----------------------------------------------------------------
        if (!is_scalar($value)) {
            throw new \Exception('Value must be a scalar value.');
        }

        //-----------------------------------------------------------------
        // Which Class is using this trait?
        //-----------------------------------------------------------------
        // (i.e. How do we add the bind parameter?)
        //-----------------------------------------------------------------
        switch ($this->db_type) {

            //-----------------------------------------------------------------
            // MySQL
            //-----------------------------------------------------------------
            case 'mysql':
            case 'mysqli':
                if (count($this->bind_params) == 0) {
                    $this->bind_params[] = '';
                }
                $this->bind_params[0] .= $type;
                $this->bind_params[] = $value;
                return '?';
                break;

            //-----------------------------------------------------------------
            // PgSQL
            //-----------------------------------------------------------------
            case 'pgsql':
                $index = count($this->bind_params);
                $ph = '$' . $index;
                if (isset($this->bind_params[$index])) {
                    throw new \Exception('An error occurred trying to add the PostgreSQL bind parameter. Parameter index already in use.');
                }
                $this->bind_params[$index] = $value;
                return $ph;
                break;

            //-----------------------------------------------------------------
            // Oracle
            //-----------------------------------------------------------------
            case 'oracle':
                $index = count($this->bind_params);
                $ph = 'p' . $index;
                if (isset($this->bind_params[$ph])) {
                    throw new \Exception('An error occurred trying to add the Oracle bind parameter. Parameter index already in use.');
                }
                $this->bind_params[$ph] = $value;
                return ':' . $ph;
                break;

            //-----------------------------------------------------------------
            // Default
            //-----------------------------------------------------------------
            default:
                $this->bind_params[] = $value;
                return '?';
                break;

        }
    }

    //==================================================================================
    //==================================================================================
    // Add a Bind Parameters
    //==================================================================================
    //==================================================================================
    protected function AddBindParams(Array $values, $type='s')
    {
        $place_holders = '';
        foreach ($values as $value) {
            $tmp_ph = $this->AddBindParam($value, $type);
            $place_holders .= ($place_holders) ? (', ' . $tmp_ph) : ($tmp_ph);
        }
        return $place_holders;
    }

}
