<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Image Formatting Class
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 **/
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\Format;

//*****************************************************************************
/**
 * Image Formatting Class
 */
//*****************************************************************************
class Image
{

	//*****************************************************************************
	//*****************************************************************************
	/**
	* Image Resize and Save Function
	*
	* @param string Current file location
	* @param string Desired file location
	* @param int Max Width
	* @param int Max Height
	* @param string Output Type (Optional, default "jpg")
	*/
	//*****************************************************************************
	//*****************************************************************************
	public static function img_resize_save($curr_file, $save_file, $max_width, $max_height, $out_format="jpg")
	{
		$ret_code = 0;
		$out_format = strtolower($out_format);
	
		//================================================================
		// Valid File
		//================================================================
		if (file_exists($curr_file) && !is_dir($curr_file)) {
			$img_info = getimagesize($curr_file);
	
			//============================================================
			// Invalid Image
			//============================================================
			if (!$img_info) {
				$ret_code = 2;
			}
			//============================================================
			// Valid Image
			//============================================================
			else {
				//--------------------------------------------------------
				// Image Type
				//--------------------------------------------------------
				$img_type = strtolower($img_info["mime"]);
				
				//--------------------------------------------------------
				// Current Dimensions
				//--------------------------------------------------------
				$curr_width = $img_info[0];
				$curr_height = $img_info[1];
				
				//--------------------------------------------------------
				// New Dimensions
				//--------------------------------------------------------
				$width_perc = $max_width / $curr_width;
				$height_perc = $max_height / $curr_height;
	
				if ($width_perc < 1 || $height_perc < 1) {
					if ($width_perc < $height_perc) {
						$new_width = $max_width;
						$new_height = $curr_height * $width_perc;
					}
					else if ($width_perc > $height_perc) {
						$new_width = $curr_width * $height_perc;
						$new_height = $max_height;
					}
					else {
						$new_width = $max_width;
						$new_height = $max_height;
					}
	
					//----------------------------------------------------
					// Load
					//----------------------------------------------------
					$thumb = imagecreatetruecolor($new_width, $new_height);
					$save = true;
					switch ($img_type) {
						case "image/png":
							$source = imagecreatefrompng($curr_file);
							break;
	
						case "image/gif":
							$source = imagecreatefromgif($curr_file);
							break;
							
						case "image/jpeg":
							$source = imagecreatefromjpeg($curr_file);
							break;
	
						default:
							//--------------------------------------------
							// Invalid Image Type
							//--------------------------------------------
							$ret_code = 3;
							$save = false;
							break;
					}
	
					//----------------------------------------------------
					// Resize and Save
					//----------------------------------------------------
					if ($save) {
						if (is_dir(dirname($save_file)) && is_writeable(dirname($save_file))) {
	
							//--------------------------------------------
							// Resize
							//--------------------------------------------
							$resize_status = imagecopyresampled($thumb, $source, 0, 0, 0, 0, $new_width, $new_height, $curr_width, $curr_height);
	
							//--------------------------------------------
							// Save
							//--------------------------------------------
							if ($resize_status) {
								$save_status = false;
								switch ($out_format) {
									case "png":
										$save_status = imagepng($thumb, $save_file);
										break;
	
									case "gif":
										$save_status = imagegif($thumb, $save_file);
										break;
	
									default:
										$save_status = imagejpeg($thumb, $save_file);
										break;
								}
								if (!$save_status) { $ret_code = 6; }
							}
							else { $ret_code = 5; }
						}
						//----------------------------------------------------
						// Invalid save path
						//----------------------------------------------------
						else {
							$ret_code = 4;
						}
					}
				}
				else {
					//----------------------------------------------------
					// No need to resize, just move
					//----------------------------------------------------
					if (is_dir(dirname($save_file)) && is_writeable(dirname($save_file))) {
						move_uploaded_file($curr_file, $save_file);
					}
					else { $ret_code = 7; }
				}
			}
		}
		//================================================================
		// Valid File
		//================================================================
		else {
			$ret_code = 1;
		}
		
		return $ret_code;
	}	
}
