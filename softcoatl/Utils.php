<?php

/**
 * Utils
 * XXXXXXXXXX®
 * © 2019, Softcoatl
 * http://www.softcoatl.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since ago 2019
 */

namespace com\softcoatl\utils;

class Utils {
    
    public static function split($string, $separator = ",") {

        preg_match_all('/"((?:[^"]|\\\\.)*)"/', $string, $match);
        $criteria = "";
        foreach ($match[1] as $token) {
            $criteria .= ( $criteria == "" ? "" : $separator) . $token;
        }
        foreach($match[0] as $token) {
            $string = str_replace( $token, "", $string );
        }
        if (strlen($string) > 0) {
            $criteria .= ( $criteria == "" ? "" : $separator) . str_replace( " ", $separator, trim( $string ) );
        }
        return $criteria;
    }    

    public static function uempty($value, $default = "") {
        return empty($value) ? $default : $value;
    }
}
