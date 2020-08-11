<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Content Delivery Plugin
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 **/
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\Content;

//**************************************************************************************
/**
 * Content Delivery Class
 */
//**************************************************************************************
class CDN
{

	//*****************************************************************************
	//*****************************************************************************
	// Output Content Type Header
	//*****************************************************************************
	//*****************************************************************************
	public static function OutputContentType($file)
	{
		$path_parts = pathinfo($file);
		if (empty($path_parts['extension'])) {
			return false;
		}
		$ext = strtolower($path_parts['extension']);

		switch ($ext) {

			//======================================================
			// Javascript
			//======================================================
			case 'js':
				header('Content-type: text/javascript');
				break;

			//======================================================
			// CSS
			//======================================================
			case 'css':
				header('Content-type: text/css');
				break;

			//======================================================
			// Images
			//======================================================
			case 'jpg':
			case 'jpeg':
			case 'gif':
			case 'png':
				header("Content-type: image/{$ext}");
				break;

			//======================================================
			// Scalable Vector Graphics (SVG)
			//======================================================
			case 'svg':
			case 'svgz':
				header("Content-type: image/svg+xml");
				if ($ext == 'svgz') {
					header("Content-Encoding: gzip");	
				}
				break;

			//======================================================
			// XML
			//======================================================
			case 'xml':
			case 'xsl':
				header('Content-type: text/xml');
				break;

			//======================================================
			// HTML / XHTML
			//======================================================
			case 'html':
			case 'xhtml':
				header('Content-type: text/html');
				break;

			//======================================================
			// Text
			//======================================================
			case 'txt':
			case 'json':
				header('Content-type: text/plain');
				break;

			//======================================================
			// Default: File Not Found (i.e. 404)
			//======================================================
			default:
				return false;
				break;
		}

		return true;
	}

	//*****************************************************************************
	//*****************************************************************************
	// Output Stream
	//*****************************************************************************
	//*****************************************************************************
	public static function OutputStream($stream, Array $args=[])
	{
		//=================================================================
		// Defaults / Extract Args
		//=================================================================
		$output_header = false;
		$is_base64 = false;
		extract($args);
		$header = false;

		//=================================================================
		// Validate Resource is Stream...
		//=================================================================
		if (get_resource_type($stream) != 'stream') { return false; }

		//=================================================================
		// Output Headers: Yes
		//=================================================================
		if (!empty($output_header)) {

			//--------------------------------------------------------
			// Pull the First Chunk (100 Characters)
			//--------------------------------------------------------
			$first_chunk = stream_get_contents($stream, 100);

			//--------------------------------------------------------
			// Is this a Data URI Scheme Header?
			//--------------------------------------------------------
			$dus_data = self::ParseDataURISchemeHeader($first_chunk);
			extract($dus_data);

			//--------------------------------------------------------
			// Update First Chunk (header has been removed)
			//--------------------------------------------------------
			if (!empty($dus_data['chunk'])) {
				$first_chunk = $dus_data['chunk'];
			}

			//--------------------------------------------------------
			// If Content Type is set: Output it
			//--------------------------------------------------------
			if (!empty($content_type)) {
				header("Content-type: {$content_type}");
			}
			//--------------------------------------------------------
			// If File Name is set:
			// Determine Content Type from File Name
			//--------------------------------------------------------
			else if (!empty($file_name)) {
				\phpOpenFW\Content\CDN::OutputContentType($file_name);
			}
		}

		//=================================================================
		// Output Stream Contents
		//=================================================================
		ob_start();
		if (!empty($first_chunk)) { print $first_chunk; }
		print stream_get_contents($stream);
		if (!empty($is_base64)) {
			print base64_decode(ob_get_clean());
		}
		else {
			print ob_get_clean();
		}

		return true;
	}

	//*****************************************************************************
	//*****************************************************************************
	// Parse Data URI Scheme Header
	//*****************************************************************************
	// See: https://en.wikipedia.org/wiki/Data_URI_scheme
	//*****************************************************************************
	//*****************************************************************************
	public static function ParseDataURISchemeHeader($chunk)
	{
		//--------------------------------------------------------
		// Setup Return Values (with defaults)
		//--------------------------------------------------------
		$ret_vals = [
			'embedded_headers' => false
		];

		//--------------------------------------------------------
		// Is there data to work with?
		//--------------------------------------------------------
		if ($chunk) {

			//--------------------------------------------------------
			// Determine a lot of things...
			//--------------------------------------------------------
			$has_binary = !ctype_print($chunk);
			$embedded_headers = (bool)(stripos($chunk, 'data:') !== false);
			if ($embedded_headers) {
				$header_end = stripos($chunk, ',');
				$header = substr($chunk, 5, $header_end - 5);
				$is_base64 = (bool)(stripos($chunk, 'base64'));
				$chunk = substr($chunk, $header_end + 1);
	
				//--------------------------------------------------------
				// Return Values
				//--------------------------------------------------------
				$ret_vals['embedded_headers'] = $embedded_headers;
				$ret_vals['header'] = $header;
				$ret_vals['is_base64'] = $is_base64;
				$ret_vals['chunk'] = $chunk;
				$ret_vals['content_type'] = substr($header, 0, stripos($header, ';'));
			}
	
			$ret_vals['has_binary'] = $has_binary;
		}

		return $ret_vals;
	}

}

