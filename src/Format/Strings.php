<?php
//******************************************************************************
//******************************************************************************
/**
 * Strings Formatting Class
 *
 * @package         phpopenfw/phpopenfw3/phpopenfw3
 * @author          Christian J. Clark
 * @copyright       Copyright (c) Christian J. Clark
 * @website         https://phpopenfw.org
 * @license         https://mit-license.org
 **/
//******************************************************************************
//******************************************************************************

namespace phpOpenFW\Format;

//******************************************************************************
/**
 * Strings Formatting Class
 */
//******************************************************************************
class Strings
{
    //==========================================================================
    //==========================================================================
    /**
     * Replace the last occurrence of a string
     */
    //==========================================================================
    //==========================================================================
    public static function str_replace_last($search, $replace, $subject)
    {
        if ((string)$search == '') {
            trigger_error('Invalid search string.');
        }
        $pos = strrpos($subject, $search);
        if ($pos !== false) {
            $search = substr_replace($subject, $replace, $pos, strlen($search));
        }
        return $search;
    }

    //==========================================================================
    //==========================================================================
    /**
     * Replace the last occurrence of a string (Case-insensitive)
     */
    //==========================================================================
    //==========================================================================
    public static function str_ireplace_last($search, $replace, $subject)
    {
        if ((string)$search == '') {
            trigger_error('Invalid search string.');
        }
        $pos = strripos($subject, $search);
        if ($pos !== false) {
            $search = substr_replace($subject, $replace, $pos, strlen($search));
        }
        return $search;
    }

    //==========================================================================
    //==========================================================================
    /**
     * Trim Slashes (usually for URLs and file / directory paths)
     */
    //==========================================================================
    //==========================================================================
    public static function TrimSlashes(&$str, $front_slashes=false, $rear_slashes=true)
    {
        if (strlen($str) > 0) {
            //------------------------------------------------------------------
            // Remove Trailing Slashes
            //------------------------------------------------------------------
            if ($rear_slashes) {
                while (substr($str, strlen($str) - 1, 1) == '/') {
                    $str = substr($str, 0, strlen($str) - 1);
                }
                while (substr($str, strlen($str) - 1, 1) == '\\') {
                    $str = substr($str, 0, strlen($str) - 1);
                }
            }

            //------------------------------------------------------------------
            // Remove Front Slashes
            //------------------------------------------------------------------
            if ($front_slashes) {
                while (substr($str, 0, 1) == '/') {
                    $str = substr($str, 1, strlen($str));
                }
                while (substr($str, 0, 1) == '\\') {
                    $str = substr($str, 1, strlen($str));
                }
            }
        }
    }

    //==========================================================================
    //==========================================================================
    /**
     * Add URL Parameters Method
     *
     * Given a URL, add another paramter to it and return it.
     * @param string A URL
     * @param array An array in the form of [Key] => [Value] to be used for paramters.
     * @param array Additional options.
     * @return string New URL with update arguments/parameters
     */
    //==========================================================================
    //==========================================================================
    public static function AddUrlParams($url, Array $params, Array $args=[])
    {
        //----------------------------------------------------------------------
        // Defaults / Extact Args
        //----------------------------------------------------------------------
        $xml_escape = false;
        $url_encode = true;
        extract($args);
        $out_url = $url;
        
        //----------------------------------------------------------------------
        // No Params? Return URL.
        //----------------------------------------------------------------------
        if (count($params) <= 0) {
            return $out_url;
        }

        //----------------------------------------------------------------------
        // Add Params
        //----------------------------------------------------------------------
        $args_started = false;
        foreach ($params as $arg => $val) {
            if (!$args_started && stristr($out_url, '?') === false) {
                $out_url .= '?';
                $args_started = true;
            }
            else {
                if ($xml_escape) {
                    $out_url .= '&amp;';
                }
                else {
                    $out_url .= '&';
                }
            }
            if (!$url_encode) {
                $out_url .= $arg . '=' . $val;
            }
            else {
                $out_url .= $arg . '=' . urlencode($val);
            }
        }

        //----------------------------------------------------------------------
        // Return URL
        //----------------------------------------------------------------------
        return $out_url;
    }

    //==========================================================================
    //==========================================================================
    /**
     * URL Friendly String Function
     */
    //==========================================================================
    //==========================================================================
    public static function UrlFriendly($str)
    {
        $str = strtolower($str);
        $str = str_replace(' ' , '-', $str);
        $str = preg_replace("/[^a-zA-Z0-9_\-s]/", "", $str);
        return $str;
    }

}
