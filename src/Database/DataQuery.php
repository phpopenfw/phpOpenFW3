<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Data Query class
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 */
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\Database;

//**************************************************************************************
/**
 * Data Query Class
 *
 * Pass the constructor an associative array:
 *
 * [type] -> 'insert', 'update', 'delete', 'select'<br/>
 * [table] -> "<i>table name</i>"<br/>
 * [fields] -> An associative array of fields and their values. $fields["field_name"] = ["field_value"]<br/>
 * [filter_phrase] -> The full SQL "where" phrase used to filter results<br/>
 * [order] -> order by phrase<br/>
 * [group] -> group by phrase<br/>
 * [limit] -> limit phrase<br/>
 */
//**************************************************************************************
class DataQuery {

	//========================================================================
	// Class variables
	//========================================================================

	/**
	* @var array The array of all of the query parts
	**/
	private $query_array;
	
	/**
	* @var string The type of query we are building
	**/
	private $type;
	
	/**
	* @var string The table(s) in the SQL statement
	**/
	private $table;
	
	/**
	* @var string The fields(s) in the SQL statement
	**/
	private $fields;
	
	/**
	* @var string The filtering phrase in the SQL statement
	**/
	private $filter_phrase;
	
	/**
	* @var string The order by phrase in the SQL statement
	**/
	private $order;
	
	/**
	* @var string The group by phrase in the SQL statement
	**/
	private $group;
	
	/**
	* @var string The limit phrase in the SQL statement
	**/
	private $limit;

	//=========================================================================
	// Public functions
	//=========================================================================
	
	//*************************************************************************
	/**
	* Constructor Function
	* @param array The array of all of the query parts
	**/
	//*************************************************************************
	public function __construct($in_qa)
	{
		//--------------------------------------------------------------------
		// Set query array or error if no array passed
		//--------------------------------------------------------------------
		if (is_array($in_qa)) {
			$this->query_array = $in_qa;
		}
		else {
			trigger_error('You must pass an array to the query constructor!');
		}

		//--------------------------------------------------------------------
		// Query Type
		//--------------------------------------------------------------------
		if (!array_key_exists('type', $in_qa)) {
			trigger_error('No query type was specified in the array!');
		}
		else { $this->type = $in_qa['type']; }
		
		//--------------------------------------------------------------------
		// Query Table(s)
		//--------------------------------------------------------------------
		if (!array_key_exists('table', $in_qa)) {
			trigger_error('No query table was specified in the array!');
		}
		else {
			if (is_array($in_qa['table'])) {
				foreach ($in_qa['table'] as $table) {
					if (isset($this->table) && !empty($this->table)) { $this->table .= ', '; }
					$this->table .= $table;
				}
			}
			else {
				$this->table = $in_qa['table'];
			}
		}
		
		//--------------------------------------------------------------------
		// Fields
		//--------------------------------------------------------------------
		if (array_key_exists('fields', $in_qa)) {
			$this->fields = ' ';
			$field_count = 0;
			if (is_array($in_qa['fields'])) {
				foreach ($in_qa['fields'] as $key => $val) {
					switch ($this->type) {
						case 'select':
							if (isset($this->fields) && $this->fields != ' ') { $this->fields .= ', '; }
							$this->fields .= $val;
							break;
			
						case 'insert':
							if ($field_count == 0) { $this->fields .= '('; }
							if (!isset($field_vals)) { $field_vals = ' values ('; }
							
							if (isset($this->fields) && $field_count != 0) { $this->fields .= ', '; }
							if (isset($field_vals) && $field_count != 0) { $field_vals .= ', '; }
							
							$this->fields .= $key;
							$field_vals .= $val;
							break;
			
						case 'update':
							if (isset($this->fields) && $field_count != 0) { $this->fields .= ', '; }
							$this->fields .= "{$key} = {$val}";
							break;
					}
					$field_count++;
				}
			}
			else {
				$this->fields = $in_qa['fields'];
			}
			
			if ($this->type == 'insert') {
				$this->fields .= ')' . $field_vals . ')';
			}
		}

		//--------------------------------------------------------------------
		// Filter phrase (Where)
		//--------------------------------------------------------------------
		if (array_key_exists('filter_phrase', $in_qa)) {
			$this->filter_phrase = ' ' . $in_qa['filter_phrase'];
		}
		
		//--------------------------------------------------------------------
		// Order by
		//--------------------------------------------------------------------
		if (array_key_exists('order', $in_qa)) {
			$this->order = ' ' . $in_qa['order'];
		}
		
		//--------------------------------------------------------------------
		// Group by
		//--------------------------------------------------------------------
		if (array_key_exists('group', $in_qa)) {
			$this->group = ' ' . $in_qa['group'];
		}
		
		//--------------------------------------------------------------------
		// Limit
		//--------------------------------------------------------------------
		if (array_key_exists('limit', $in_qa)) {
			$this->limit = ' LIMIT ' . $in_qa['limit'];
		}

	}
	
	//*************************************************************************
	/**
	* Render the query
	**/
	//*************************************************************************
	public function render()
	{
		switch ($this->type) {
			case 'select':
				$query_string = "select {$this->fields} from {$this->table}";
				if (isset($this->filter_phrase)) { $query_string .= $this->filter_phrase; }
				if (isset($this->order)) { $query_string .= $this->order; }
				if (isset($this->group)) { $query_string .= $this->group; }
				if (isset($this->limit)) { $query_string .= $this->limit; }
				break;
			
			case 'insert':
				$query_string = "insert into {$this->table}";
				if (isset($this->fields)) { $query_string .= $this->fields; }
				break;
			
			case 'update':
				$query_string = "update {$this->table} set";
				if (isset($this->fields)) { $query_string .= $this->fields; }
				if (isset($this->filter_phrase)) { $query_string .= $this->filter_phrase; }
				if (isset($this->limit)) { $query_string .= $this->limit; }
				break;
			
			case 'delete':
				$query_string = "delete from {$this->table}";
				if (isset($this->filter_phrase)) { $query_string .= $this->filter_phrase; }
				if (isset($this->limit)) { $query_string .= $this->limit; }
				break;
		}
		
		if (isset($query_string)) { return $query_string; }
	}
}
