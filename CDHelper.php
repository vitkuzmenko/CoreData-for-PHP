<?php

/*
 * Created 18/02/15 by Vitaliy Kuz'menko © 2014
 * All rights reserved.

 * CDHelper.php
 * CDHelper
 */

class CDHelper {
	
	/**
	 * Convert undersoce string to camel case.
	 * 
	 * @access public
	 * @param mixed $string
	 * @param bool $capitalizeFirstCharacter (default: false)
	 * @return void
	 */
	public static function underscoreToCamelCase($string, $capitalizeFirstCharacter = false) {
	    $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));	
	    if (!$capitalizeFirstCharacter) {
	        $str[0] = strtolower($str[0]);
	    }
	    return $str;
	}
	
	/**
	 * Convert camel case string to underscore.
	 * 
	 * @access public
	 * @param mixed $input
	 * @return void
	 */
	public static function camelCaseToUnderscore($string) {
		preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $string, $matches);
		$ret = $matches[0];
		foreach ($ret as &$match) {
			$match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
		}
		return implode('_', $ret);
	}

}
