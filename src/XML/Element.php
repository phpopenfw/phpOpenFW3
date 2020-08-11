<?php
//**************************************************************************************
//**************************************************************************************
/**
 * A class for generating XML elements
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 **/
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\XML;
use phpOpenFW\XML\Transform;

//**************************************************************************************
/**
 * (XML) Element Class
 */
//**************************************************************************************
abstract class Element
{
	/**
	* @var string name of XML elements
	**/
	protected $element;			// XML element
	
	/**
	* @var array An associative array of element attributes in the form of ["name" => "value"]
	**/
	protected $attributes;		// Array of attributes and values
	
	/**
	* @var string The value between the opening and ending element tags
	**/
	protected $inset_val;		// Value between the element tags
	
	/**
	* @var string The file path to the XSL stylesheet to be used for transformation
	**/
	protected $xsl_template;	// XSL stylesheet used to render form
	
	/**
	* @var string The raw XML of the element
	**/
	protected $element_xml;		// Element XML
	
	/**
	* @var bool Force and endtag for this element
	**/
	protected $endtag;		// Force and endtag for this element

	/**
	* @var int Number of tabs
	**/
	protected $tabs;		// Number of tabs

	/**
	* @var int Output Style
	**/
	protected $style;		// Output Style

	/**
	* @var Array An array of child nodes
	**/
	protected $child_nodes;		// Child Nodes

	//*************************************************************************
	// String Conversion Function
	//*************************************************************************
	public function __toString() { return $this->render(true);  }

	//*************************************************************************
	/**
	* Element class render function
	**/
	//*************************************************************************
	//*************************************************************************
	// Render Function
	//*************************************************************************
	//*************************************************************************
	public function render($buffer=false)
	{
		if ($buffer) { ob_start(); }
		$el_cont = '';

		// Tabs
		$tabs = '';
		for ($i = 0; $i < $this->tabs; $i++) { $tabs .= "\t"; }

		//***************************************
		// Child Nodes OR Inset Text
		//***************************************
		if ($this->child_nodes) {
			// Child Nodes
			foreach ($this->child_nodes as $node) {
				if (is_object($node) && get_class($node) == 'phpOpenFW\XML\GenElement') {
					 $node->set_tabs($this->tabs + 1);
				}
				$el_cont .= $node;
			}
		}
		else if ($this->inset_val !== false && $this->inset_val !== '') {
			// Inset Value
			$el_cont .= ($this->style == 'tree') ? ("\t{$tabs}" . $this->inset_val . "\n") : ($this->inset_val);
		}

		//***************************************
		// Opening Tag		
		//***************************************
		$this->element_xml = "{$tabs}<{$this->element}";
		if (isset($this->attributes)) {
			foreach ($this->attributes as $key => $value) {
				$this->element_xml .= " {$key}=\"{$value}\"";
			}
		}

		//***************************************
		// Build Closing Tag
		//***************************************
		$close_tag = "</{$this->element}>\n";
		if ($this->style == 'tree') { $close_tag = $tabs . $close_tag; }

		//***************************************
		// Content / Closing Tag
		//***************************************
		if ($el_cont !== false && $el_cont !== '') {
			if ($this->style == 'tree') {
				$this->element_xml .= ">\n" . $el_cont . $close_tag;	
			}
			else { $this->element_xml .= '>' . $el_cont . $close_tag; }

		}
		else if ($this->endtag) { $this->element_xml .= ">{$close_tag}"; }
		else { $this->element_xml .=  " />\n"; }

		//***************************************
		// Perform XML Transformation
		//***************************************
		$sxoe = (isset($_SESSION['show_xml_on_error']) && $_SESSION['show_xml_on_error'] == 1) ? (true) : (false);
		Transform::XSL($this->element_xml, $this->xsl_template, $sxoe);
		if ($buffer) { return ob_get_clean(); }
	}

	//*************************************************************************
	/**
	* Set the XSL stylesheet
	* @param string The file path to the XSL stylesheet to be used for transformation
	**/
	//*************************************************************************
	// Set the XSL Stylesheet
	//*************************************************************************
	public function set_xsl($stylesheet) { $this->xsl_template = $stylesheet; }

