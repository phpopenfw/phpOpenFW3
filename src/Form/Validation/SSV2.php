<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Improved Server Side Validation Class
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 **/
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\Form\Validation;

//**************************************************************************************
/**
 * SSV2 Class
 */
//**************************************************************************************
class SSV2
{
	//==============================================================
	// Member Variables
	//==============================================================
	/**
	 * @var array Validation Types
	 **/
	private $validation_types;
	
	/**
	 * @var array Are validations valid?
	 **/
	private $validations_valid;

	/**
	 * @var bool Validation Status
	 **/
	private $validation_status;

	/**
	 * @var array Validations to perform
	 **/
	private $validations;

	/**
	 * @var array Failed Validations
	 **/
	private $failed_checks;

	/**
	 * @var array Validation error messages
	 **/
	private $fail_messages;

	//======================================================================================
	//======================================================================================
	// Member Functions
	//======================================================================================
	//======================================================================================
	
	//**************************************************************************************
	//**************************************************************************************
	/**
	 * Constructor function
	 **/
	//**************************************************************************************
	//**************************************************************************************
	public function __construct()
	{
        //------------------------------------------------------------------
		// Validation Types
        //------------------------------------------------------------------
		$this->validation_types = [
		    'is_empty',
		    'is_empty_or_zero',
            'is_not_empty',
            'is_not_empty_or_zero',
            'is_numeric',
            'is_not_numeric',
            'is_date',
            'fields_match',
            'fields_not_match',
            'fail',
            'custom',
            'function'
		];

        //------------------------------------------------------------------
        // Reset
        //------------------------------------------------------------------
        $this->ResetAll();
	}

	//**************************************************************************************
	//**************************************************************************************
	/**
	 * Add Check
	 **/
	//**************************************************************************************
	//**************************************************************************************
	public function AddCheck()
	{
    	$args = func_get_args();

        //------------------------------------------------------------------
        // Parameters Passed as an Array
        //------------------------------------------------------------------
        if (count($args) == 1 && is_array($args[0])) {
            $params = $args[0];
        }
        //------------------------------------------------------------------
        // Custom Validation
        //------------------------------------------------------------------
        // 1) Function Name or Anonymous Function
        // 2) Fail Message
        //------------------------------------------------------------------
        else if (count($args) == 2) {
            if ($args[0] == '__fail__') {
                $params = [
        		    'field' => false, 
        		    'type' => 'fail',
        		    'message' => $args[1]
                ];                
            }
            else {
                $params = [
        		    'field' => false, 
        		    'type' => 'custom',
        		    'function' => $args[0],
        		    'message' => $args[1]
                ];
            }
        }
        //------------------------------------------------------------------
        // Normal Validation
        //------------------------------------------------------------------
        // 1) Field Name
        // 2) Validation Type
        // 3) Fail Message
        //------------------------------------------------------------------
        else if (count($args) == 3) {
            $params = [
                'field' => $args[0],
                'type' => $args[1],
                'message' => $args[2]
            ];
        }
        //------------------------------------------------------------------
        // Function Validation
        //------------------------------------------------------------------
        // 1) Field Name
        // 2) Validation Type (function)
        // 3) Function Name or Anonymous Function
        // 3) Fail Message
        //------------------------------------------------------------------
        else if (count($args) == 4 && $args[1] == 'function') {
            $params = [
    		    'field' => $args[0], 
    		    'type' => 'function',
    		    'function' => $args[2],
    		    'message' => $args[3]
            ];
        }
        //------------------------------------------------------------------
        // Invalid Validation
        //------------------------------------------------------------------
        else {
            trigger_error(__METHOD__ . ' - Invalid validation check parameters.');
            $this->validations_valid = false;
            return false;
        }

        //------------------------------------------------------------------
        // Validate "Message" Parameter
        //------------------------------------------------------------------
        if (empty($params['message'])) {
            trigger_error(__METHOD__ . ' - Missing validation failure message parameter.');
            $this->validations_valid = false;
            return false;
        }

        //------------------------------------------------------------------
        // Validate "Type" Parameters
        //------------------------------------------------------------------
        if (empty($params['type'])) {
            trigger_error(__METHOD__ . ' - Missing validation type parameter.');
            $this->validations_valid = false;
            return false;
        }
        else if (!in_array($params['type'], $this->validation_types)) {
            trigger_error(__METHOD__ . ' - Invalid validation type parameter.');
            $this->validations_valid = false;
            return false;
        }

        //------------------------------------------------------------------
        // Validate "Field" Parameter
        //------------------------------------------------------------------
        if ($params['type'] != 'custom' && $params['type'] != 'fail') {
            if (empty($params['field'])) {
                trigger_error(__METHOD__ . ' - Field parameter must be specified for validation types that are not function or custom.');
                $this->validations_valid = false;
                return false;
            }
        }

        //------------------------------------------------------------------
        // Validate "Function" Parameters
        //------------------------------------------------------------------
        if ($params['type'] == 'custom' || $params['type'] == 'function') {
            if (empty($params['function'])) {
                trigger_error(__METHOD__ . ' - Function parameter must be specified for custom and function validations.');
                $this->validations_valid = false;
                return false;
            }
            else if (!is_callable($params['function']) && (is_string($params['function']) && function_exists($params['function']))) {
                trigger_error(__METHOD__ . ' - Invalid function parameter specified for custom and function validation.');
                $this->validations_valid = false;
                return false;
            }
        }

        //------------------------------------------------------------------
        // Add Validation
        //------------------------------------------------------------------
        $this->validations[] = $params;
        return true;
	}

