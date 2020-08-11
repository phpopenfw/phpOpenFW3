<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Code Benchmark Plugin
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 */
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\Utility;

//**************************************************************************************
//**************************************************************************************
// Example
//**************************************************************************************
// $cb = new CodeBenchmark();
// $cb->start_timer();
// ##### YOUR CODE HERE #####
// $cb->stop_timer();
// $cb->print_results(true);
//**************************************************************************************
//**************************************************************************************

//**************************************************************************************
/**
 * Code Benchmark Class
 */
//**************************************************************************************
class CodeBenchmark
{
	private $start;
	private $stop;

	//************************************
	// Constructor Function
	//************************************
	public function __construct($start_timer=false)
	{
		$this->start = false;
		$this->stop = false;
		if ($start_timer) { $this->start = microtime(); }
	}

	//************************************
	// Microtime Pretty Function
	//************************************
	private function microtime_pretty($time)
	{
		list($usec, $sec) = explode(" ", $time);
    	return ((float)$usec + (float)$sec);
	}

	//************************************
	// Start Timer Function
	//************************************
	public function start_timer() { $this->start = microtime(); }

	//************************************
	// Stop Timer Function
	//************************************
	public function stop_timer() { $this->stop = microtime(); }

	//************************************
	// Return Raw Results Function
	//************************************
	public function get_raw_results()
	{
		return array('start' => $this->start, 'stop' => $this->stop);
	}

	//************************************
	// Return Results Function
	//************************************
	public function get_results() {
		return array('start' => $this->microtime_pretty($this->start), 'stop' => $this->microtime_pretty($this->stop));
	}

	//************************************
	// Print Results Function
	//************************************
	public function print_results($html=false) {
		$start = $this->microtime_pretty($this->start);
		$stop = $this->microtime_pretty($this->stop);

		print "Start time: $start";
		print ($html) ? ("<br/>\n") : ("\n");
		print "Stop time: $stop";
		print ($html) ? ("<br/>\n") : ("\n");
		$diff = $stop - $start;
		print "Elapsed time: $diff seconds";
		print ($html) ? ("<br/>\n") : ("\n");
	}
}