	//*************************************************************************
	/**
	* Add an attribute to the element
	* @param string Attribute name
	* @param string Attribute value
	**/	
	//*************************************************************************
	// Add an attribute
	// (Leave this function as is. It provides a means of directly setting
	// an attribute's value unlike the attr() function.)
	//*************************************************************************
	public function set_attribute($attr, $value) { $this->attributes[(string)$attr] = $value; }

	//*************************************************************************
	/**
	* Add, append, overwrite, or remove an attribute value to the element
	* @param string Attribute name
	* @param string Attribute value
	**/	
	//*************************************************************************
	// Add an attribute
	//*************************************************************************
	public function attr($attr, $value=false, $append=true)
	{
		$key = trim((string)$attr);
		if ($key == '.') { $key = 'class'; }
		if ($key == '#') { $key = 'id'; }
		$val = (string)$value;
		$append = (bool)$append;
		$key_exists = (isset($this->attributes[$key])) ? (true) : (false);
		if ($key == '') { return false; }
		if ($val == '') {
			if (isset($this->attributes[$key])) {
				unset($this->attributes[$key]);
			}
			else { return false; }
		}
		else {
			if ($append && $key_exists) {
				$this->attributes[$key] .= ' ' . $value;
			}
			else {
				$this->attributes[$key] = $value;
			}
		}
		
		return true;
	}

	//*************************************************************************
	/**
	* Add, append, overwrite, or remove attribute values to the element
	* @param array An array of key/value pairs of attributes
	* @param bool Append flag. True = append, false = overwrite
	**/	
	//*************************************************************************
	// Add, append, overwrite, or remove attributes
	//*************************************************************************
	public function attrs($attrs, $append=true)
	{
		if (!is_array($attrs)) { return false; }

		$count = 0;
		foreach ($attrs as $attr => $value) {
			if ($this->attr($attr, $value, $append)) { $count++; }
		}

		return $count;
	}

	//*************************************************************************
	/**
	* Set the number tabs to use to indent the element
	* @param int Number of tabs
	**/	
	//*************************************************************************
	// Set the number tabs to use to indent the element
	//*************************************************************************
	public function set_tabs($tabs) { $this->tabs = $tabs + 0; }
	
	//*************************************************************************
	/**
	* Set the inset value of an element
	* @param string Inset value between opening and ending tags
	**/	
	//*************************************************************************
	// Set Element inset value (between tags)
	//*************************************************************************
	public function inset($value) { $this->inset_val = $value; }

	//*************************************************************************
	/**
	* Add child element
	* @param mixed Add child element
	**/	
	//*************************************************************************
	// Add child element
	//*************************************************************************
	public function add_child($child)
	{
		if (!is_array($this->child_nodes)) { $this->child_nodes = array(); }
		$this->child_nodes[] = $child;
	}

	//*************************************************************************
	/**
	* Force an endtag on this element
	* @param bool Force and endtag (True = yes, False or Empty = no)
	**/	
	//*************************************************************************
	// Force and endtag (True = yes, False or Empty = no)
	//*************************************************************************
	public function force_endtag($bool) { if ($bool) { $this->endtag = true; } }

	//*****************************************************************************
	/**
	* Return a string as valid XML escaped value
	*
	* @param string Data to escape
	* @return string Escaped data
	*/
	//*****************************************************************************
	protected function xml_escape($str_data)
	{
		return \phpOpenFW\XML\Format::xml_escape($str_data);
	}

	//*************************************************************************
	/**
	* Set output to display inline
	**/	
	//*************************************************************************
	// Set output to display inline
	//*************************************************************************
	public function display_inline() { $this->style = 'inline'; }

	//*************************************************************************
	/**
	* Set output to display tree
	**/	
	//*************************************************************************
	// Set output to display tree
	//*************************************************************************
	public function display_tree() { $this->style = 'tree'; }

	//*************************************************************************
	/**
	* Element class destructor function
	**/
	//*************************************************************************
	// Destructor Function
	//*************************************************************************
	public function __destruct() {}
}
