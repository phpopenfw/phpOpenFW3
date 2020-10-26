<?php
//*****************************************************************************
//*****************************************************************************
/**
 * Command Line Interface Class
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 **/
//*****************************************************************************
//*****************************************************************************

namespace phpOpenFW\CLI;

//*****************************************************************************
/**
 * CLI Class
 */
//*****************************************************************************
class CLI
{
	//*************************************************************************
	//*************************************************************************
	// Class Members
	//*************************************************************************
	//*************************************************************************
	protected $app_path = false;
	protected $config = [];
	protected $args = [];
	protected $mod_title = false;

	//*************************************************************************
	//*************************************************************************
	// Get Instanace
	//*************************************************************************
	//*************************************************************************
	public static function GetInstance($app_path, $raw_args)
	{
    	//=====================================================================
		// Valid Environment?
    	//=====================================================================
		if (strtoupper(php_sapi_name()) != 'CLI') {
			self::PrintError('Invalid environment.');
			exit;
		}
    	//=====================================================================
		// Validate App Path
    	//=====================================================================
		if (!is_dir($app_path)) {
			self::PrintError('Invalid application path.');
			return false;
		}

    	//=====================================================================
		// Parse Arguments
    	//=====================================================================
		if (!$args = self::ParseArgs($raw_args)) {
			self::PrintError('Invalid arguments.');
			return false;
		}

    	//=====================================================================
		// Return New CLI Object
    	//=====================================================================
		return new static($app_path, $args);
	}

	//*************************************************************************
	//*************************************************************************
	// Constructor
	//*************************************************************************
	//*************************************************************************
	public function __construct($app_path, Array $args=[])
	{
		$this->app_path = $app_path;
		define('CLI_APP_ROOT', $app_path);
		$this->args = $args;

		//=====================================================================
        // Load Configuration
		//=====================================================================
		$config = \phpOpenFW\Core::LoadConfiguration();
		if ($config->IsValid()) {
			$this->config = $config->Export();
		}

    	//=====================================================================
    	// Load Data Sources
    	//=====================================================================
    	\phpOpenFW\Core::LoadDataSources([
        	'display_errors' => false
    	]);
	}

	//*************************************************************************
	//*************************************************************************
	// Run Job
	//*************************************************************************
	//*************************************************************************
	public function Run()
	{
		//=============================================================
		// Extract Arguments
		//=============================================================
		extract($this->args , EXTR_PREFIX_ALL, 'arg');

		//=============================================================
		// Check Parameters
		//=============================================================
		if (empty($this->args['j'])) {
			self::PrintErrorExit('No job specified (Use -j option).');
		}
		else {
			$mod_title = $this->args['j'];
			$this->mod_title = $mod_title;
		}

		//====================================================================
		// Include Job
		//====================================================================
		$job = (isset($this->args['j'])) ? ($this->args['j']) : (false);
		$job_dir = "{$this->app_path}/controllers/{$job}";
		if ($job && is_dir($job_dir)) {

			//=============================================================
			// Job Controller
			//=============================================================
			$job_controller = "{$job_dir}/controller.php";
			if (!file_exists($job_controller)) {
				self::PrintErrorExit('Unable to find job controller.');
			}
		
			//=============================================================
			// Job local.inc.php
			//=============================================================
			$job_local = "{$job_dir}/local.var.php";
			if (file_exists($job_local)) {
				include($job_local);
				if (!empty($mod_title)) {
					$this->mod_title = $mod_title;
				}
			}

			//=============================================================
			// Job Title
			//=============================================================
			$this->PrintTitle($mod_title);

			//=============================================================
			// Application CLI Pre-Script File
			//=============================================================
			$pre_script = "{$this->app_path}/pre_cli.inc.php";
			if (file_exists($pre_script)) { include($pre_script); }

			//=============================================================
			// Environment
			//=============================================================
            $this->SetEnv();
		
			//=============================================================
			// Run Mode
			//=============================================================
			$run_mode = false;
			if (isset($this->args['run_mode'])) {
				$run_mode = $this->args['run_mode'];
				define('RUN_MODE', $run_mode);
				$tmp_msg = "Run Mode is '{$run_mode}'";
				self::PrintMessage($tmp_msg, 0, '*');
			}
			define('RUN_MODE', $run_mode);

			//=============================================================
			// Verbose
			//=============================================================
			$verbose = (isset($this->args['v'])) ? (true) : (false);
			define('VERBOSE', $verbose);
			if ($verbose) {
				$tmp_msg = "Verbose output is ON";
				self::PrintMessage($tmp_msg, 0, '*');
			}
		
			//=============================================================
			// Call Controller
			//=============================================================
            $this->PrintOutputHeader();
			include($job_controller);
		
			//=============================================================
			// Application CLI Post-Script File
			//=============================================================
			$post_script = "{$this->app_path}/post_cli.inc.php";
			if (file_exists($post_script)) { include($post_script); }
		
		}
		else {
			self::PrintErrorExit('Invalid job.');
		}		
	}