	//**************************************************************************************
	//**************************************************************************************
	/**
	 * Add Automatic Fail
	 **/
	//**************************************************************************************
	//**************************************************************************************
	public function AddFail($arg1, $arg2=false)
	{
        if (!empty($arg2)) {
            return $this->AddCheck($arg1, 'fail', $arg2);
        }
        return $this->AddCheck('__fail__', $arg1);
    }

	//**************************************************************************************
	//**************************************************************************************
	/**
     * Validate Function
     * @param Array The array of values to be validated
	 * @return bool Success - True, Failure - False
	 **/
	//**************************************************************************************
	//**************************************************************************************
	public function Validate($data)
	{
        //------------------------------------------------------------------
        // If validations are not valid, fail validation immediately
        //------------------------------------------------------------------
    	if (!$this->validations_valid) {
        	$this->validation_status = false;
        	return false;
    	}

        //------------------------------------------------------------------
        // Validations
        //------------------------------------------------------------------
        $this->validation_status = true;
		foreach ($this->validations as $key => $check) {

			//==============================================================
            // Process Current Validation
			//==============================================================
			$vr = $this->ProcessValidation($check, $data);

			//==============================================================
			// Result of current validation
			//==============================================================
			$this->check_status[$key] = $vr;
			if (!$vr) {
				$this->validation_status = false;
				$this->fail_messages[$key] = $check['message'];
				$tmp_field = (array_key_exists('field', $check)) ? ($check['field']) : (null);
				$this->failed_checks[$key] = [
    				'index' => $key,
    				'field' => $tmp_field,
    				'type' => $check['type'],
    				'message' => $check['message']
				]; 
			}
		}
		
		return $this->validation_status;
	}

