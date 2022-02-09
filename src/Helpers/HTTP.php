<?php
//*****************************************************************************
//*****************************************************************************
/**
 * HTTP Helper Object
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
 * HTTP Helper Class
 */
//*****************************************************************************
class HTTP
{
    //=========================================================================
    //=========================================================================
    /**
     * This method will redirect the user to the given page
     */
    //=========================================================================
    //=========================================================================
    public static function Redirect($location=false)
    {
        //---------------------------------------------------------------------
        // Set the location
        //---------------------------------------------------------------------
        if (empty($location)) {
            $qs_start = strpos($_SERVER['REQUEST_URI'], '?');
            if ($qs_start === false) {
                $location = $_SERVER['REQUEST_URI'];
            }
            else {
                $location = substr($_SERVER['REQUEST_URI'], 0, $qs_start);
            }
        }

        //---------------------------------------------------------------------
        // Redirect
        //---------------------------------------------------------------------
        header("Location: {$location}");
        exit;
    }

    //=========================================================================
    //=========================================================================
    /**
     * Return All HTTP Request Headers
     */
    //=========================================================================
    //=========================================================================
    public static function GetAllHeaders()
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        $headers = [];
        foreach ($_SERVER as $name => $value) {
           if (substr($name, 0, 5) == 'HTTP_') {
               $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
               $headers[$name] = $value;
           }
           else if ($name == "CONTENT_TYPE") {
               $headers["Content-Type"] = $value;
           }
           else if ($name == "CONTENT_LENGTH") {
               $headers["Content-Length"] = $value;
           }
       }
       return $headers;
    }

    //=========================================================================
    //=========================================================================
    /**
    * Get URL Path Function
    */
    //=========================================================================
    //=========================================================================
    public static function GetUrlPath()
    {
        //---------------------------------------------------------------------
        // If $_SERVER['REDIRECT_URL'] is set
        //---------------------------------------------------------------------
        if (isset($_SERVER['REDIRECT_URL'])) {
            return $_SERVER['REDIRECT_URL'];
        }
        //---------------------------------------------------------------------
        // If $_SERVER['PATH_INFO'] is set
        //---------------------------------------------------------------------
        else if (isset($_SERVER['PATH_INFO'])) {
            return $_SERVER['PATH_INFO'];
        }
        //---------------------------------------------------------------------
        // If $_SERVER['REQUEST_URI'] is set
        //---------------------------------------------------------------------
        else if (isset($_SERVER['REQUEST_URI'])) {
            $qs_start = strpos($_SERVER['REQUEST_URI'], '?');
            if ($qs_start === false) {
                return $_SERVER['REQUEST_URI'];
            }
            else {
                return substr($_SERVER['REQUEST_URI'], 0, $qs_start);
            }
        }

        return false;
    }

    //=========================================================================
    //=========================================================================
    /**
    * Get HTML Path Function
    */
    //=========================================================================
    //=========================================================================
    public static function GetHtmlPath()
    {
        $path = '';
        if (isset($_SERVER['DOCUMENT_ROOT']) && isset($_SERVER['SCRIPT_FILENAME'])) {
            $doc_root = $_SERVER['DOCUMENT_ROOT'];
            $doc_root_parts = explode('/', $doc_root);
            $script_file = $_SERVER['SCRIPT_FILENAME'];
            $script_file_parts = explode('/', $script_file);

            foreach ($script_file_parts as $key => $part) {
                if (!isset($doc_root_parts[$key])) {
                    if ($part != 'index.php') {
                        $path .= '/' . $part;
                    }
                }
            }
        }
        else {
            $self = $_SERVER['PHP_SELF'];
            $self_arr = explode('/', $self);
            foreach ($self_arr as $item) {
                if (!empty($item) && $item != 'index.php') {
                    $path .= '/' . $item;
                }
            }
            if ($path == '/') {
                $path = '';
            }
        }
        return $path;
    }

}
