<?php
//*****************************************************************************
//*****************************************************************************
/**
 * Strings Generation Class
 *
 * @package         phpOpenFW
 * @author          Christian J. Clark
 * @copyright       Copyright (c) Christian J. Clark
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

}
