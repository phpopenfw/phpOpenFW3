<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Plugin for performing XML Transformations using XSL Stylesheets
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 **/
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\XML;

//**************************************************************************************
/**
 * XML Transform Class
 */
//**************************************************************************************
class Transform
{

	//*****************************************************************************
	/**
	 * XML Transformation Method
	 *
	 * Transform an XML string given an XSL template
	 * @param string XML data string
	 * @param string File path to an XSL template
	 */
	//*****************************************************************************
	public static function XSL($xml_data, $xsl_template, $show_xml_on_error=false, $use_cache=true)
	{
		//--------------------------------------------------------------
		// Load status variables
		//--------------------------------------------------------------
		$xml_load_status = '';
		$xsl_load_status = '';
	
		//--------------------------------------------------------------
		// Transform XML
		//--------------------------------------------------------------
		if (isset($xml_data) && isset($xsl_template) && !empty($xml_data) && !empty($xsl_template)) {
			
			//--------------------------------------------------------------
			// Load the XML source
			//--------------------------------------------------------------
			$xml = new \DOMDocument();
			$xml_load_status = $xml->loadXML($xml_data);
			if (!$xml_load_status) {
				trigger_error('Error: Malformed XML data given!!'); 
				if ($show_xml_on_error) { echo $xml_data; }
			}
	
			//--------------------------------------------------------------
			// Load the XSL Source
			//--------------------------------------------------------------
			if (file_exists($xsl_template)) {
				set_error_handler('\phpOpenFW\XML\Transform::HandleXSLError');
				$xsl = new \DOMDocument;
				$xsl_load_status = $xsl->load($xsl_template, LIBXML_NOCDATA);
				restore_error_handler();
			}
			else {
				trigger_error("Error: XSL Stylesheet '{$xsl_template}' does not exist!");
				return false;
			}
	
			//--------------------------------------------------------------
			// XSLT Processor (or XSLTCache) Object
			//--------------------------------------------------------------
			if (extension_loaded('xslcache') && $use_cache) {
				if ($xsl_load_status && $xml_load_status) { $proc = new \xsltCache; }
				else { $proc = new \XSLTProcessor; }
			}
			else { $proc = new \XSLTProcessor; }
			$proc->registerPHPFunctions();
	
			//--------------------------------------------------------------
			// Set the XSL Stylesheet and configure processor parameters
			//--------------------------------------------------------------
			if ($xsl_load_status) {
				if (extension_loaded('xslcache') && $use_cache) { $proc->importStyleSheet($xsl_template); }
				else { $proc->importStyleSheet($xsl); }
			}
			else {
				trigger_error('Error: XSL Stylesheet syntax errors occurred on load!!');
				return false;
			}
			
			//--------------------------------------------------------------
			// Transform XML
			//--------------------------------------------------------------
			if ($xml_load_status && $xsl_load_status) {
				$output = $proc->transformToXML($xml);
	
				//--------------------------------------------------------------
				// Check for successful output
				//--------------------------------------------------------------
				if ($output) {
					echo $output;
				}
				else {
					trigger_error('Error: XML Transformation Error!!');
					echo "<br/>\n{$xml_data}";
					return false;
				}
			}
		}
		//--------------------------------------------------------------
		// Return Data Only
		//--------------------------------------------------------------
		else if (isset($xml_data) && !empty($xml_data)) {
			echo $xml_data;
		}
		
		return true;
	}
	
	//*****************************************************************************
	/**
	 * XSL Load Error Handler
	 */
	//*****************************************************************************
	public static function HandleXSLError($errno, $errstr, $errfile, $errline)
	{
	    if ($errno == E_WARNING && (substr_count($errstr,'DOMDocument::load()') > 0)) {
	        print "<p>{$errstr}</p>\n";
	        return true;
	    }

	    return false;
	}
	
}
