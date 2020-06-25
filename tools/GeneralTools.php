<?php
/**
 * Created by PhpStorm.
 * User: Hadrien
 * Date: 25/06/2020
 * Time: 12:02
 */

namespace Jobs\Tools;


class GeneralTools
{

    public static function getStringBetween($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    public static function toCamelCase($string){
        $val = ucwords(str_replace(['-', '_'], ' ', $string));
        $val = str_replace(' ', '', $val);
        return lcfirst($val);
    }

}