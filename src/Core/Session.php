<?php
//*****************************************************************************
//*****************************************************************************
/**
 * Session Class
 *
 * @package         phpopenfw/phpopenfw3
 * @author          Christian J. Clark
 * @copyright       Copyright (c) Christian J. Clark
 * @website         https://phpopenfw.org
 * @license         https://mit-license.org
 **/
//*****************************************************************************
//*****************************************************************************

namespace phpOpenFW\Core;

class Session
{

    //*************************************************************************
    //*************************************************************************
    /**
     * Is Session Started?
     */
    //*************************************************************************
    //*************************************************************************
    public static function IsStarted()
    {
        if (version_compare(phpversion(), '5.4.0', '>=')) {
            return (session_status() === PHP_SESSION_ACTIVE) ? (true) : (false);
        }
        else {
            return (session_id() === '') ? (false) : (true);
        }
    }

    //*************************************************************************
    //*************************************************************************
    /**
     * Activate Session
     */
    //*************************************************************************
    //*************************************************************************
    public static function Activate()
    {
        if (defined('PHPOPENFW_CONFIG_INDEX')) {
            $_SESSION[PHPOPENFW_CONFIG_INDEX] =& $GLOBALS[PHPOPENFW_CONFIG_INDEX];
        }
        if (isset($GLOBALS['PHPOPENFW_DATA_SOURCES'])) {
            $_SESSION['PHPOPENFW_DATA_SOURCES'] =& $GLOBALS['PHPOPENFW_DATA_SOURCES'];
        }
        if (isset($GLOBALS['PHPOPENFW_DEFAULT_DATA_SOURCE'])) {
            $_SESSION['PHPOPENFW_DEFAULT_DATA_SOURCE'] = $GLOBALS['PHPOPENFW_DEFAULT_DATA_SOURCE'];
        }
    }

    //*************************************************************************
    //*************************************************************************
    /**
     * Re-activate Session
     */
    //*************************************************************************
    //*************************************************************************
    public static function Reactivate()
    {
        if (isset($_SESSION['PHPOPENFW_CONFIG_INDEX'])) {
            $GLOBALS['PHPOPENFW_CONFIG_INDEX'] =& $_SESSION['PHPOPENFW_CONFIG_INDEX'];
            if (!defined('PHPOPENFW_CONFIG_INDEX')) {
                define('PHPOPENFW_CONFIG_INDEX', $GLOBALS['PHPOPENFW_CONFIG_INDEX']);
            }
        }
        if (isset($_SESSION['PHPOPENFW_DATA_SOURCES'])) {
            $GLOBALS['PHPOPENFW_DATA_SOURCES'] =& $_SESSION['PHPOPENFW_DATA_SOURCES'];
        }
        if (isset($_SESSION['PHPOPENFW_DEFAULT_DATA_SOURCE'])) {
            $GLOBALS['PHPOPENFW_DEFAULT_DATA_SOURCE'] = $_SESSION['PHPOPENFW_DEFAULT_DATA_SOURCE'];
        }
    }

    //*************************************************************************
    //*************************************************************************
    /**
     * Kill a Session Function
     */
    //*************************************************************************
    //*************************************************************************
    public static function Kill()
    {
        if (isset($_SESSION)) {
            $_SESSION = array();

            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params['path'], $params['domain'],
                    $params['secure'], $params['httponly']
                );
            }

            session_unset();
            session_destroy();
            return true;
        }

        return false;
    }

}