	//*************************************************************************
	//*************************************************************************
	// Run Job
	//*************************************************************************
	//*************************************************************************
	protected static function SetEnv()
	{
		//---------------------------------------------------------
		// Environment from CLI
		//---------------------------------------------------------
		if (!empty($this->args['env'])) {
			define('ENV', $this->args['env']);
		}
		//---------------------------------------------------------
		// Environment from Config
		//---------------------------------------------------------
		else if (!empty($this->config['env'])) {
			define('ENV', $this->config['env']);
		}
		//---------------------------------------------------------
		// Environment from GLOBALS
		//---------------------------------------------------------
		else if (!empty($GLOBALS['env'])) {
			define('ENV', $GLOBALS['env']);
		}

		//---------------------------------------------------------
		// Display Environment
		//---------------------------------------------------------
		if (defined('ENV')) {
			$env = ENV;
			$tmp_msg = "Environment is '{$env}'";
			self::PrintMessage($tmp_msg, 0, '*');
		}
    }

	//*************************************************************************
	//*************************************************************************
	// Parse Arguments
	//*************************************************************************
	//*************************************************************************
	protected static function ParseArgs($args)
	{
		//print_r($args);
		$num_args = count($args);
		$new_args = array();
		$open_arg = false;
		for ($i = 1; $i < $num_args; $i++) {

			//------------------------------------------------------
			// Current Argument
			//------------------------------------------------------
			$arg = $args[$i];

			//------------------------------------------------------
			// Next Argument
			//------------------------------------------------------
			$next_arg = (isset($args[$i+1])) ? ($args[$i+1]) : (false);

			//------------------------------------------------------
			// Current Argument's First Character
			//------------------------------------------------------
			$arg_1char = substr($arg, 0, 1);

			//------------------------------------------------------
			// Current Argument's First Two Characters
			//------------------------------------------------------
			$arg_2char = substr($arg, 0, 2);

			//------------------------------------------------------
			// Parse Switches
			//------------------------------------------------------
			if ($arg_1char == '-' && strlen($arg) == 2) {
				$switch = substr($arg, 1);
				if (strlen($switch) > 1) {
					$switches = str_split($switch);
					foreach ($switches as $curr_sw) { $new_args[$curr_sw] = false; }
					$open_arg = $curr_sw;
				}
				else {
					$open_arg = $switch;
					$new_args[$switch] = false;
				}
			}
			else if ($arg_2char == '--') {
				$switch = substr($arg, 2);
				$open_arg = $switch;
				$new_args[$switch] = false;
			}
			//------------------------------------------------------
			// Parse Switches
			//------------------------------------------------------
			else {
				if ($open_arg) {
					$new_args[$open_arg] = $arg;
					$open_arg = false;
				}
				else {
					$new_args['loose_args'][] = $arg;
				}
			}
		}
		return $new_args;
	}

	//#########################################################################
	//#########################################################################
	// Message Printing Methods
	//#########################################################################
	//#########################################################################

	//*************************************************************************
	//*************************************************************************
	// Print Title
	//*************************************************************************
	//*************************************************************************
	protected static function PrintTitle($title)
	{
    	if ($mod_title) {
            print "\n**********************************************************************\n";
            print ">>>>> {$mod_title} <<<<<";
            print "\n**********************************************************************\n";
        }
	}

	//*************************************************************************
	//*************************************************************************
	// Print Output Header
	//*************************************************************************
	//*************************************************************************
	protected static function PrintOutputHeader()
	{
    	$text = 'Job Output';
		print "\n**********************************************************************";
		print "\n*** {$text}";
		print "\n**********************************************************************\n";
    }

	//*************************************************************************
	//*************************************************************************
	// Print Confirmation
	//*************************************************************************
	//*************************************************************************
	protected static function PrintConfirmation($msg, $depth=0)
	{
		self::PrintMessage($msg, $depth, '[ok]');
	}

	//*************************************************************************
	//*************************************************************************
	// Print Warning
	//*************************************************************************
	//*************************************************************************
	protected static function PrintWarning($msg, $depth=0)
	{
		self::PrintMessage($msg, $depth, '[!!]');
	}

	//*************************************************************************
	//*************************************************************************
	// Print Error
	//*************************************************************************
	//*************************************************************************
	protected static function PrintError($msg, $depth=0)
	{
		self::PrintMessage($msg, $depth, "\n** [ERROR] ** :");
	}

	//*************************************************************************
	//*************************************************************************
	// Print Message
	//*************************************************************************
	//*************************************************************************
	protected static function PrintMessage($msg, $depth=0, $prefix='')
	{
		$tabs = '';
		if ($depth) {
			$tabs = str_pad('', $depth, "\t", STR_PAD_LEFT);
		}
		if ($tabs) { print $tabs; }
		if ($prefix) { print "{$prefix} "; }
		print "{$msg}\n";
	}

	//*************************************************************************
	//*************************************************************************
	// Print Error and Exit
	//*************************************************************************
	//*************************************************************************
	protected static function PrintErrorExit($msg)
	{
		print "\n** [ERROR] ** : {$msg}\n";
		print "\n**********************************************************************";
		print "\n*** Exited with Error";
		print "\n**********************************************************************\n\n";
		exit;
	}

}
