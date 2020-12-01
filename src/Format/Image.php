<?php
//*****************************************************************************
//*****************************************************************************
/**
 * Image Formatting Class
 *
 * @package         phpOpenFW
 * @author          Christian J. Clark
 * @copyright       Copyright (c) Christian J. Clark
 * @license         https://mit-license.org
 **/
//*****************************************************************************
//*****************************************************************************

namespace phpOpenFW\Format;

//*****************************************************************************
/**
 * Image Formatting Class
 */
//*****************************************************************************
class Image
{

    //=========================================================================
    //=========================================================================
    /**
    * Image Resize and Save Function
    *
    * @param string Current file location
    * @param string Desired file location
    * @param int Max Width
    * @param int Max Height
    * @param string Output Type (Optional, jpg (default), png, gif, webp)
    * @param int Quality (jpg / webp: 0 - 100, png: 0 - 9)
    * @param mixed Rotate (auto (default), none, 90, 180, 270)
    */
    //=========================================================================
    //=========================================================================
    public static function img_resize_save($curr_file, $save_file, $max_width, $max_height, $output_format="jpg", $quality=-1, $rotate='auto')
    {
        //---------------------------------------------------------------------
        // Passthrough to new method
        //---------------------------------------------------------------------
        return self::Resize([
            'curr_file' => $curr_file,
            'save_file' => $save_file,
            'max_width' => $max_width,
            'max_height' => $max_height,
            'output_format' => $output_format,
            'quality' => $quality,
            'rotate' => $rotate
        ]);
    }

    //=========================================================================
    //=========================================================================
    /**
    * Image Resize and Save Function
    *
    * @param Array Method parameters
    */
    //=========================================================================
    // Arguments:
    //=========================================================================
    // -> curr_file : Current image file
    // -> save_file : File to save new image to
    // -> max_width : Maximum image width
    // -> max_height : Maximum image height
    // -> output_format : Output format (jpg (default), gif, png, webp)
    // -> quatiy : Image quality (-1 is default) (jpg / webp: 0 - 100, png: 0 - 9)
    // -> rotate : Rotate image (auto (default), none, 90, 180, 270)
    //=========================================================================
    public static function Resize(Array $args)
    {
        //---------------------------------------------------------------------
        // Defaults / Extract Args
        //---------------------------------------------------------------------
        $curr_file = false;
        $save_file = false;
        $max_width = false;
        $max_height = false;
        $output_format = 'jpg';
        $rotate = 'auto';
        $quality = -1;
        extract($args);
        $output_format = strtolower($output_format);

        //---------------------------------------------------------------------
        // Validate current image file / new file location
        //---------------------------------------------------------------------
        if (!$curr_file || !file_exists($curr_file) || is_dir($curr_file)) {
            return 1;
        }
        if (!$save_file || !is_dir(dirname($save_file)) || !is_writeable(dirname($save_file))) {
            return 4;
        }

        //---------------------------------------------------------------------
        // Get / Validate Image Information
        //---------------------------------------------------------------------
        $img_info = getimagesize($curr_file);
        if (!$img_info) {
            return 2;
        }

        //---------------------------------------------------------------------
        // Image Type
        //---------------------------------------------------------------------
        $img_type = strtolower($img_info["mime"]);

        //---------------------------------------------------------------------
        // Current Dimensions
        //---------------------------------------------------------------------
        $curr_width = $img_info[0];
        $curr_height = $img_info[1];

        //---------------------------------------------------------------------
        // New Dimensions
        //---------------------------------------------------------------------
        $width_perc = ($max_width) ? ($max_width / $curr_width) : (1);
        $height_perc = ($max_height) ? ($max_height / $curr_height) : (1);

        //---------------------------------------------------------------------
        // No Resize?
        //---------------------------------------------------------------------
        if ($width_perc >= 1 && $height_perc >= 1) {

            //-----------------------------------------------------------------
            // Move File
            //-----------------------------------------------------------------
            if (move_uploaded_file($curr_file, $save_file)) {
                return 0;
            }
            else {
                return 8;
            }
        }

        //---------------------------------------------------------------------
        // Calculate new height and width
        //---------------------------------------------------------------------
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

        //---------------------------------------------------------------------
        // Create new image from current image
        //---------------------------------------------------------------------
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

            case "image/webp":
                $rotate = false;
                $source = imagecreatefromwebp($curr_file);
                break;

            //-----------------------------------------------------------------
            // Invalid Image Type
            //-----------------------------------------------------------------
            default:
                return 3;
                break;
        }

        //---------------------------------------------------------------------
        // Create New Image
        //---------------------------------------------------------------------
        $new_image = imagecreatetruecolor($new_width, $new_height);

        //---------------------------------------------------------------------
        // Resize current image into new image
        //---------------------------------------------------------------------
        $resize_status = imagecopyresampled($new_image, $source, 0, 0, 0, 0, $new_width, $new_height, $curr_width, $curr_height);
        if (!$resize_status) {
            return 5;
        }

        //---------------------------------------------------------------------
        // Rotate Image?
        //---------------------------------------------------------------------
        if ($rotate && $rotate != 'none') {
            $deg = 0;
            if (!is_numeric($rotate)) {
                $exif = exif_read_data($curr_file);
                if (isset($exif['Orientation']) && $exif['Orientation'] != 1) {
                    switch ($exif['Orientation']) {
                        case 3:
                            $deg = 180;
                            break;
                        case 6:
                            $deg = 270;
                            break;
                        case 8:
                            $deg = 90;
                            break;
                    }
                }
            }
            if ($deg) {
                if (!$new_image = imagerotate($new_image, $deg, 0)) {
                    return 10;
                }
            }
        }

        //---------------------------------------------------------------------
        // Save Image
        //---------------------------------------------------------------------
        switch ($output_format) {
            case "png":
                $save_status = imagepng($new_image, $save_file, $quality);
                break;

            case "gif":
                $save_status = imagegif($new_image, $save_file);
                break;

            case "webp":
                $save_status = imagewebp($new_image, $save_file, $quality);
                break;

            default:
                $save_status = imagejpeg($new_image, $save_file, $quality);
                break;
        }
        if (!$save_status) {
            $ret_code = 6;
        }

        //---------------------------------------------------------------------
        // Return Code 0 =  Success
        //---------------------------------------------------------------------
        return 0;
    }

}
