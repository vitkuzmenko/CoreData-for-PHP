<?php

/*
 * Created 31/05/14 by Vitaliy Kuz'menko © 2014
 * All rights reserved.

 * CoreData.php
 * 
 */
// Class Autoloader
spl_autoload_register(function ($sClassName) {
	if (empty($sClassName)) {
		throw new Exception('Class name is empty');
	}

	$sPath = realpath(dirname(__FILE__));
	if (empty($sPath)) {
		throw new Exception('Current path is empty');
	}

	$sFile = sprintf('%s%s%s.php', $sPath, DIRECTORY_SEPARATOR, str_replace('_', DIRECTORY_SEPARATOR, $sClassName));

	if (is_file($sFile) && is_readable($sFile)) {
		require_once $sFile;
	}
});

/**
 * Singleton CoreData class
 *
 */
final class CoreData {
	
	public $coordinator;
	
	/**
	 * Names in data base based on underscore
	 * 
	 * (default value: false)
	 * 
	 * @var bool
	 * @access public
	 * @static
	 */
	static $isUnderscore = false;
	
	/**
	 * Output objects values data based on underscore
	 * 
	 * (default value: false)
	 * 
	 * @var bool
	 * @access public
	 * @static
	 */
	static $outputIsUnderscore = false;
	
    /**
     * Call this method to get singleton
     *
     * @return CoreData
     */
    public static function sharedInstance() {
        static $inst = null;
        if ($inst === null) {
            $inst = new CoreData();
        }
        return $inst;
    }
    
    /**
     * Get store by name or first store.
     * 
     * @access public
     * @static
     * @param mixed $name (default: null)
     * @return void
     */
    public static function getStore($name = null) {
    	$coordinator = CoreData::coordinator();
    	return $coordinator->getStore($name);
    }
        
    /**
     * Get CDPersistentStoreCoordinator
     * 
     * @access public
     * @return CDPersistentStoreCoordinator
     */
    public static function coordinator() {
	    $coreData = CoreData::sharedInstance();
	    
    	if ($coreData->coordinator) {
	    	return $coreData->coordinator;
    	}
    	
    	$coreData->coordinator = new CDPersistentStoreCoordinator();
    	
    	return $coreData->coordinator;
    }

	// ! Errors
    
    public static function error() {
		return CDError::sharedInstance();
    }

    public static function checkForError() {
		return CDError::checkForError();
    }
    
    // ! AddStores
    
    /**
     * Add Store to CDPersistentStoreCoordinator.
     * 
     * @access public
     * @static
     * @param CDPersistentStore $store
     * @return void
     */
    public static function addStore(CDPersistentStore $store) {
    	$coordinator = CoreData::coordinator();
    	$coordinator->addPersistentStore($store);
    }
    
    public static function addStoreFromArray(array $store) {
		$newStore = new CDPersistentStore($store['name'], $store['host'], $store['user'], $store['password'], $store['dataBase']);
		CoreData::addStore($newStore);
    }
    
    public static function connect() {
    	$coordinator = CoreData::coordinator();
    	$coordinator->connect();
    }
    
}