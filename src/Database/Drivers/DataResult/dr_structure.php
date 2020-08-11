<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Data Result Structure Class
 * An abstract data result structure class
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 * @access		private
 */
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\Database\Drivers\DataResult;

//**************************************************************************************
/**
 * Data Result Class
 */
//**************************************************************************************
abstract class dr_structure {

	//************************************************************************	
	// Class variables
	//************************************************************************
	/**
	* @var string Data source index as specified in the configuration
	**/
	protected $data_src;

	/**
	* @var string Data source type (mysql, mysqli, pgsql, oracle, mssql, db2, sqlsrv, sqlite)
	**/
	protected $data_type;

	/**
	* @var string Display Data Type 
	**/
	protected $disp_dt;

	/**
	* @var resource Internal Result Handle or Object 
	**/
	protected $resource;

	/**
	* @var resource Internal Connection Handle. Not required and not necessary for most drivers.
	**/
	protected $handle;

	/**
	* @var array The records returned from the result set
	**/
	protected $records;

	/**
	* @var bool A flag to determine if the records have been cached/saved.
	**/
	protected $records_set;

	/**
	* @var int The number of records in the record set.
	**/
	protected $num_recs;

	/**
	* @var int The number of fields in the record set.
	**/
	protected $num_fields;

	/**
	* @var int Internal Flags / Counters
	**/
	protected $flags;

	/**
	* Constructor function
	*
	* Initializes data result object
	*
	* @param mixed The result resource id or object
	* @param string A valid data source handle as specified in the configuration
	**/
	//*************************************************************************
	// Constructor function
	//*************************************************************************
	public function __construct($resource, $data_src='', $opts=false)
	{
		//==================================
		// Data Source
		//==================================
		if ($data_src != '') {
			if (!isset($_SESSION[$data_src])) {
				$this->display_error(__FUNCTION__, "Invalid Data Source '{$data_src}'.");
				return false;
			}
		}
		else {
			if (isset($_SESSION['default_data_source'])) {
				$data_src = $_SESSION['default_data_source'];
			}
			else {
				$this->display_error(__FUNCTION__, 'Data Source not given and default data source is not set.');
				return false;
			}
		}
		$this->data_src = $data_src;

		//==================================
		// Data Type
		//==================================
        $this->data_type = $_SESSION[$this->data_src]['type'];
        $this->disp_dt = '[' . strtoupper('dr_' . $this->data_type) . ']';

		//==================================
		// Resource
		//==================================
		if (gettype($resource) != 'object' && gettype($resource) != 'resource') {
			$this->display_error(__FUNCTION__, 'Invalid resource handle or object.');
			return false;
		}
		$this->resource = $resource;

		//==================================
		// Handle / Statement Handle
		//==================================
		$this->handle = (isset($opts['handle'])) ? ($opts['handle']) : (false);
		$this->stmt = (isset($opts['stmt'])) ? ($opts['stmt']) : (false);

		//==================================
		// Options
		//==================================
		if (is_array($opts)) {
			foreach ($opts as $opt_key => $opt_val) { $this->set_opt($opt_key, $opt_val); }
		}

		//==================================
		// Defaults
		//==================================
		$this->num_recs = false;
		$this->num_fields = false;
		$this->records = array();
		$this->records_set = false;
		$this->afftected_rows = false;
		$this->opts = array();
		$this->flags = array();
		$this->reset_flags();

		//==================================
		// Pre-setup Method
		//==================================
		if (method_exists($this, 'pre_setup')) {
			$this->pre_setup();
		}

		//==================================
		// Determine Number of Rows/Fields
		//==================================
		$this->set_num_rows();
		$this->set_num_fields();
	}

	//*************************************************************************
	/**
	* Destructor Function
	**/
	//*************************************************************************
    public function __destruct() {}

	//*************************************************************************
	/**
	* Display Error Function
	**/
	//*************************************************************************
	protected function display_error($function, $msg)
	{
		$class = __CLASS__;
		trigger_error("Error: [{$class}]::{$function}(): {$msg}");
	}

	//*************************************************************************
	/**
	* Set the Number of Rows in the current result set
	**/
	//*************************************************************************
	protected function set_num_rows() { $this->num_recs = false; }

	//*************************************************************************
	/**
	* Get the number records in a result set. A False value means the value could not be determined.
	**/
	//*************************************************************************
	public function num_rows() { return $this->num_recs; }

	//*************************************************************************
	/**
	* Get the number fields in a result set. A False value means the value could not be determined.
	**/
	//*************************************************************************
	public function num_fields() { return $this->num_fields; }

    //*************************************************************************
	/**
	* Get Option Function
	* @param string The key of the value to retrieve
	**/
	//*************************************************************************
    public function get_opt($opt)
    {
    	$opt = strtoupper((string)$opt);
    	if ($opt == '') { return false; }
    	if (isset($this->inst_opts[$opt])) { return $this->inst_opts[$opt]; }
    	return false;
    }

    //*************************************************************************
	/**
	* Set Option Function
	* @param string The key of the value to set
	* @param string The value to set
	**/
	//*************************************************************************
    public function set_opt($opt, $val=false)
    {
    	$opt = strtoupper((string)$opt);
    	if ($opt == '') { return false; }
    	$this->inst_opts[$opt] = $val;
    	return true;
    }

	//*************************************************************************
	/**
	* Get Raw Resource
	* @return The raw resource or statement handle returned from the query.
	**/
	//*************************************************************************
	public function get_raw_resource()
	{
		return $this->resource;
	}

	//*************************************************************************
	/**
	* Function to display a "Not Available" Message
	**/
	//*************************************************************************
	protected function not_available($function)
	{
		trigger_error("The function {$function}() is not available with the {$this->disp_dt} class.", E_USER_WARNING);
		return false;
	}

    //*************************************************************************
	/**
	* Default Set Result Pointer Function
	**/
	//*************************************************************************
    public function set_result_pointer($offset=0)
    {
    	$this->reset_flags();
    }

    //*************************************************************************
	/**
	* Reset Flags Function
	**/
	//*************************************************************************
    public function reset_flags()
    {
    	$this->flags['fetch_row'] = 0;
    	$this->flags['fetch_all_rows'] = 0;    
    }

	//*************************************************************************
	/**
	* Fetch all rows in a result
	**/
	//*************************************************************************
	public function fetch_all_rows()
	{
		if (!$this->records_set) {

			// Reset Result Pointer
			@$this->set_result_pointer();

			// Pull All Rows
			while ($row = $this->fetch_row()) {
	            array_push($this->records, $row);
	        }

		    if ($this->records) {
		    	$this->records_set = true;
		    	$this->num_recs = count($this->records);
		    }
	    }

	    $this->flags['fetch_all_rows']++;
	    return $this->records;
	}

	//*************************************************************************
	/**
	* Fetch all rows in a result (Used for Oracle, DB2, and SQLSRV)
	**/
	//*************************************************************************
	protected function fetch_all_rows2()
	{
		if (!$this->records_set) {

			if ($this->flags['fetch_row']) {
				return false;
			}

			while ($row = $this->fetch_row()) {
	            array_push($this->records, $row);
	        }

		    if ($this->records) {
		    	$this->records_set = true;
		    	$this->num_recs = count($this->records);
		    }
	    }

	    $this->flags['fetch_all_rows']++;
	    return $this->records;
	}

}

