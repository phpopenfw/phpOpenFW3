<?php
//*****************************************************************************
//*****************************************************************************
/**
 * Namespaces Class
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
 * Namespaces Class
 */
//*****************************************************************************
class Namespaces
{
    //*************************************************************************
    //*************************************************************************
    /**
     * Alias Classes Directory
     */
    //*************************************************************************
    //*************************************************************************
    public static function AliasClassesDir($dir, $real_ns, $alias_ns, Array $args=[])
    {
        $recursive = true;
        extract($args);
        if (!is_dir($dir) || !$files = scandir($dir)) {
            throw new \Exception('Directory is invalid or inaccessible.');
        }
        $aliased = 0;
        foreach ($files as $file) {
            if (substr($file, 0, 1) == '.') {
                continue;
            }
            $full_file = $dir . '/' . $file;
            if (is_dir($full_file) && $recursive) {
                \phpOpenFW\Format\Strings::TrimSlashes($real_ns);
                \phpOpenFW\Format\Strings::TrimSlashes($alias_ns);
                $aliased += static::AliasClassesDir(
                    $full_file, 
                    $real_ns . '\\' . $file,
                    $alias_ns . '\\' . $file,
                    $args
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
     *
     * Alias a class from one namespace to another
     */
    //*************************************************************************
    //*************************************************************************
    public static function AliasClass($class, $real_ns='', $alias_ns='')
    {
        //---------------------------------------------------------------------
        // Does namespace cache already exist?
        //---------------------------------------------------------------------
        global $namespace_cache;
        if (!$namespace_cache) {
            $namespace_cache = new \phpOpenFW\Cache\Objects\GlobalCache('PHPOPENFW_NAMESPACE_CACHE');
        }

        //---------------------------------------------------------------------
        // Namespaces must be strings
        //---------------------------------------------------------------------
        if (!is_string($real_ns) || !is_string($alias_ns)) {
            throw new \Exception('Namespaces must be passed as strings.');
        }

        //---------------------------------------------------------------------
        // Real namespace has already been calculated
        //---------------------------------------------------------------------
        if ($tmp_real_ns = $namespace_cache->get($real_ns)) {
            $real_ns = $tmp_real_ns;
        }
        //---------------------------------------------------------------------
        // Real namespace has NOT been calculated
        //---------------------------------------------------------------------
        else {
            $tmp_real_ns = $real_ns;
            \phpOpenFW\Format\Strings::TrimSlashes($tmp_real_ns);
            $namespace_cache->set($real_ns, $tmp_real_ns);
        }

        //---------------------------------------------------------------------
        // Alias namespace has already been calculated
        //---------------------------------------------------------------------
        if ($tmp_alias_ns = $namespace_cache->get($alias_ns)) {
            $alias_ns = $tmp_alias_ns;
        }
        //---------------------------------------------------------------------
        // Alias namespace has NOT been calculated
        //---------------------------------------------------------------------
        else {
            $tmp_alias_ns = $alias_ns;
            \phpOpenFW\Format\Strings::TrimSlashes($tmp_alias_ns);
            $namespace_cache->set($alias_ns, $tmp_alias_ns);
        }

        //---------------------------------------------------------------------
        // Strip prepending slashes from class stub
        //---------------------------------------------------------------------
        while (substr($class, 0, 1) == '\\') {
            $class = substr($class, 1);
        }

        //---------------------------------------------------------------------
        // Set Real and Alias classes
        //---------------------------------------------------------------------
        $real_class = $tmp_real_ns . '\\' . $class;
        $alias_class = $tmp_alias_ns . '\\' . $class;

        //---------------------------------------------------------------------
        // Check for alias class conflict
        //---------------------------------------------------------------------
        if (!class_exists($alias_class)) {
            return class_alias($real_class, $alias_class);
        }

        //---------------------------------------------------------------------
        // Alias NOT created
        //---------------------------------------------------------------------
        return false;
    }

    //*************************************************************************
    //*************************************************************************
    /**
     * Remap namespace and alias class
     */
    //*************************************************************************
    //*************************************************************************
    public static function RemapAliasClass($ns_map, $class)
    {
        foreach ($ns_map as $legacy_ns => $new_ns) {
            $ns_len = strlen($legacy_ns);
            if (substr($class, 0, $ns_len) == $legacy_ns) {
                $class_end = substr($class, $ns_len);
                return static::AliasClass($class_end, $new_ns, $legacy_ns);
            }
        }
        return false;
    }

}
