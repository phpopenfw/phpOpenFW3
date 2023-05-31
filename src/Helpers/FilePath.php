<?php
//*****************************************************************************
//*****************************************************************************
/**
 * File Path Helpers Class
 *
 * @package         phpopenfw/phpopenfw3
 * @author          Christian J. Clark
 * @copyright       Copyright (c) Christian J. Clark
 * @website         https://phpopenfw.org
 * @license         https://mit-license.org
 **/
//*****************************************************************************
//*****************************************************************************

namespace phpOpenFW\Helpers;

//*****************************************************************************
/**
 * FilePath Class
 */
//*****************************************************************************
class FilePath
{
    //=========================================================================
    //=========================================================================
    // Determine Mime Type
    //=========================================================================
    //=========================================================================
    public static function GetExtension(String $file_path)
    {
        $path_parts = pathinfo($file_path);
        if (empty($path_parts['extension'])) {
            return false;
        }
        return strtolower($path_parts['extension']);
    }

    //=========================================================================
    //=========================================================================
    // Determine Mime Type
    //=========================================================================
    //=========================================================================
    public static function GetMimeType(String $file_path)
    {
        //---------------------------------------------------------------------
        // Get Extension
        //---------------------------------------------------------------------
        $ext = static::GetExtension($file_path);
        if (!$ext) {
            return false;
        }

        //---------------------------------------------------------------------
        // Extension -> mime type map
        //---------------------------------------------------------------------
        $map = [
            'js'    => 'text/javascript',
            'css'   => 'text/css',
            'jpg'   => 'image/jpeg',
            'jpeg'  => 'image/jpeg',
            'gif'   => 'image/gif',
            'png'   => 'image/png',
            'svg'   => 'image/svg+xml',
            'svgz'  => 'image/svg+xml',
            'xml'   => 'text/xml',
            'xsl'   => 'text/xml',
            'html'  => 'text/html',
            'xhtml' => 'text/html',
            'txt'   => 'text/plain',
            'json'  => 'application/json',
            'csv'   => 'application/csv'
        ];

        //---------------------------------------------------------------------
        // Determine Mime Type
        //---------------------------------------------------------------------
        if (isset($map[$ext])) {
            return $map[$ext];
        }

        //---------------------------------------------------------------------
        // No mime type mapping found
        //---------------------------------------------------------------------
        return false;
    }
}