	//**************************************************************************************
	//**************************************************************************************
	/**
     * Process Validation Method
     * @param Array Validation
	 * @return bool Success - True, Failure - False
	 **/
	//**************************************************************************************
	//**************************************************************************************
	protected function ProcessValidation($validation, $data)
	{
    	extract($validation);

        //==============================================================
		// Set / Reset variable values
		//==============================================================
		$vr = null;
		$var_val1 = null;
		$var_val2 = null;
		if (!empty($field)) {
    		if (is_array($field)) {
        		if (isset($field[0]) && isset($data[$field[0]])) {
        		    $var_val1 = $data[$field[0]];
                }
        		if (isset($field[1]) && isset($data[$field[1]])) {
        		    $var_val2 = $data[$field[1]];
                }
            }
            else {
        		if (isset($data[$field])) {
        		    $var_val1 = $data[$field];
                }
            }
        }

		//==============================================================
		// Perform Validation
		//==============================================================
		switch ($type) {

			case 'is_not_empty':
				$vr = ($var_val1 != '');
				break;

			case 'is_not_empty_or_zero':
				$vr = ($var_val1 != '' && (string)$var_val1 != '0');
				break;

			case 'is_empty':
				$vr = ($var_val1 == '');
				break;

			case 'is_empty_or_zero':
				$vr = ($var_val1 == '' || (string)$var_val1 != '0');
				break;

			case 'is_numeric':
				$vr = ($var_val1 !== '' && $var_val1 !== false) ? (is_numeric($var_val1)) : (true);
				break;

			case 'is_not_numeric':
				$vr = ($var_val1 !== '' && $var_val1 !== false) ? (!is_numeric($var_val1)) : (true);
				break;

			case 'fields_match':
				$vr = ($var_val1 == $var_val2);
				break;

			case 'fields_not_match':
				$vr = ($var_val1 != $var_val2);
				break;

			case 'is_date':
				$regex = '/^\d{1,2}\/\d{1,2}\/\d{4}$/';
				$vr = preg_match($regex, $var_val1);
				if (strlen($var_val1) > 10) { $vr = false; }
				break;

			case 'fail':
				$vr = false;
				break;
			
			case 'custom':
			case 'function':
                if (is_object($function)) {
                    if ($type == 'function') {
                        $vr = $function($var_val1, $var_val2);
                    }
                    else {
                        $vr = $function($data);
                    }                    
                }
                else {
                    if ($type == 'function') {
                        $vr = call_user_func_array($function, [$var_val1, $var_val2]);
                    }
                    else {
                        $vr = call_user_func_array($function, [$data]);
                    }
                }
				break;
		}

        return $vr;
    }

    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    // Setter Methods
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	//**************************************************************************************
	//**************************************************************************************
	/**
	 * Reset Method
	 **/
	//**************************************************************************************
	//**************************************************************************************
	public function Reset()
	{
		$this->validation_status = null;
		$this->failed_checks = [];
		$this->fail_messages = [];
    }

	//**************************************************************************************
	//**************************************************************************************
	/**
	 * Reset All Method
	 **/
	//**************************************************************************************
	//**************************************************************************************
	protected function ResetAll()
	{
    	$this->validations_valid = true;
		$this->validations = [];
		$this->Reset();
    }

    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    // Getter / Status Methods
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	//**************************************************************************************
	//**************************************************************************************
	/**
	 * Are Validations Valid
	 **/
	//**************************************************************************************
	//**************************************************************************************
	public function ValidationsValid() { return $this->validations_valid; }

	//**************************************************************************************
	//**************************************************************************************
	/**
	* Status
	* @return bool Returns the status of the server side validation (null before / true or false after)
	**/
	//**************************************************************************************
	//**************************************************************************************
	public function Status() { return $this->validation_status; }

	//**************************************************************************************
	//**************************************************************************************
	/**
	* Number of Failed Validations
	* @return integer Returns the number of failed validations
	**/
	//**************************************************************************************
	//**************************************************************************************
	public function NumFailedChecks() { return count($this->failed_checks); }

	//**************************************************************************************
	//**************************************************************************************
	/**
	* Fail Messages
	* @return array Returns the failure messages
	**/
	//**************************************************************************************
	//**************************************************************************************
	public function FailMessages() { return $this->fail_messages; }

	//**************************************************************************************
	//**************************************************************************************
	/**
	* Return the Failed Validations
	* @return array Returns the failed validations
	**/
	//**************************************************************************************
	//**************************************************************************************
	public function FailedChecks() { return $this->failed_checks; }

	//**************************************************************************************
	//**************************************************************************************
	/**
	* Return the Failed Validations Indexed by Field
	* @return array Returns the failed validations indexed by field
	**/
	//**************************************************************************************
	//**************************************************************************************
	public function FailuresByField()
	{
    	$tmp = [];
    	foreach ($this->failed_checks as $fc) {
        	$tmp_field = (!empty($fc['field'])) ? ($fc['field']) : ('__unknown__');
        	if (!isset($tmp[$tmp_field])) { $tmp[$tmp_field] = []; }
        	$tmp[$tmp_field][] = $fc;
    	}
    	return $tmp;
	}

	//**************************************************************************************
	//**************************************************************************************
	/**
	* Detail Status
	* @return bool Returns a detailed status of the server side validation
	**/
	//**************************************************************************************
	//**************************************************************************************
	public function DetailedStatus()
	{
    	return [
            'validations_valid' => $this->ValidationsValid(),
    	    'validation_status' => $this->Status(),
            'num_failures' => $this->NumFailedChecks(),
            'failed_checks' => $this->FailedChecks(),
            'fail_messages' => $this->FailMessages(),
            'failures_by_field' => $this->FailuresByField()
        ];
	}

}
