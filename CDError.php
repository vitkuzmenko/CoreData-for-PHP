<?php

/*
 * Created 16/02/15 by Vitaliy Kuz'menko © 2014
 * All rights reserved.

 * error.php
 * Error
 */
 
class CDError {
		
	
	/**
	 * Error code
	 * 
	 * @var mixed
	 * @access public
	 */
	var $code;
	
	/**
	 * Error name
	 * String value with all uppercase letters and underscores for spaces
	 * 
	 * @var mixed
	 * @access public
	 */
	var $error;

	/**
	 * Singleton for Erorr class.
	 * 
	 * @access public
	 * @static
	 * @return Main object
	 */
	public static function sharedInstance() {
        static $inst = null;
        if ($inst === null) {
            $inst = new self();
        }
        return $inst;
    }
    
    public static function setCode($code, $error) {
		$object = self::sharedInstance();
		$object->code = $code;
		$object->error = $error;
	}
    	
	public static function setError($error) {
		$object = self::sharedInstance();
		$object->error = $error;
	}
	
	/**
	 * Check request for errors.
	 * 
	 * @access private
	 * @return true if no errors
	 */
	public static function checkForError() {
		$object = self::sharedInstance();
		if ((is_null($object->code) || $object->code == 0 || empty($object->code)) 
		&& (is_null($object->error) || empty($object->error))) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Get error data in array.
	 * 
	 * @access public
	 * @static
	 * @return array if have error, null if no error
	 */
	public static function getError() {
		
		if (self::checkForError()) {
			return;
		}
		
		$object = self::sharedInstance();
		
		return $object->error;
	}
			
}
