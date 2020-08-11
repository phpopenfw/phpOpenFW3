<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Plugin for performing XML Formatting
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 **/
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\XML;
use phpOpenFW\XML\GenElement;

//**************************************************************************************
/**
 * XML Format Class
 */
//**************************************************************************************
class Format
{

	//*****************************************************************************
	/**
	 * Generate XML Data from an array
	 *
	 * @param string Top level XML data element name
	 * @param array Array to generate XML from
	 * @param string Prefix to use for numeric element names
	 */
	//*****************************************************************************
	public static function array2xml($element, $data, $num_prefix='data_', $depth=0, $count=0)
	{
		//--------------------------------------------------------------
		// Prefix numeric elements
		//--------------------------------------------------------------
		if (is_numeric($element)) { $element = $num_prefix . $depth . '_' . $count; }
		
		//--------------------------------------------------------------
		// Create Indent Tabs
		//--------------------------------------------------------------
		$indent = '';
		for ($x = 0; $x < $depth; $x++) {
			$indent .= "\t";
		}
	
		if ($element === '') {
			trigger_error("Error: generate_xml(): XML Element name not supplied! (depth: {$depth})");
			return false;
		}
		
		if (is_array($data)) {
			$xml = "{$indent}<{$element}>\n";
			$count = 0;
			foreach ($data as $key => $row_data) {
				$xml .= self::array2xml($key, $row_data, $num_prefix, $depth+1, $count);
				$count++;	
			}
			$xml .= "{$indent}</{$element}>\n";	
		}
		else {
			$xml = "{$indent}<{$element}>{$data}</{$element}>\n";
		}
	
		return $xml;
	}
	
	//*****************************************************************************
	/**
	 * Return a string as valid XML escaped value
	 *
	 * @param string Data to escape
	 * @return string Escaped data
	 */
	//*****************************************************************************
	public static function xml_escape($str_data)
	{
		if ($str_data !== '') {
			return '<![CDATA[' . self::strip_cdata_tags($str_data) . ']]>';
		}
		else { return false; }
	}
	
	//*****************************************************************************
	/**
	 * Return an array with valid XML escaped values
	 *
	 * @param array Data to escape
	 * @return array Escaped data
	 */
	//*****************************************************************************
	public static function xml_escape_array($in_data)
	{
		if (is_array($in_data)) {
			foreach ($in_data as $key => $item) {
				$in_data[$key] = self::xml_escape_array($item);
			}
			return $in_data;
		}
		else if ($in_data !== '' && !is_numeric($in_data)) {
			return '<![CDATA[' . self::strip_cdata_tags($in_data) . ']]>';
		}
		else if (is_numeric($in_data))
		{
			return $in_data;
		}
		else { return false; }
	}
	
	//*****************************************************************************
	/**
	 * Strip all CDATA begin and end tags from the string passed.
	 *
	 * @param string String to strip CDATA tags from
	 * @return string Cleaned string
	 */
	//*****************************************************************************
	public static function strip_cdata_tags($str_data)
	{
		settype($str_data, 'string');
		$str_data = str_replace('<![CDATA[', '', $str_data);
		$str_data = str_replace(']]>', '', $str_data);
		return $str_data;
	}

	//*****************************************************************************
	//*****************************************************************************
	// Generate an XHTML Element
	//*****************************************************************************
	//*****************************************************************************
	public static function xhe($elm=false, $content='', $attrs=array(), $escape=false)
	{
	    if ($elm) {
	        ob_start(); 
	        $c = new GenElement($elm, $content, $attrs);
	        $c->render();
	        return ($escape) ? (self::xml_escape(ob_get_clean())) : (ob_get_clean());
	    }
	    return false;
	}

}	
