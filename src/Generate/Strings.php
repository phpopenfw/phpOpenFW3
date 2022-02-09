<?php
//*****************************************************************************
//*****************************************************************************
/**
 * Strings Generation Class
 *
 * @package         phpOpenFW
 * @author          Christian J. Clark
 * @copyright       Copyright (c) Christian J. Clark
 * @website         https://phpopenfw.org
 * @license         https://mit-license.org
 **/
//*****************************************************************************
//*****************************************************************************

namespace phpOpenFW\Generate;

//*****************************************************************************
/**
 * Strings Generation Class
 */
//*****************************************************************************
class Strings
{

    //=========================================================================
    //=========================================================================
    // Random String Generator
    //=========================================================================
    // Source from: https://stackoverflow.com/questions/4356289/php-random-string-generator
    //=========================================================================
    //=========================================================================
    public static function Random(int $length=10, $keyspace=''): string
    {
        if (empty($keyspace)) {
            $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        if ($length < 1) {
            throw new \RangeException("Length must be a positive integer.");
        }
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces []= $keyspace[random_int(0, $max)];
        }

        return implode('', $pieces);
    }

    //==========================================================================
    //==========================================================================
    // Generate a Cache Key
    //==========================================================================
    //==========================================================================
    public static function CacheKey($base, Array $params=[], Array $args=[])
    {
        //----------------------------------------------------------------------
        // Check Base
        //----------------------------------------------------------------------
        if (!$base && !$params) {
            throw new \Exception('Empty cache key input given.');
        }

        //----------------------------------------------------------------------
        // Defaults / Extract Args
        //----------------------------------------------------------------------
        $separator = ':';
        $hash = 'md5';
        extract($args);
        $cache_key = '';

        //----------------------------------------------------------------------
        // Base is an array
        //----------------------------------------------------------------------
        if ($base && is_iterable($base)) {
            foreach ($base as $b) {
                if (is_array($b)) {
                    $cache_key .= $separator . serialize($b);
                }
                else {
                    $cache_key .= $separator . $b;
                }
            }
        }
        else {
            $cache_key = $base;
        }

        //----------------------------------------------------------------------
        // Add Params
        //----------------------------------------------------------------------
        foreach ($params as $p) {
            if (is_array($p)) {
                $cache_key .= $separator . serialize($p);
            }
            else {
                $cache_key .= $separator . $p;
            }
        }

        //----------------------------------------------------------------------
        // Hash and return the key
        //----------------------------------------------------------------------
        if ($hash) {
            $hash = strtolower($hash);
            if (!in_array($hash, hash_algos())) {
                throw new \Exception('Invalid hash algorithm given.');
            }
            return hash($hash, $cache_key);
        }
        //----------------------------------------------------------------------
        // Return the raw key
        //----------------------------------------------------------------------
        else {
            return $cache_key;
        }
    }

}
