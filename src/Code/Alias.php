<?php
//*****************************************************************************
//*****************************************************************************
/**
 * Alias Class
 *
 * @package         phpOpenFW
 * @author          Christian J. Clark
 * @copyright       Copyright (c) Christian J. Clark
 * @license         https://mit-license.org
 **/
//*****************************************************************************
//*****************************************************************************

namespace phpOpenFW\Code;

//*****************************************************************************
/**
 * Alias Class
 */
//*****************************************************************************
class Alias
{
    //*************************************************************************
    //*************************************************************************
    /**
     * Alias Classes Directory
     */
    //*************************************************************************
    //*************************************************************************
    public static function AliasClassesDir($dir, $real_ns, $alias_ns)
    {
        if (!is_dir($dir) || !$files = scandir($dir)) {
            throw new \Exception('Directory is invalid or inaccessible.');
        }
        $aliased = 0;
        foreach ($files as $file) {
            $full_file = $dir . '/' . $file;
            if (is_dir($full_file)) {
                $aliased += static::AliasClassesDir(
                    $full_file, 
                    $real_ns . '\\' . $file,
                    $alias_ns . '\\' . $file
                );
            }
            else {
                $pi = pathinfo($full_file);
                if ($pi && $pi['extension'] == 'php') {
                    $aliased += (int)static::AliasClass($pi['filename'], $real_ns, $alias_ns);
                }
            }
        }
        return $aliased;
    }

    //*************************************************************************
    //*************************************************************************
    /**
     * Alias Class
     */
    //*************************************************************************
    //*************************************************************************
    public static function AliasClass($class, $real_ns='', $alias_ns='')
    {
        if (!is_scalar($real_ns) || !is_scalar($alias_ns)) {
            throw new \Exception('Namespaces must be passed as strings.');
        }
        if (substr($real_ns, strlen($real_ns) - 1, 1) != '\\') {
            $real_ns .= '\\';
        }
        if (substr($alias_ns, strlen($alias_ns) - 1, 1) != '/\\') {
            $alias_ns .= '\\';
        }
        if (substr($class, 0, 1) == '\\') {
            $class = substr($class, 1);
        }
        return class_alias($real_ns . $class, $alias_ns . $class);
    }

}
