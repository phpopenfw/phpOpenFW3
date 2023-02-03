<?php
//*****************************************************************************
//*****************************************************************************
/**
 * Content Delivery Plugin
 *
 * @package         phpopenfw/phpopenfw3
 * @author          Christian J. Clark
 * @copyright       Copyright (c) Christian J. Clark
 * @website         https://phpopenfw.org
 * @license         https://mit-license.org
 **/
//*****************************************************************************
//*****************************************************************************

namespace phpOpenFW\Content;
use \phpOpenFW\Helpers\FilePath;

//*****************************************************************************
/**
 * Content Delivery Class
 */
//*****************************************************************************
class CDN
{
    //*************************************************************************
    //*************************************************************************
    // Output Content Type Header
    //*************************************************************************
    //*************************************************************************
    public static function OutputContentType($file, Array $args=[])
    {
        //---------------------------------------------------------------------
        // Extract Args
        //---------------------------------------------------------------------
        extract($args, EXTR_SKIP);

        //---------------------------------------------------------------------
        // Is content type set?
        //---------------------------------------------------------------------
        if (!empty($content_type) && empty($mime_type)) {
            $mime_type = $content_type;
        }

        //---------------------------------------------------------------------
        // Get Mime Type
        //---------------------------------------------------------------------
        if (empty($mime_type) && $file) {
            $mime_type = FilePath::GetMimeType($file);
        }

        //---------------------------------------------------------------------
        // Did we get a mime type?
        //---------------------------------------------------------------------
        if ($mime_type) {
            header('Content-type: ' . $mime_type);
            $ext = FilePath::GetExtension($file);
            if ($ext == 'svgz') {
                header('Content-Encoding: gzip');
            }
            return true;
        }

        //---------------------------------------------------------------------
        // No content type found
        //---------------------------------------------------------------------
        return false;
    }

    //*************************************************************************
    //*************************************************************************
    // Output Stream
    //*************************************************************************
    //*************************************************************************
    public static function OutputStream($stream, Array $args=[])
    {
        //=====================================================================
        // Defaults / Extract Args
        //=====================================================================
        $output_header = false;
        $is_base64 = false;
        $file_name = false;
        extract($args);
        $header = false;

        //=====================================================================
        // Validate Resource is Stream...
        //=====================================================================
        if (get_resource_type($stream) != 'stream') {
            return false;
        }

        //=====================================================================
        // Output Headers: Yes
        //=====================================================================
        if (!empty($output_header)) {

            //-----------------------------------------------------------------
            // Pull the First Chunk (100 Characters)
            //-----------------------------------------------------------------
            $first_chunk = stream_get_contents($stream, 100);

            //-----------------------------------------------------------------
            // Is this a Data URI Scheme Header?
            //-----------------------------------------------------------------
            $dus_data = self::ParseDataURISchemeHeader($first_chunk);
            extract($dus_data, EXTR_SKIP);

            //-----------------------------------------------------------------
            // Update First Chunk (header has been removed)
            //-----------------------------------------------------------------
            if (!empty($dus_data['chunk'])) {
                $first_chunk = $dus_data['chunk'];
            }

            //-----------------------------------------------------------------
            // Output Content Type Header
            //-----------------------------------------------------------------
            $ct_args = [];
            if (!empty($content_type)) {
                $ct_args['content_type'] = $content_type;
            }
            \phpOpenFW\Content\CDN::OutputContentType($file_name, $ct_args);

            //-----------------------------------------------------------------
            // Content Disposition
            //-----------------------------------------------------------------
            if (!empty($args['content_disposition'])) {
                header('Content-Disposition: ' . $args['content_disposition']);
            }
            else if (!empty($args['force_download'])) {
                $cont_disp = 'Content-Disposition: attachment;';
                if ($file_name) {
                    $cont_disp .= ' filename=' . $file_name . ';';
                }
                header($cont_disp);
            }
        }

        //=====================================================================
        // Output Stream Contents
        //=====================================================================
        ob_start();
        if (!empty($first_chunk)) {
            print $first_chunk;
        }
        print stream_get_contents($stream);
        if (!empty($is_base64)) {
            print base64_decode(ob_get_clean());
        }
        else {
            print ob_get_clean();
        }

        return true;
    }

    //*************************************************************************
    //*************************************************************************
    // Parse Data URI Scheme Header
    //*************************************************************************
    // See: https://en.wikipedia.org/wiki/Data_URI_scheme
    //*************************************************************************
    //*************************************************************************
    public static function ParseDataURISchemeHeader($chunk)
    {
        //---------------------------------------------------------------------
        // Setup Return Values (with defaults)
        //---------------------------------------------------------------------
        $ret_vals = [
            'embedded_headers' => false
        ];

        //---------------------------------------------------------------------
        // Is there data to work with?
        //---------------------------------------------------------------------
        if ($chunk) {

            //-----------------------------------------------------------------
            // Determine a lot of things...
            //-----------------------------------------------------------------
            $has_binary = !ctype_print($chunk);
            $embedded_headers = (bool)(stripos($chunk, 'data:') !== false);
            if ($embedded_headers) {
                $header_end = stripos($chunk, ',');
                $header = substr($chunk, 5, $header_end - 5);
                $is_base64 = (bool)(stripos($chunk, 'base64'));
                $chunk = substr($chunk, $header_end + 1);
    
                //-------------------------------------------------------------
                // Return Values
                //-------------------------------------------------------------
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

